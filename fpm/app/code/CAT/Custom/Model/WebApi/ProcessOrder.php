<?php

namespace CAT\Custom\Model\WebApi;

use Magento\Framework\Registry;
use Magedelight\Sales\Model\Order as VendorOrder;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Shipping\Controller\Adminhtml\Order\ShipmentLoader;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Magento\Framework\DB\TransactionFactory;
use Ktpl\BarcodeGenerator\Model\Barcode;
use Magento\Framework\HTTP\PhpEnvironment\RemoteAddress;
use Ktpl\Warehousemanagement\Model\WarehousemanagementFactory;
use Magento\Store\Model\ScopeInterface;
use MDC\Sales\Model\Source\Order\PickupStatus;
use Magedelight\Sales\Model\Order\SplitOrder\DiscountProcessor;
use Magedelight\Sales\Model\Sales\Service\InvoiceService;
use Magedelight\Sales\Helper\Data;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class ProcessOrder
 * @package CAT\Custom\Model\WebApi
 */
class ProcessOrder
{
    /**
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @var ShipmentLoader
     */
    protected $shipmentLoader;

    /**
     * @var TransactionFactory
     */
    protected $_transaction;

    /**
     * @var EventManager
     */
    protected $_eventManager;

    /**
     * @var Registry
     */
    protected $_registry;

    /**
     * @var Barcode
     */
    protected $barcode;

    /**
     * @var RemoteAddress
     */
    protected $remoteAddress;

    /**
     * @var WarehousemanagementFactory
     */
    protected $wareHouseManagement;

    /**
     * @var DiscountProcessor
     */
    protected $discountProcessor;

    /**
     * @var InvoiceService
     */
    protected $invoiceService;

    /**
     * @var Data
     */
    protected $salesHelper;

    /**
     * ProcessOrder constructor.
     * @param OrderRepositoryInterface $orderRepository
     * @param ShipmentLoader $shipmentLoader
     * @param TransactionFactory $transaction
     * @param EventManager $eventManager
     * @param Registry $registry
     * @param Barcode $barcode
     * @param RemoteAddress $remoteAddress
     * @param WarehousemanagementFactory $wareHouseManagement
     * @param DiscountProcessor $discountProcessor
     * @param InvoiceService $invoiceService
     * @param Data $salesHelper
     */
    public function __construct(
        OrderRepositoryInterface $orderRepository,
        ShipmentLoader $shipmentLoader,
        TransactionFactory $transaction,
        EventManager $eventManager,
        Registry $registry,
        Barcode $barcode,
        RemoteAddress $remoteAddress,
        WarehousemanagementFactory $wareHouseManagement,
        DiscountProcessor $discountProcessor,
        InvoiceService $invoiceService,
        Data $salesHelper
    ) {
        $this->orderRepository = $orderRepository;
        $this->shipmentLoader = $shipmentLoader;
        $this->_transaction = $transaction;
        $this->_eventManager = $eventManager;
        $this->_registry = $registry;
        $this->barcode = $barcode;
        $this->remoteAddress = $remoteAddress;
        $this->wareHouseManagement = $wareHouseManagement;
        $this->discountProcessor = $discountProcessor;
        $this->invoiceService = $invoiceService;
        $this->salesHelper = $salesHelper;
    }

    /**
     * @param \Magedelight\Sales\Model\Order $vendorOrder
     */
    public function processOrder($vendorOrder) {
        /** @var \Magento\Sales\Model\Order\Shipment $shipment */
        if($shipment = $this->generateShipment($vendorOrder)) {
            $lineItemData = $this->barcode->getItemDataFromBarcode($shipment->getIncrementId());
            $visitorIp = $this->remoteAddress->getRemoteAddress();
            $userId = 0;
            if(is_array($lineItemData)) {
                foreach($lineItemData as $key => $singleRaw) {
                    $singleRaw['barcode_number'] = $shipment->getIncrementId();
                    $singleRaw['product_location'] = 0; //0: vendor to WH, 1: WH to vendor
                    $singleRaw['order_event'] = 2; // return type : 0
                    $singleRaw['ip_address'] = $visitorIp;
                    $singleRaw['user_id'] = $userId;
                    $model = $this->wareHouseManagement->create();
                    $warehouseCollection = $model->getCollection()
                        ->addFieldToFilter('product_location', $singleRaw['product_location'])
                        ->addFieldToFilter('barcode_number', $shipment->getIncrementId())
                        ->addFieldToFilter('product_id', $singleRaw['product_id']);
                    if (!$warehouseCollection->getSize()) {
                        $model->setData($singleRaw);
                        $model->save();
                    }
                    // update Tookan status
                    $this->barcode->updateTookanStatus($shipment->getIncrementId());
                    // update vendor order status
                    $vendorOrder->setStatus(
                        VendorOrder::STATUS_IN_TRANSIT
                    )->setPickupStatus(
                        PickupStatus::PICKED
                    )->save();
                }
            }
        }
    }

    /**
     * @param $vendorOrder
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function generateShipment($vendorOrder) {
        $orderId = $vendorOrder->getOrderId();
        $vendorId = $vendorOrder->getVendorId();
        $vendorOrderId = $vendorOrder->getVendorOrderId();
        /** @var \Magento\Sales\Model\Order $order */
        $order = $this->orderRepository->get($orderId);

        $items = [];
        /** @var \Magento\Sales\Model\Order\Item $item */
        foreach ($order->getItemsCollection() as $item) {
            if ( $vendorOrderId === $item->getVendorOrderId() ) {
                if( null !== $item->getParentItemId() ) {
                    $items[$item->getParentItemId()] =  $item->getQtyOrdered();
                }else{
                    $items[$item->getId()] =  $item->getQtyOrdered();
                }
            }
        }

        $data['vendor_id'] = $vendorId;
        $data['vendor_order_id'] = $vendorOrderId;
        $data['items'] = $items;

        $this->shipmentLoader->setOrderId($orderId);
        $this->shipmentLoader->setShipment($data);
        $shipment = $this->shipmentLoader->load($vendorOrderId);

        if($shipment) {
            $shipment->setData('vendor_id',$vendorId);
            $shipment->register();
            $vendorOrder->setData('main_order', $shipment->getOrder());
            $vendorOrder->setStatus(VendorOrder::STATUS_SHIPPED);
            $shipment->setVendorOrder($vendorOrder);
            $shipment->setVendorOrderId($vendorOrderId);
            $this->_saveShipment($shipment);

            $this->_registry->unregister('current_shipment');
            return $shipment;
        }else{
            return false;
        }
    }

    /**
     * @param $shipment
     * @return $this
     */
    public function _saveShipment($shipment) {
        $shipment->getOrder()->setIsInProcess(true);
        $transaction = $this->_transaction->create();
        $transaction->addObject(
            $shipment
        )->addObject(
            $shipment->getOrder()
        )->addObject(
            $shipment->getVendorOrder()
        )->save();

        return $this;
    }

    /**
     * @param $vendorOrder
     * @return bool
     * @throws LocalizedException
     */
    public function generateInvoice($vendorOrder)
    {
        $orderId = $vendorOrder->getOrderId();
        $order = $this->orderRepository->get($orderId);
        $vendorOrderId = $vendorOrder->getVendorOrderId();
        if ($vendorOrder->canInvoice()) {
            $items = [];
            $data = [];
            foreach ($vendorOrder->getItems() as $item) {
                $liableEntitiesForDiscount[$item->getVendorOrderId()] =
                    $this->discountProcessor->calculateVendorDiscountAmount($item, $item->getVendorId());
            }

            foreach ($order->getItemsCollection() as $item) {
                if ($vendorOrderId === $item->getVendorOrderId()) {
                    if(null !== $item->getParentItemId()) {
                        $items[$item->getParentItemId()] =  $item->getQtyOrdered();
                    } else {
                        $items[$item->getId()] =  $item->getQtyOrdered();
                    }
                }
            }
            $invoiceItems = $items;

            $invoice = $this->invoiceService->prepareInvoice(
                $order,
                $invoiceItems,
                $vendorOrder->getVendorOrderId()
            );

            if($invoice) {
                $invoice = $this->invoiceService->processInvoiceData(
                    $invoice,
                    $order,
                    $invoiceItems,
                    $vendorOrder,
                    $this->salesHelper->getConfig('commission/payout/shipping_liability', ScopeInterface::SCOPE_WEBSITE)
                );

                if (!$invoice) {
                    throw new LocalizedException(__('We can\'t save the invoice right now.'));
                }

                if (!$invoice->getTotalQty()) {
                    throw new LocalizedException(
                        __('You can\'t create an invoice without products.')
                    );
                }
                $this->_registry->register('current_invoice', $invoice);

                $invoice->register();

                $invoice->getOrder()->setCustomerNoteNotify(!empty($data['send_email']));
                $invoice->getOrder()->setIsInProcess(true);

                $transactionSave = $this->_transaction->create()
                    ->addObject($invoice)
                    ->addObject($invoice->getOrder());

                if ($vendorOrder->getId()) {
                    $liableEntityForDiscount = VendorOrder::LIABLE_ENTITY_FOR_DISCOUNT_ADMIN;
                    if (!empty($liableEntitiesForDiscount) && array_key_exists($vendorOrder->getId(), $liableEntitiesForDiscount)) {
                        $liableEntityForDiscount = ($liableEntitiesForDiscount[$vendorOrder->getId()]) ? VendorOrder::LIABLE_ENTITY_FOR_DISCOUNT_SELLER : VendorOrder::LIABLE_ENTITY_FOR_DISCOUNT_ADMIN;
                    }
                    $vendorOrder->registerInvoice($invoice, $liableEntityForDiscount);
                    $vendorOrder->setData('main_order', $invoice->getOrder());
                    $transactionSave->addObject($vendorOrder);
                }
                $transactionSave->save();
                $this->_registry->unregister('current_invoice');

                $this->callAllEvents($vendorOrder);
                return true;
            } else {
                return false;
            }
        }
        return false;
    }

    public function callAllEvents($vendorOrder) {
        // invoice generate after
        $this->_eventManager->dispatch('vendor_order_invoice_generate_after', ['order' => $vendorOrder]);

        //shipment generate after
        $this->_eventManager->dispatch('vendor_order_shipment_generate_after', ['order' => $vendorOrder]);

        //order in transit after
        $this->_eventManager->dispatch('vendor_orders_in_transit_after', ['vendor_order' => $vendorOrder]);
    }
}