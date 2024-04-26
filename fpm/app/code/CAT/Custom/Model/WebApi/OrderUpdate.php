<?php

namespace CAT\Custom\Model\WebApi;

use CAT\Custom\Api\OrderUpdateInterface;
use Ktpl\Tookan\Model\Config\Source\TookanStatus;
use Magedelight\Sales\Model\Config\Source\Order\CancelledBy;
use Magedelight\Sales\Model\Order as VendorOrder;
use Magedelight\Sales\Model\Order as VendorOrderModel;
use Magedelight\Sales\Model\ResourceModel\Order\CollectionFactory;
use Magento\Sales\Api\Data\OrderInterfaceFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magedelight\Sales\Api\OrderRepositoryInterface;
use Magedelight\Sales\Api\Data\CustomMessageInterfaceFactory;
use Magento\Framework\App\ResourceConnection;
use Ktpl\BarcodeGenerator\Model\Barcode;
use Magento\Framework\HTTP\PhpEnvironment\RemoteAddress;
use Ktpl\Warehousemanagement\Model\WarehousemanagementFactory;
use Magento\Sales\Model\Order as CoreOrder;
use Magento\Sales\Model\Order\Item as CoreOrderItem;
use MDC\Sales\Model\Source\Order\PickupStatus;
use Magento\Framework\Event\ManagerInterface as EventManagerInterface;
use Magento\Framework\Webapi\Exception;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Psr\Log\LoggerInterface;
use Magento\Framework\DB\Transaction as Transaction;
use Magento\CustomerBalance\Helper\Data;
use MDC\Sales\Model\Sales\Order\Creditmemo\Save as CreditMemoSave;
use Magento\Framework\DB\TransactionFactory;

class OrderUpdate implements OrderUpdateInterface
{
    /**
     * @var OrderInterfaceFactory
     */
    protected $orderFactory;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var \CAT\Custom\Model\WebApi\ProcessOrder
     */
    protected $processOrder;

    /**
     * @var \Magedelight\Sales\Api\Data\CustomMessageInterfaceFactory
     */
    protected $customMessageInterface;

    /**
     * @var OrderRepositoryInterface
     */
    protected $vendorOrderRepository;

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
     * @var ResourceConnection
     */
    protected $resourceConnection;

    /**
     * @var AdapterInterface
     */
    protected $_connection;

    /**
     * @var EventManagerInterface
     */
    protected $eventManager;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var Transaction
     */
    protected $transaction;

    /**
     * @var Data
     */
    protected $customerBalanceDataHelper;

    /**
     * @var CreditMemoSave
     */
    protected $creditMemoSave;

    /**
     * @param OrderInterfaceFactory $orderFactory
     * @param CollectionFactory $collectionFactory
     * @param ProcessOrder $processOrder
     * @param CustomMessageInterfaceFactory $customMessageInterface
     * @param OrderRepositoryInterface $vendorOrderRepository
     * @param ResourceConnection $resourceConnection
     * @param Barcode $barcode
     * @param RemoteAddress $remoteAddress
     * @param WarehousemanagementFactory $wareHouseManagement
     * @param EventManagerInterface $eventManager
     * @param LoggerInterface $logger
     * @param Transaction $transaction
     * @param Data $customerBalanceDataHelper
     * @param CreditMemoSave $creditMemoSave
     * @param TransactionFactory $transactionFactory
     */
    public function __construct(
        OrderInterfaceFactory $orderFactory,
        CollectionFactory $collectionFactory,
        ProcessOrder $processOrder,
        CustomMessageInterfaceFactory $customMessageInterface,
        OrderRepositoryInterface $vendorOrderRepository,
        ResourceConnection $resourceConnection,
        Barcode $barcode,
        RemoteAddress $remoteAddress,
        WarehousemanagementFactory $wareHouseManagement,
        EventManagerInterface $eventManager,
        LoggerInterface $logger,
        Transaction $transaction,
        Data $customerBalanceDataHelper,
        CreditMemoSave $creditMemoSave,
        TransactionFactory $transactionFactory
    ) {
        $this->orderFactory = $orderFactory;
        $this->collectionFactory = $collectionFactory;
        $this->processOrder = $processOrder;
        $this->customMessageInterface = $customMessageInterface;
        $this->vendorOrderRepository = $vendorOrderRepository;
        $this->resourceConnection = $resourceConnection;
        $this->barcode = $barcode;
        $this->remoteAddress = $remoteAddress;
        $this->wareHouseManagement = $wareHouseManagement;
        $this->eventManager = $eventManager;
        $this->logger = $logger;
        $this->transaction = $transaction;
        $this->customerBalanceDataHelper = $customerBalanceDataHelper;
        $this->creditMemoSave = $creditMemoSave;
        $this->saveTransaction = $transactionFactory->create();
        $this->_connection = $this->resourceConnection->getConnection();
    }

    /**
     * @param string $orderId
     * @param \Magento\Tests\NamingConvention\true\mixed $items
     * @return bool|void
     * @throws Exception
     */
    public function execute($orderId, $items)
    {
        try{
            if(empty($items)) {
                throw new Exception (__("Invalid request data"), Exception::HTTP_NOT_FOUND);
            }
            $order = $this->orderFactory->create()->loadByIncrementId($orderId);
            if(!$order->getId()) {
                throw new Exception (__('No order exist.'), Exception::HTTP_NOT_FOUND);
            }
            foreach($items as $key => $value) {
                $order->setData($key, $value);
            }
            if($order->save()) {return true;}
        } catch(NoSuchEntityException $e) {
            throw new Exception (__($e->getMessage()), Exception::HTTP_NOT_FOUND);
        }
    }

    /**
     * @param string $id
     * @param \Magento\Tests\NamingConvention\true\mixed $items
     * @return bool|void
     * @throws Exception
     */
    public function updateSubOrder($id, $items) {
        try{
            if(empty($items)) {
                throw new Exception (__("Invalid request data"), Exception::HTTP_NOT_FOUND);
            }
            $subOrderCollection = $this->collectionFactory->create()
            ->addFieldToFilter('increment_id', ['eq' => $id]);
            if($subOrderCollection->getSize()) {
                /** @var \Magedelight\Sales\Model\Order $subOrder */
                $subOrder = $subOrderCollection->getFirstItem();
                foreach($items as $key => $value) {
                    if ($key === 'in_transit' ) {
                        $this->processOrder->processOrder($subOrder);
                        $subOrder->setIsSortedTimestamp(date('Y-m-d h:i:s'));
                    }
                    if ($key === 'parent_order') {
                        $order = $this->orderFactory->create()->load($subOrder->getOrderId());
                        foreach ($value as $_key => $_value) {
                            $order->setData($_key, $_value);
                        }
                        $order->save();
                    }
                    $subOrder->setData($key, $value);
                }
                if($subOrder->save()) {return true;}
            } else {
                throw new Exception (__('No Record found.'), Exception::HTTP_NOT_FOUND);
            }
        } catch(NoSuchEntityException $e) {
            throw new Exception (__($e->getMessage()), Exception::HTTP_NOT_FOUND);
        }
    }

    /**
     * Update sub-order pickup status
     * Request json {"vendorOrderId": "206605","pickupStatus": "1"}
     * Method PUT
     * @param int $vendorOrderId
     * @param int $pickupStatus
     * @return \Magedelight\Sales\Api\Data\CustomMessageInterface|void
     */
    public function updateStatus($vendorOrderId, $pickupStatus)
    {
        $customMessage = $this->customMessageInterface->create();
        try {
            $vendorOrder = $this->vendorOrderRepository->getById($vendorOrderId);
            if ($vendorOrder->getPickupStatus() == 1) {
                $customMessage->setMessage(__('Already Updated.'));
                $customMessage->setStatus(false);
                return $customMessage;
            }
            $vendorOrder->setPickupStatus($pickupStatus);
            $vendorOrder->setIsPickedUpTimestamp(date('Y-m-d h:i:s'));
            $this->vendorOrderRepository->save($vendorOrder);
            $customMessage->setMessage(__('Pickup status successfully updated.'));
            $customMessage->setStatus(true);
            return $customMessage;
        } catch (\Exception $exception) {
            $customMessage->setMessage(__('Failed to update pickup status.'));
            $customMessage->setStatus(false);
            return $customMessage;
        }
    }

    /**
     * @param int $vendorOrderId
     * Request Param {"vendorOrderId": 206605}
     * Method PUT
     * @return \Magedelight\Sales\Api\Data\CustomMessageInterface|void
     */
    public function warehouseOrderDelivery($vendorOrderId)
    {
        $customMessage = $this->customMessageInterface->create();
        try {
            $vendorOrder = $this->vendorOrderRepository->getById($vendorOrderId);
            if ($vendorOrder->getVendorOrderId()) {
                $selectQuery = $this->_connection->select()
                    ->from('sales_shipment', 'increment_id')
                    ->where('vendor_order_id=?', $vendorOrder->getVendorOrderId());
                $shipmentId = $this->_connection->fetchOne($selectQuery);
                if (empty($shipmentId)) {
                    $customMessage->setMessage(__('No Shipment ID found for this sub order!'));
                    $customMessage->setStatus(false);
                    return $customMessage;
                }
                $lineItemData = $this->barcode->getItemDataFromBarcode($shipmentId);
                $visitorIp = $this->remoteAddress->getRemoteAddress();
                if(is_array($lineItemData)) {
                    foreach($lineItemData as $key => $singleRaw) {
                        $singleRaw['barcode_number'] = $shipmentId;
                        $singleRaw['product_location'] = 1;
                        $singleRaw['order_event'] = 2;
                        $singleRaw['ip_address'] = $visitorIp;
                        $singleRaw['user_id'] = 0;
                        $model = $this->wareHouseManagement->create();
                        $warehouseCollection = $model->getCollection()
                            ->addFieldToFilter('product_location',$singleRaw['product_location'])
                            ->addFieldToFilter('barcode_number',$singleRaw['barcode_number'])
                            ->addFieldToFilter('product_id',$singleRaw['product_id']);
                        if (!$warehouseCollection->getSize()) {
                            $model->setData($singleRaw)->save();
                            // update Tookan status
                            $this->barcode->updateTookanStatus($shipmentId, TookanStatus::OUT_FOR_DELIVERY);
                            $vendorOrder->setStatus(
                                VendorOrder::STATUS_OUT_WAREHOUSE
                            )->setPickupStatus(
                                PickupStatus::PICKED
                            )->setOutOfWarehouseTimestamp(
                                date('Y-m-d h:i:s')
                            )->save();
                            /** Event for updating the out of warehouse status */
                            $this->eventManager->dispatch('vendor_orders_out_warehouse_after', ['vendor_order' => $vendorOrder]);
                        }
                    }
                }
                $customMessage->setMessage(__('Item is moved to out of warehouse.'));
                $customMessage->setStatus(true);
                return $customMessage;
            } else {
                $customMessage->setMessage(__('No Record found!'));
                $customMessage->setStatus(false);
                return $customMessage;
            }
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            $customMessage->setMessage(__($e->getMessage()));
            $customMessage->setStatus(false);
            return $customMessage;
        }
    }

    /**
     * @param \Magento\Tests\NamingConvention\true\mixed $subOrderIds
     * @param int $isPaid
     * @return bool
     * @throws Exception
     */
    public function bulkUpdateInvoicePaid($subOrderIds, $isPaid)
    {
        try {
            if(!empty($subOrderIds)) {
                $where = 'vendor_order_id IN (' . implode(',', $subOrderIds) . ')';
                $this->_connection->update('md_vendor_order', ['invoice_paid' => $isPaid, 'paid_date' => date('Y-m-d h:i:s')], $where);
                return true;
            }
            return false;
        } catch (\Exception $e) {
            throw new Exception (__($e->getMessage()), Exception::HTTP_NOT_FOUND);
        }
    }

    /**
     * @param int $id
     * @return bool
     */
    public function checkSubOrder($id)
    {
        try {
            /** @var OrderRepositoryInterface $order */
            $order = $this->vendorOrderRepository->getById($id);
            if ($order) {return true;}
            return false;
        } catch (\Exception $e) {
            $this->logger->critical(__('Vendor Order ID: %1 and Error Message: %2', [$id, $e->getMessage()]));
            return false;
        }
    }

    /**
     * @param \Magento\Tests\NamingConvention\true\mixed $items
     * @return array|void
     */
    public function updateBulkSubOrder($items)
    {
        if ($items) {
            $err = [];
            foreach ($items as $subOrderId => $otherColumn) {
                try {
                    $subOrder = $this->vendorOrderRepository->getById($subOrderId);
                    if (isset($otherColumn['in_transit']) && $otherColumn['in_transit']) {
                        /** Process the suborder... **/
                        $this->processOrder->processOrder($subOrder);
                        $otherColumn['is_sorted_timestamp'] = date('Y-m-d h:i:s');
                        unset($otherColumn['in_transit']);
                    }
                    if (array_key_exists('parent_order', $otherColumn) && !empty($otherColumn['parent_order'])) {
                        /** update the main order value **/
                        $order = $this->orderFactory->create()->load($subOrder->getOrderId());
                        $order->addData($otherColumn['parent_order'])->save();
                        unset($otherColumn['parent_order']);
                    }
                    $subOrder->addData($otherColumn)->save();

                } catch (\Exception $e) {
                    $err[0][] = $subOrderId;
                }
            }
            return $err;
        }
    }

    /**
     * @param int $vendorOrderId
     * @return \Magedelight\Sales\Api\Data\CustomMessageInterface|void
     */
    public function cancelSubOrder($vendorOrderId)
    {
        $vendorOrder = $this->vendorOrderRepository->getById($vendorOrderId);
        $order = $this->orderFactory->create()->load($vendorOrder->getOrderId());
        if ($order && $vendorOrder && $vendorOrder->canCancel()) {
            try {
                $this->eventManager->dispatch(
                    'admin_orders_cancel_after',
                    ['order' => $order, 'vendor_order' => $vendorOrder]
                );

                $customerBalanceHelper = $this->customerBalanceDataHelper;

                if($customerBalanceHelper->isAutoRefundEnabled() && $vendorOrder->getStatus() === VendorOrderModel::STATUS_PENDING || $vendorOrder->getStatus() === VendorOrderModel::STATUS_CONFIRMED|| $vendorOrder->getStatus() === VendorOrderModel::STATUS_PROCESSING ){
                    if($this->checkRemainingItems($order)) {
                        /* Store credit refund on cancel order by admin(do as vendor) */
                        $this->eventManager->dispatch('refund_store_credit_order_cancel_after', ['order' => $order, "sub_order_total"=> $order->getBaseCustomerBalanceAmount(), 'vendor_order_id' => $vendorOrder->getVendorOrderId()]);
                        /* Store credit refund on cancel order by admin(do as vendor) */
                    }
                }

                $orderCancelledAfterInvAndShp = false;
                /*if order is cancelled after invoiced/shipment create credit memo manually first */
                if($vendorOrder->getStatus() === VendorOrderModel::STATUS_PACKED || $vendorOrder->getStatus() === VendorOrderModel::STATUS_SHIPPED || $vendorOrder->getStatus() === VendorOrderModel::STATUS_HANDOVER || $vendorOrder->getStatus() === VendorOrderModel::STATUS_IN_TRANSIT  || $vendorOrder->getStatus() === VendorOrderModel::STATUS_OUT_WAREHOUSE ){

                    $orderCancelledAfterInvAndShp = true;

                    //$saveCreditMemo = $this->_objectManager->create(\MDC\Sales\Model\Sales\Order\Creditmemo\Save::class);
                    $params['order_id'] = $order->getId();
                    $params['do_as_vendor'] = $vendorOrder->getVendorId();
                    $params['vendor_order_id'] = $vendorOrder->getVendorOrderId();
                    $this->creditMemoSave->generateCreditMemoWithCancelOrder($vendorOrder,$params,$order);
                }
                /*if order is cancelled after invoiced/shipment create credit memo manually first */

                $payment = $order->getPayment();
                $payment->setBaseAmountAuthorized($vendorOrder->getGrandTotal());
                $payment->setAmountAuthorized($vendorOrder->getGrandTotal());
                $payment->cancel();
                $vendorOrder->registerCancel($order, $orderCancelledAfterInvAndShp);
                $vendorOrder->setData('cancelled_by', CancelledBy::MERCHANT);

                /*** Added fixes for order cancel issue when coupon code applied unable to cancel ***/
                $cancelledItems = [];
                $itemsCount = [];
                foreach ($order->getAllItems() as $item) {
                    if ($item->getParentItemId()) {
                        continue;
                    }
                    $itemsCount[] =  $item->getId();
                    if ($item->getStatusId() === CoreOrderItem::STATUS_CANCELED) {
                        $cancelledItems[] = $item->getId();
                    }
                }

                if (count($itemsCount) === count($cancelledItems)) {
                    $state = CoreOrder::STATE_CANCELED;
                    $order->setState($state)
                        ->setStatus($order->getConfig()->getStateDefaultStatus($state));
                }

                $this->saveTransaction->addObject($vendorOrder)->addObject($order)->save();

                $customMessage = $this->customMessageInterface->create();
                $customMessage->setMessage(__('The order has been canceled.'));
                $customMessage->setStatus(true);
                return $customMessage;
            } catch (\Exception $e) {
                $this->logger->critical($e);
                $customMessage = $this->customMessageInterface->create();
                $customMessage->setMessage(__('The order was not canceled, Please try again later.'));
                $customMessage->setStatus(false);
                return $customMessage;
            }
        }
    }

    /**
     * @param CoreOrder $order
     * @return bool
     */
    public function checkRemainingItems(\Magento\Sales\Model\Order $order) {
        $i = 0; $j = 0;
        foreach ($order->getAllVisibleItems() as $item) {
            if($item->getQtyOrdered() > ($item->getQtyCanceled() + $item->getQtyRefunded())) {
                $j = 1; $j += $i; $i++;
            }
        }
        if ($j === 1) { return true; }
        return false;
    }
}
