<?php 

namespace MDC\Sales\Model\Sales\Order\Creditmemo;

use Magento\Framework\Model\AbstractModel;
use Magento\Sales\Model\Order\Email\Sender\CreditmemoSender;
use Magedelight\Sales\Model\Sales\Order\CreditmemoFactory;
use Magento\Framework\Event\ManagerInterface as EventManagerInterface;

class Save extends AbstractModel
{
	/**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_Sales::sales_creditmemo';

    /**
     * @var \Magedelight\Sales\Controller\Adminhtml\Order\CreditmemoLoader
     */
    protected $creditmemoLoader;

    /**
     * @var CreditmemoSender
     */
    protected $creditmemoSender;
      /**
     * @var \Magedelight\Sales\Model\CheckStoreCreditHistory
     */
     protected $creditHistoryModel;

     /**
     * @var CreditmemoFactory;
     */
    protected $creditmemoFactory;
     /**
     * @var \Magento\CatalogInventory\Api\StockConfigurationInterface
     */
    protected $stockConfiguration;

    /**
     * @var EventManagerInterface
     */
    protected $eventManager;

    /**
     * @param Action\Context $context
     * @param \Magedelight\Sales\Controller\Adminhtml\Order\CreditmemoLoader $creditmemoLoader
     * @param CreditmemoSender $creditmemoSender
     * @param \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory
     */
	
	public function __construct(
        \Magento\Framework\Model\Context $context,	 
        \Magento\Framework\Registry $registry,
        \Magedelight\Sales\Controller\Adminhtml\Order\CreditmemoLoader $creditmemoLoader,
        CreditmemoSender $creditmemoSender,        
        \Magedelight\Sales\Model\CheckStoreCreditHistory $creditHistoryModel,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        CreditmemoFactory $creditmemoFactory,
        \Magento\CatalogInventory\Api\StockConfigurationInterface $stockConfiguration,
        EventManagerInterface $eventManager,
        array $data = []
	){
		$this->creditmemoLoader = $creditmemoLoader;
        $this->creditmemoSender = $creditmemoSender;        
        $this->creditHistoryModel = $creditHistoryModel;
        $this->creditmemoFactory = $creditmemoFactory;
        $this->stockConfiguration = $stockConfiguration;
        $this->eventManager = $eventManager;
        $this->registry = $registry;
        parent::__construct($context,$registry,$resource,$resourceCollection,$data);
	}

	public function generateCreditMemoWithCancelOrder($vendorOrder,$params,$order){        
        $orderId = $params['order_id'];
        $vendorId = $params['do_as_vendor'];
        $vendorOrderId = $params['vendor_order_id'];  

        $data['do_offline'] = true;
        $data['send_email'] = 1;
 
        $creditmemo = $this->loadCreditMemo($params,$vendorOrder,$order);
        
        if ($creditmemo) {
                if (!$creditmemo->isValidGrandTotal()) {
                    throw new \Magento\Framework\Exception\LocalizedException(
                        __('The credit memo\'s total must be positive.')
                    );
                }
                $creditmemoManagement = \Magento\Framework\App\ObjectManager::getInstance()
                        ->get(\Magento\Sales\Api\CreditmemoManagementInterface::class);
                 
                $creditmemoManagement->refund($creditmemo, (bool)$data['do_offline'], !empty($data['send_email']));

                if (!empty($data['send_email'])) {
                    $this->creditmemoSender->send($creditmemo);
                }

                return true;               
            }
            else{
                return false;
            }
         
	}

    public function loadCreditMemo($params,$vendorOrder,$order){

        $invoiceData = [];
        $qtys = [];
        $data = [];
        $backToStock = [];

        foreach ($order->getItemsCollection() as $item) {
 
            if ( $params['vendor_order_id'] === $item->getVendorOrderId() ) {
               $invoiceData['items'][$item->getId()] = array("back_to_stock" => 1,"shipping_rate" => $item->getShippingAmount(),"qty" => $item->getQtyOrdered() );

               $qtys[$item->getId()] =  $item->getQtyOrdered();

                $backToStock[$item->getId()] = true;
            }
        } 

        $data = array_merge($data, $invoiceData);
        $data['shipping_amount'] = $item->getShippingAmount();
        $data['do_offline'] = true;

        
        // $invoiceData = $this->request->getParam('creditmemo', []);
        $invoiceItems = isset($invoiceData['items']) ? $invoiceData['items'] : [];
        $vendorOrderQty = $invoiceQty = $orderShippingAmount = $orderShippingTaxAmount = 0;
        $orderBaseShippingAmount = $orderBaseShippingTaxAmount = $giftwrapperAmount = $baseGiftwrapperAmount = 0;
        $discountAmount = $baseDiscountAmount = $storeCreditAmount = $baseStoreCreditAmount = 0;
        $invoiceBaseGrandTotal = $invoiceGrandTotal = $tax = $baseTax = 0;
       
        $customerRefundAmount = $this->creditHistoryModel->getCustomerRefundableAmount($order, $vendorOrder->getGrandTotal(), $params['vendor_order_id']);
        $storeCreditAmount = $customerRefundAmount;
         foreach ($order->getItemsCollection() as $item) {
                    if ($item->getData('vendor_order_id') != $params['vendor_order_id']) {
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
              
                $creditmemo->setVendorId($params['do_as_vendor']);
                $creditmemo->setVendorOrder($vendorOrder);
                $creditmemo->setVendorOrderId($vendorOrder->getVendorOrderId());
              
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


            $input = [];

            $input = array_merge($input, $invoiceData);
            $input['shipping_amount'] = $item->getShippingAmount();
            $input['do_offline'] = true;
            $input['refund_customerbalance_return_enable'] = true;
            $input['refund_customerbalance_return'] = $storeCreditAmount;

         
             $this->eventManager->dispatch(
                'adminhtml_sales_order_creditmemo_register_before',
                ['creditmemo' => $creditmemo, 'input' => $input]
            ); 
            
            $this->registry->register('current_creditmemo', $creditmemo);

            $this->registry->unregister('current_creditmemo');
            
            return $creditmemo;
                
        }
}

?>