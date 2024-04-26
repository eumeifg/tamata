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
namespace Magedelight\Sales\Controller\Sellerhtml\Order;

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
     *
     * @param CreditmemoRepositoryInterface $creditmemoRepository
     * @param CreditmemoFactory $creditmemoFactory
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param \Magento\Sales\Api\InvoiceRepositoryInterface $invoiceRepository
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magedelight\Vendor\Model\Session $sellerSession
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\CatalogInventory\Api\StockConfigurationInterface $stockConfiguration
     * @param \Magedelight\Sales\Model\Order $vendorOrder
     * @param array $data
     */
    public function __construct(
        CreditmemoRepositoryInterface $creditmemoRepository,
        CreditmemoFactory $creditmemoFactory,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Sales\Api\InvoiceRepositoryInterface $invoiceRepository,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magedelight\Vendor\Model\Session $sellerSession,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\Registry $registry,
        \Magento\CatalogInventory\Api\StockConfigurationInterface $stockConfiguration,
        \Magedelight\Sales\Model\Order $vendorOrder,
        array $data = []
    ) {
        $this->creditmemoRepository = $creditmemoRepository;
        $this->creditmemoFactory = $creditmemoFactory;
        $this->orderFactory = $orderFactory;
        $this->invoiceRepository = $invoiceRepository;
        $this->eventManager = $eventManager;
        $this->sellerSession = $sellerSession;
        $this->messageManager = $messageManager;
        $this->registry = $registry;
        $this->stockConfiguration = $stockConfiguration;
        $this->vendorOrder = $vendorOrder;
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
            $data = $this->sellerSession->getFormData(true);
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
     * @param int $orderId
     * @param int $vendorId
     * @return \Magedelight\Sales\Model\Order
     */
    protected function getVendorOrder($orderId, $vendorId)
    {
        return $this->vendorOrder->getByOriginOrderId($orderId, $vendorId);
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
            $this->messageManager->addError(__('The order no longer exists.'));
            return false;
        }

        /**
         * Check creditmemo create availability
         */
        if (!$vendorOrder->canCreditmemo()) {
            $this->messageManager->addError(__('We can\'t create credit memo for the order.'));
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
     * @return \Magento\Sales\Model\Order\Creditmemo|false
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function load($newAction = false)
    {
        $creditmemo = false;
        $creditmemoId = $this->getCreditmemoId();
        $orderId = $this->getOrderId();
        if ($creditmemoId) {
            $creditmemo = $this->creditmemoRepository->get($creditmemoId);
        } elseif ($orderId) {
            $data = $this->getCreditmemo();
            $order = $this->orderFactory->create()->load($orderId);
            $vendorId = $this->getVendorId();
            $vendorOrder = $this->getVendorOrder($orderId, $vendorId);
            $invoice = $this->_initInvoice($order);

            if (!$this->_canCreditmemo($vendorOrder)) {
                return false;
            }

            $savedData = $this->_getItemData();

            $qtys = [];
            $backToStock = [];
            if ($newAction) {
                $savedData = [];
                $data['shipping_amount'] = ($vendorOrder->getData('shipping_amount') -
                    $vendorOrder->getData('shipping_refunded'));
            } elseif ($order->getShippingMethod() == 'rbmatrixrate_rbmatrixrate') {
                $data['shipping_amount'] = 0;
            }

            foreach ($savedData as $orderItemId => $itemData) {
                if (isset($itemData['qty'])) {
                    $qtys[$orderItemId] = $itemData['qty'];
                    if ($order->getShippingMethod() == 'rbmatrixrate_rbmatrixrate') {
                        $data['shipping_amount'] += $itemData['qty'] * $itemData['shipping_rate'];
                    }
                }
                if (isset($itemData['back_to_stock'])) {
                    $backToStock[$orderItemId] = true;
                }
            }
            $data['qtys'] = $qtys;
            $data['tax_amount'] = $vendorOrder->getTaxAmount() - $vendorOrder->getTaxRefunded();
            $data['base_tax_amount'] = $vendorOrder->getBaseTaxAmount() - $vendorOrder->getBaseTaxRefunded();
            $data['shipping_amount'] = $vendorOrder->getShippingAmount() - $vendorOrder->getShippingRefunded();
            $data['base_shipping_amount'] =
                $vendorOrder->getBaseShippingAmount() - $vendorOrder->getBaseShippingRefunded();
            $data['shipping_tax_amount'] =
                $vendorOrder->getShippingTaxAmount() - $vendorOrder->getShippingTaxRefunded();
            $data['base_shipping_tax_amount'] =
                $vendorOrder->getBaseShippingTaxAmount() - $vendorOrder->getBaseShippingTaxRefunded();
            $data['grand_total'] = $vendorOrder->getGrandTotal() - $vendorOrder->getTotalRefunded();
            $data['base_grand_total'] = $vendorOrder->getBaseGrandTotal() - $vendorOrder->getBaseTotalRefunded();

            if ($invoice) {
                $creditmemo = $this->creditmemoFactory->createByInvoice($invoice, $data);
            } else {
                $order->setVendorFilter($vendorId);
                $creditmemo = $this->creditmemoFactory->createByVendorOrder($order, $vendorOrder, $data);
            }
            $creditmemo->setVendorId($vendorId);
            $creditmemo->setVendorOrder($vendorOrder);
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
            'frontend_sales_order_creditmemo_register_before',
            ['creditmemo' => $creditmemo, 'input' => $this->getCreditmemo()]
        );

        $this->registry->register('current_creditmemo', $creditmemo);
        return $creditmemo;
    }
}
