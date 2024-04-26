<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Sales
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Sales\Controller\Adminhtml\Order;

use Magedelight\Sales\Model\Sales\Order\CreditmemoFactory;
use Magento\Framework\DataObject;
use Magento\Sales\Api\CreditmemoRepositoryInterface;

/**
 * @method CreditmemoLoader setCreditmemoId($id)
 * @method CreditmemoLoader setCreditmemo($creditMemo)
 * @method CreditmemoLoader setInvoiceId($id)
 * @method CreditmemoLoader setOrderId($id)
 * @method CreditmemoLoader setVendorId($id)
 * @method int getCreditmemoId()
 * @method string getCreditmemo()
 * @method int getInvoiceId()
 * @method int getOrderId()
 * @method int getVendorId()
 */
class CreditmemoLoader extends DataObject
{
    /**
     * @var \Magedelight\Sales\Model\Order
     */
    protected $vendorOrder;

    /**
     * @var CreditmemoRepositoryInterface;
     */
    protected $creditmemoRepository;

    /**
     * @var CreditmemoFactory;
     */
    protected $creditmemoFactory;

    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $orderFactory;

    /**
     * @var \Magento\Sales\Api\InvoiceRepositoryInterface
     */
    protected $invoiceRepository;

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $eventManager;

    /**
     * @var \Magento\Backend\Model\Session
     */
    protected $backendSession;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\CatalogInventory\Api\StockConfigurationInterface
     */
    protected $stockConfiguration;

    /**
     * @var \Magedelight\Sales\Api\OrderRepositoryInterface
     */
    protected $vendorOrderRepository;    
     /**
     * @var \Magedelight\Sales\Model\CheckStoreCreditHistory
     */
    protected $creditHistoryModel;

    /**
     * @param CreditmemoRepositoryInterface $creditmemoRepository
     * @param CreditmemoFactory $creditmemoFactory
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param \Magento\Sales\Api\InvoiceRepositoryInterface $invoiceRepository
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Backend\Model\Session $backendSession
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\CatalogInventory\Api\StockConfigurationInterface $stockConfiguration
     * @param \Magento\Framework\App\Request\Http $request
     * @param \Magedelight\Sales\Model\Order $vendorOrder
     * @param \Magedelight\Vendor\Helper\Data $vendorHelper
     * @param \Magedelight\Sales\Api\OrderRepositoryInterface $vendorOrderRepository
     * @param array $data
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        CreditmemoRepositoryInterface $creditmemoRepository,
        CreditmemoFactory $creditmemoFactory,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Sales\Api\InvoiceRepositoryInterface $invoiceRepository,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Backend\Model\Session $backendSession,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\Registry $registry,
        \Magento\CatalogInventory\Api\StockConfigurationInterface $stockConfiguration,
        \Magento\Framework\App\Request\Http $request,
        \Magedelight\Sales\Model\Order $vendorOrder,
        \Magedelight\Vendor\Helper\Data $vendorHelper,
        \Magedelight\Sales\Api\OrderRepositoryInterface $vendorOrderRepository,
        \Magedelight\Sales\Model\CheckStoreCreditHistory $creditHistoryModel,        
        array $data = []
    ) {
        $this->creditmemoRepository = $creditmemoRepository;
        $this->creditmemoFactory = $creditmemoFactory;
        $this->orderFactory = $orderFactory;
        $this->invoiceRepository = $invoiceRepository;
        $this->eventManager = $eventManager;
        $this->backendSession = $backendSession;
        $this->messageManager = $messageManager;
        $this->registry = $registry;
        $this->stockConfiguration = $stockConfiguration;
        $this->vendorOrder = $vendorOrder;
        $this->vendorHelper = $vendorHelper;
        $this->request = $request;
        $this->vendorOrderRepository = $vendorOrderRepository;
        $this->creditHistoryModel = $creditHistoryModel;     
        parent::__construct($data);
    }

    /**
     * Get requested items qtys and return to stock flags
     *
     * @return array
     */
    protected function _getItemData()
    {
        $data = $this->getCreditmemo();
        if (!$data) {
            $data = $this->backendSession->getFormData(true);
        }

        if (isset($data['items'])) {
            $qtys = $data['items'];
        } else {
            $qtys = [];
        }
        return $qtys;
    }

    /**
     *
     * @param int $vendorOrderId
     * @return \Magedelight\Sales\Api\Data\VendorOrderInterface
     */
    protected function getVendorOrder($vendorOrderId)
    {
        return $this->vendorOrderRepository->getById($vendorOrderId);
    }

    /**
     * Check if creditmeno can be created for order
     * @param \Magedelight\Sales\Model\Order $vendorOrder
     * @return bool
     */
    protected function _canCreditmemo($vendorOrder)
    {
        /**
         * Check order existing
         */
        if (!$vendorOrder->getId()) {
            $this->messageManager->addErrorMessage(__('The order no longer exists.'));
            return false;
        }

        /**
         * Check creditmemo create availability
         */
        if (!$vendorOrder->canCreditmemo()) {
            $this->messageManager->addErrorMessage(__('We can\'t create credit memo for the order.'));
            return false;
        }
        return true;
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     * @return $this|bool
     */
    protected function _initInvoice($order)
    {
        $invoiceId = $this->getInvoiceId();
        if ($invoiceId) {
            $invoice = $this->invoiceRepository->get($invoiceId);
            $invoice->setOrder($order);
            if ($invoice->getId()) {
                return $invoice;
            }
        }
        return false;
    }

    /**
     * Initialize creditmemo model instance
     *
     * @param bool $newAction
     * @return \Magento\Sales\Model\Order\Creditmemo|false
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function load($newAction = false)
    {
        $creditmemo = false;
        $orderId = $this->getOrderId();
        $creditmemoId = $this->getCreditmemoId();
        if ($creditmemoId) {
            $creditmemo = $this->creditmemoRepository->get($creditmemoId);
        } elseif ($orderId) {
            $data = $this->getCreditmemo();
            $order = $this->orderFactory->create()->load($orderId);
            $vendorOrder = $this->getVendorOrder($this->getVendorOrderId());
            $invoice = $this->_initInvoice($order);

            if (!$this->_canCreditmemo($vendorOrder)) {
                return false;
            }

            $savedData = $this->_getItemData();
            $multiplier = 1;
            $qtys = [];
            $backToStock = [];
            if ($newAction) {
                $savedData = [];
                /* $data['shipping_amount'] = ($vendorOrder->getData('shipping_amount') -
                 $vendorOrder->getData('shipping_refunded')); */
            } /*elseif ($order->getShippingMethod() == 'rbmatrixrate_rbmatrixrate') {
                 $data['shipping_amount'] = 0;
            }*/

            foreach ($savedData as $orderItemId => $itemData) {
                if (isset($itemData['qty'])) {
                    $qtys[$orderItemId] = $itemData['qty'];
                    /*if ($order->getShippingMethod() == 'rbmatrixrate_rbmatrixrate') {
                        $data['shipping_amount'] += $itemData['qty'] * $itemData['shipping_rate'];
                    }*/
                }
                if (isset($itemData['back_to_stock'])) {
                    $backToStock[$orderItemId] = true;
                }
            }
            $invoiceData = $this->request->getParam('creditmemo', []);
            $invoiceItems = isset($invoiceData['items']) ? $invoiceData['items'] : [];
            $vendorOrderQty = $invoiceQty = $orderShippingAmount = $orderShippingTaxAmount = 0;
            $orderBaseShippingAmount = $orderBaseShippingTaxAmount = $giftwrapperAmount = $baseGiftwrapperAmount = 0;
            $discountAmount = $baseDiscountAmount = $storeCreditAmount = $baseStoreCreditAmount = 0;
            $invoiceBaseGrandTotal = $invoiceGrandTotal = $tax = $baseTax = 0;

            /* On creat new credit memo check available customer store credit to be refund  */
            $storeCreditAmount = $order->getBaseCustomerBalanceAmount() - $order->getCustomerBalanceRefunded();

            $creditAction = '5'; // 5 = reverted, 4 = refunded
            $customerRevertedAmount = 0;
            $creditReverted  = $this->creditHistoryModel->getOrderRevertRefundHistory($order, $creditAction);
            if($creditReverted['reverted'] && $creditReverted['reverted'] > 0 ){
                $customerRevertedAmount = $creditReverted['reverted']; 

              if($customerRevertedAmount >= $storeCreditAmount ){
                     $storeCreditAmount = 0;
                }
            }    
             

            /*On creat new credit memo check available customer store credit to be refund*/
           
            if (empty($invoiceItems)) {
            
                /*Get sales order item discount amount in case admin liability to adjust in Credit memo grand total for Store credit refund case */ 
                if($vendorOrder->getDiscountAmount() > 0 && $vendorOrder->getBaseDiscountAmount() > 0){
                    $discountAmount =  $vendorOrder->getDiscountAmount();
                    $baseDiscountAmount = $vendorOrder->getBaseDiscountAmount();
                }else{
                    foreach ($order->getItemsCollection() as $item) {
                        if ( $this->getVendorOrderId() === $item->getVendorOrderId() ) {
                            $discountAmount = $item->getDiscountAmount();
                            $baseDiscountAmount = $item->getBaseDiscountAmount();
                        }
                    }
                }
                /*Get sales order item discount amount in case admin liability to adjust in Credit memo grand total for Store credit refund case */

                $data['qtys'] = $qtys;
                $data['tax_amount'] = $vendorOrder->getTaxAmount() - $vendorOrder->getTaxRefunded();
                $data['base_tax_amount'] = $vendorOrder->getBaseTaxAmount() - $vendorOrder->getBaseTaxRefunded();
                /* $data['shipping_amount'] = $vendorOrder->getShippingAmount() -
                 $vendorOrder->getShippingRefunded(); */
                $data['base_shipping_amount'] =
                    $vendorOrder->getBaseShippingAmount() - $vendorOrder->getBaseShippingRefunded();
                $data['shipping_tax_amount'] =
                    $vendorOrder->getShippingTaxAmount() - $vendorOrder->getShippingTaxRefunded();
                $data['discount_amount'] = $vendorOrder->getDiscountAmount() - $vendorOrder->getDiscountRefunded();
                $data['base_discount_amount'] =
                    $vendorOrder->getBaseDiscountAmount() - $vendorOrder->getBaseDiscountRefunded();
                $data['base_shipping_tax_amount'] =
                    $vendorOrder->getBaseShippingTaxAmount() - $vendorOrder->getBaseShippingTaxRefunded();
                /*if Store credit amount grater than the item total set store credit equals to that only*/
                if($storeCreditAmount > $vendorOrder->getGrandTotal()){

                    $storeCreditAmount = $vendorOrder->getGrandTotal();
                }
                /*if Store credit amount grater than the item total set store credit equals to that only*/
                $data['grand_total'] = $vendorOrder->getGrandTotal() - $vendorOrder->getTotalRefunded() - $storeCreditAmount - $discountAmount;
                $data['base_grand_total'] = $vendorOrder->getBaseGrandTotal() - $vendorOrder->getBaseTotalRefunded() - $storeCreditAmount;
                $data['subtotal'] = $vendorOrder->getSubtotal() - $vendorOrder->getSubtotalRefunded();
                $data['base_subtotal'] = $vendorOrder->getBaseSubtotal() - $vendorOrder->getBaseSubtotalRefunded();
                $data['base_subtotal'] = $vendorOrder->getBaseSubtotal() - $vendorOrder->getBaseSubtotalRefunded();
                $giftwrapperAmount = ($vendorOrder->getGiftwrapAmount()) ? $vendorOrder->getGiftwrapAmount() : 0;
            } else {

                /*On save credit memo update store credit admin added to refund (partial or full)*/
                if( isset($data['refund_customerbalance_return']) && $data['refund_customerbalance_return'] > 0)
                {
                    $storeCreditAmount = $data['refund_customerbalance_return'];
                }
                /*On save credit memo update store credit admin added to refund (partial or full)*/                 

                foreach ($order->getItemsCollection() as $item) {
                    if ($item->getData('vendor_order_id') != $this->getVendorOrderId()) {
                        $invoiceItems[$item->getId()] = 0;
                    } else {
                        $vendorOrderQtyVendor = $item->getQtyOrdered();
                        $vendorOrderQty += $item->getQtyOrdered();
                        if (array_key_exists($item->getId(), $invoiceItems)) {
                            if ($order->getShippingMethod() == 'rbmatrixrate_rbmatrixrate') {
                                $orderShippingAmount += $vendorOrder->getShippingAmount() *
                                    ($invoiceItems[$item->getId()]  / $item->getQtyOrdered());
                                $orderBaseShippingAmount += $vendorOrder->getBaseShippingAmount() *
                                    ($invoiceItems[$item->getId()] / $item->getQtyOrdered());
                                $orderShippingTaxAmount += $vendorOrder->getShippingTaxAmount() *
                                    ($invoiceItems[$item->getId()] / $item->getQtyOrdered());
                                $orderBaseShippingTaxAmount += $vendorOrder->getBaseShippingTaxAmount() *
                                    ($invoiceItems[$item->getId()] / $item->getQtyOrdered());
                            }
                            $invoiceQtyVendor = $invoiceItems[$item->getId()]['qty'];
                            $invoiceQty += $invoiceQtyVendor;
                            $giftwrapperAmount += $item->getGiftwrapperPrice() *
                                $invoiceQtyVendor / $vendorOrderQtyVendor;
                            $discountAmount += $item->getDiscountAmount() *
                                $invoiceQtyVendor / $vendorOrderQtyVendor;
                            $baseDiscountAmount += $item->getBaseDiscountAmount() *
                                $invoiceQtyVendor / $vendorOrderQtyVendor;
                            $baseGiftwrapperAmount += $item->getBaseGiftwrapperPrice() *
                                $invoiceQtyVendor / $vendorOrderQtyVendor;
                            $percent = ($vendorOrder->getTaxAmount() > 0) ?
                                $item->getTaxAmount() / $vendorOrder->getTaxAmount() : 0;
                            $orderTotalTax =  $vendorOrder->getTaxAmount();
                            $taxRate = $orderTotalTax * $percent;
                            $taxAmount = $taxRate * ($invoiceQtyVendor / $item->getQtyOrdered());

                            $basePercent = ($vendorOrder->getBaseTaxAmount() > 0) ?
                                $item->getBaseTaxAmount() / $vendorOrder->getBaseTaxAmount() : 0;
                            $baseOrderTotalTax =  $vendorOrder->getBaseTaxAmount();
                            $baseTaxRate = $baseOrderTotalTax * $basePercent;
                            $baseTaxAmount = $baseTaxRate * ($invoiceQtyVendor / $item->getQtyOrdered());
                            $tax += $taxAmount;
                            $baseTax += $baseTaxAmount;

                            $itemTotal = $item->getPriceInclTax() * $invoiceQtyVendor;
                            $itemBaseTotal = $item->getBasePriceInclTax() * $invoiceQtyVendor;
                            $totalToInclude = $itemTotal - $taxAmount;
                            $baseTotalToInclude = $itemBaseTotal - $baseTaxAmount;

                            $invoiceBaseGrandTotal += $baseTotalToInclude;
                            $invoiceGrandTotal += $totalToInclude;
                        }
                    }
                }
                /* shipping amount reset based on vendor order */
                if ($order->getShippingMethod() != 'rbmatrixrate_rbmatrixrate') {
                    $orderShippingAmount = ($vendorOrder->getBaseShippingAmount() * $invoiceQty / $vendorOrderQty);
                    $orderBaseShippingAmount = ($vendorOrder->getBaseShippingAmount() * $invoiceQty / $vendorOrderQty);
                    $orderShippingTaxAmount = ($vendorOrder->getShippingTaxAmount() * $invoiceQty / $vendorOrderQty);
                    $orderBaseShippingTaxAmount =
                        ($vendorOrder->getBaseShippingTaxAmount() * $invoiceQty / $vendorOrderQty);
                }

                $shippingInclTax = ($vendorOrder->getShippingInclTax() * $invoiceQty / $vendorOrderQty);
                $baseShippingInclTax = ($vendorOrder->getBaseShippingInclTax() * $invoiceQty / $vendorOrderQty);

                $discountTaxCompensationAmount =
                    ($vendorOrder->getDiscountTaxCompensationAmount() * $invoiceQty / $vendorOrderQty);
                $baseDiscountTaxCompensationAmount =
                    ($vendorOrder->getBaseDiscountTaxCompensationAmount() * $invoiceQty / $vendorOrderQty);
                $shippingDiscountTaxCompensationAmount =
                    ($vendorOrder->getShippingDiscountTaxCompensationAmount() * $invoiceQty / $vendorOrderQty);
                $baseShippingDiscountTaxCompensationAmount =
                    ($vendorOrder->getBaseShippingDiscountTaxCompensationAmnt() * $invoiceQty / $vendorOrderQty);
                $shippingDiscountAmount = ($vendorOrder->getShippingDiscountAmount() * $invoiceQty / $vendorOrderQty);
                $baseShippingDiscountAmount =
                    ($vendorOrder->getBaseShippingDiscountAmount() * $invoiceQty / $vendorOrderQty);

                $tax += $shippingDiscountTaxCompensationAmount;
                $baseTax += $baseShippingDiscountTaxCompensationAmount;

                /*$discountAmount += $shippingDiscountAmount;
                $baseDiscountAmount += $baseShippingDiscountAmount;*/

                $invoiceGrandTotal -= $discountTaxCompensationAmount;
                $invoiceBaseGrandTotal -= $baseDiscountTaxCompensationAmount;

                /* Add Shipping Tax to tax amount */
                $tax += $orderShippingTaxAmount;
                $baseTax += $orderBaseShippingTaxAmount;
                $finalGrandTotal = $invoiceGrandTotal + $orderShippingAmount + $tax +
                    $giftwrapperAmount + $discountTaxCompensationAmount - $discountAmount - $storeCreditAmount;
                $finalBaseGrandTotal = $invoiceBaseGrandTotal + $orderShippingAmount + $baseTax +
                    $baseGiftwrapperAmount + $baseDiscountTaxCompensationAmount - $baseDiscountAmount - $storeCreditAmount;

                $finalGrandTotal = min($finalGrandTotal, $vendorOrder->getGrandTotal());
                $finalBaseGrandTotal = min($finalBaseGrandTotal, $vendorOrder->getBaseGrandTotal());

                $finalGrandTotal = min(
                    $finalGrandTotal,
                    ($vendorOrder->getGrandTotal() - $vendorOrder->getTotalRefunded())
                );
                $finalBaseGrandTotal = min(
                    $finalBaseGrandTotal,
                    ($vendorOrder->getBaseGrandTotal() - $vendorOrder->getBaseTotalRefunded())
                );

                $finalSubTotal = min($invoiceGrandTotal, ($order->getSubtotal() - $order->getSubtotalRefunded()));
                $finalBaseSubTotal = min(
                    $invoiceBaseGrandTotal,
                    ($order->getBaseSubtotal() - $order->getBaseSubtotalRefunded())
                );

                $data['qtys'] = $qtys;
                $data['tax_amount'] = $tax;
                $data['base_tax_amount'] = $baseTax;
                /* $data['shipping_amount'] = $orderShippingAmount; */
                $data['base_shipping_amount'] = $orderBaseShippingAmount;
                $data['discount_amount'] = $discountAmount*-1;
                $data['base_discount_amount'] = $baseDiscountAmount*-1;
                $data['shipping_tax_amount'] = $orderShippingTaxAmount;
                $data['base_shipping_tax_amount'] = $orderBaseShippingTaxAmount;
                $data['grand_total'] = $finalGrandTotal;
                $data['base_grand_total'] = $finalBaseGrandTotal;
                $data['subtotal'] = $finalSubTotal;
                $data['base_subtotal'] = $finalBaseSubTotal;
            }

            $creditmemo = $this->creditmemoFactory->createByVendorOrder($order, $vendorOrder, $data);
            $creditmemo->setTaxAmount($data['tax_amount']);
            $creditmemo->setBaseTaxAmount($data['base_tax_amount']);
            $creditmemo->setGrandTotal($data['grand_total']);
            $creditmemo->setBaseGrandTotal($data['base_grand_total']);
            $creditmemo->setSubtotal($data['subtotal']);
            $creditmemo->setBaseSubtotal($data['base_subtotal']);
            /* $creditmemo->setShippingAmount($data['shipping_amount']); */
            $creditmemo->setBaseShippingAmount($data['base_shipping_amount']);
            $creditmemo->setDiscountAmount($data['discount_amount']);
            $creditmemo->setBaseDiscountAmount($data['base_discount_amount']);
            $creditmemo->setShippingTaxAmount($data['shipping_tax_amount']);
            $creditmemo->setBaseShippingTaxAmount($data['base_shipping_tax_amount']);
            if ($invoice) {
                $creditmemo->setInvoice($invoice);
                //$creditmemo = $this->creditmemoFactory->createByInvoice($invoice, $data);
            } /*else {
                $creditmemo = $this->creditmemoFactory->createByVendorOrder($order, $vendorOrder, $data);
            }*/
            $creditmemo->setVendorId($this->getVendorId());
            $creditmemo->setVendorOrder($vendorOrder);
            $creditmemo->setVendorOrderId($vendorOrder->getVendorOrderId());
            /**
             * Process back to stock flags
             */
            foreach ($creditmemo->getAllItems() as $creditmemoItem) {
                $orderItem = $creditmemoItem->getOrderItem();
                $parentId = $orderItem->getParentItemId();
                if (isset($backToStock[$orderItem->getId()])) {
                    $creditmemoItem->setBackToStock(true);
                } elseif ($orderItem->getParentItem() && isset($backToStock[$parentId]) && $backToStock[$parentId]) {
                    $creditmemoItem->setBackToStock(true);
                } elseif (empty($savedData)) {
                    $creditmemoItem->setBackToStock(
                        $this->stockConfiguration->isAutoReturnEnabled()
                    );
                } else {
                    $creditmemoItem->setBackToStock(false);
                }
            }
        }
        
        $this->eventManager->dispatch(
            'adminhtml_sales_order_creditmemo_register_before',
            ['creditmemo' => $creditmemo, 'input' => $this->getCreditmemo()]
        ); 
            
        $this->registry->register('current_creditmemo', $creditmemo);
        return $creditmemo;
    }
}
