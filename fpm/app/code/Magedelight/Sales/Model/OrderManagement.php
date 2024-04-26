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
namespace Magedelight\Sales\Model;

use Magedelight\Sales\Api\OrderManagementInterface;
use Magedelight\Sales\Model\Config\Source\Order\CancelledBy;
use Magedelight\Sales\Model\Order as VendorOrder;
use Magento\Framework\DB\Transaction as Transaction;
use Magento\Framework\Message\ManagerInterface as MessageManagerInterface;
use Magento\Sales\Model\Order as CoreOrder;
use Magento\Sales\Model\Order\Item as CoreOrderItem;
use Psr\Log\LoggerInterface as PsrLogger;

class OrderManagement implements OrderManagementInterface
{
    const BILLING_ALIAS = 'billing_o_a';
    const SHIPPING_ALIAS = 'shipping_o_a';
    const BILL_TO_FIRST_NAME_FIELD = 'billing_o_a.firstname';
    const BILL_TO_LAST_NAME_FIELD = 'billing_o_a.lastname';
    const SHIP_TO_FIRST_NAME_FIELD = 'shipping_o_a.firstname';
    const SHIP_TO_LAST_NAME_FIELD = 'shipping_o_a.lastname';

    /**
     * @var \Magento\Sales\Model\Order
     */
    protected $order;

    /**
     * @var Transaction
     */
    protected $transaction;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $dateTime;

    /**
     * @var MessageManagerInterface
     */
    protected $messageManager;

    /**
     * @var PsrLogger
     */
    protected $logger;

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $eventManager;

    /**
     * @var \RB\User\Api\OrderInterface
     */
    protected $orderInterface;

    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @var \Magento\Sales\Model\Order\Address\Renderer
     */
    protected $addressRenderer;

    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $orderFactory;

    /**
     * @var \Magento\Store\Model\StoreRepository
     */
    protected $storeRepository;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\CollectionFactory
     */
    protected $orderCollectionFactory;

    protected $storeIds = [];

    protected $_objectManager;

    /**
     * @var \Magedelight\Sales\Api\OrderRepositoryInterface
     */
    protected $vendorOrderRepository;
    /**
     * @var \Magento\Sales\Api\OrderManagementInterface
     */
    private $orderManagement;

    /**
     * OrderManagement constructor.
     * @param \Magento\Authorization\Model\UserContextInterface $userContext
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Store\Model\StoreRepository $storeRepository
     * @param \Magento\Sales\Model\Order $order
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param \Magento\Sales\Model\Order\Address\Renderer $addressRenderer
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param PsrLogger $logger
     * @param MessageManagerInterface $messageManager
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $dateTime
     * @param Transaction $transaction
     * @param ResourceModel\Order\CollectionFactory $vendorOrderCollectionFactory
     * @param \Magento\Framework\ObjectManagerInterface $objectmanager
     * @param \Magedelight\Sales\Api\Data\CustomMessageInterfaceFactory $customMessageInterface
     * @param \Magedelight\Sales\Api\OrderRepositoryInterface $vendorOrderRepository
     */
    public function __construct(
        \Magento\Authorization\Model\UserContextInterface $userContext,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Store\Model\StoreRepository $storeRepository,
        \Magento\Sales\Model\Order $order,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Sales\Model\Order\Address\Renderer $addressRenderer,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        PsrLogger $logger,
        MessageManagerInterface $messageManager,
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime,
        Transaction $transaction,
        \Magedelight\Sales\Model\ResourceModel\Order\CollectionFactory $vendorOrderCollectionFactory,
        \Magento\Framework\ObjectManagerInterface $objectmanager,
        \Magedelight\Sales\Api\Data\CustomMessageInterfaceFactory $customMessageInterface,
        \Magedelight\Sales\Api\OrderRepositoryInterface $vendorOrderRepository,
        \Magento\Sales\Api\OrderManagementInterface $orderManagement
    ) {
        $this->userContext = $userContext;
        $this->request = $request;
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->storeManager = $storeManager;
        $this->storeRepository = $storeRepository;
        $this->orderFactory = $orderFactory;
        $this->addressRenderer = $addressRenderer;
        $this->orderRepository = $orderRepository;
        $this->eventManager = $eventManager;
        $this->logger = $logger;
        $this->messageManager = $messageManager;
        $this->dateTime = $dateTime;
        $this->transaction = $transaction;
        $this->order = $order;
        $this->vendorOrderCollectionFactory = $vendorOrderCollectionFactory;
        $this->_objectManager = $objectmanager;
        $this->customMessageInterface = $customMessageInterface;
        $this->vendorOrderRepository = $vendorOrderRepository;
        $this->orderManagement = $orderManagement;
    }

    /**
     * {@inheritdoc}
     */
    public function confirmOrder($vendorOrderId)
    {
        $vendorId = $this->userContext->getUserId();
        $vendorOrder = $this->vendorOrderRepository->getById($vendorOrderId);

        if ($vendorOrder->getVendorId() != $vendorId) {
            throw new \Exception(__('This order does not belongs to you'));
        }

        if ($vendorOrder->getId() && $vendorOrder->getIsConfirmed() != 1) {
            try {
                $vendorOrder->setData("is_confirmed", 1)
                    ->setData('confirmed_at', $this->dateTime->gmtDate())
                    ->save();
                $customMessage = $this->customMessageInterface->create();
                $customMessage->setMessage(__('The order has been confirmed.'));
                $customMessage->setStatus(true);
                $this->eventManager->dispatch('vendor_orders_confirm_after', ['vendor_order' => $vendorOrder]);
                return $customMessage;
            } catch (\Exception $e) {
                $this->logger->critical($e);
                $customMessage = $this->customMessageInterface->create();
                $customMessage->setMessage(__('The order was not confirmed'));
                $customMessage->setStatus(false);
                return $customMessage;
            }
        } else {
            throw new \Exception(__('The Order does not Exists'));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function orderCancel($vendorOrderId)
    {
        $vendorOrder = $this->vendorOrderRepository->getById($vendorOrderId);
        $order = $this->_initOrder($vendorOrder->getOrderId());

        if ($order && $vendorOrder && $vendorOrder->canCancel()) {
            try {
                $vendorOrder->registerCancel($order);
                $vendorOrder->setData('cancelled_by', CancelledBy::SELLER);
                $this->transaction->addObject($vendorOrder)
                        ->addObject($order)
                        ->save();
                $this->eventManager->dispatch(
                    'vendor_orders_cancel_after',
                    ['vendor_order_ids' => [$vendorOrder->getId()]]
                );
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
     * {@inheritdoc}
     */
    public function orderStatusUpdate($vendorOrderId, $status)
    {
        $vendorOrder = $this->vendorOrderRepository->getById($vendorOrderId);
        $statuses = [VendorOrder::STATUS_IN_TRANSIT,VendorOrder::STATUS_COMPLETE];
        if (!in_array($status, $statuses)) {
            throw new \Exception(__('Order Status cannot be changed'));
        }
        if ($status === VendorOrder::STATUS_IN_TRANSIT) {
            $statusText = __('In Transit');
        } elseif ($status === VendorOrder::STATUS_COMPLETE) {
            $statusText = __('Delivered');
        }
        if ($vendorOrder->getId()) {
            try {
                if ($vendorOrder->getStatus() == $status || $vendorOrder->getStatus() == VendorOrder::STATUS_COMPLETE) {
                    throw new \Exception(__('Order Status cannot be changed'));
                }
                $vendorOrder->setData("status", $status)->save();
                $customMessage = $this->customMessageInterface->create();
                $customMessage->setMessage(__('The order status has been changed to ' . $statusText));
                $customMessage->setStatus(true);
                return $customMessage;
            } catch (\Exception $e) {
                $this->logger->critical($e);
                $customMessage = $this->customMessageInterface->create();
                $customMessage->setMessage(__('This order status cannot be changed to ' . $statusText));
                $customMessage->setStatus(false);
                return $customMessage;
            }
        }
    }

    /**
     * Initialize order model instance
     *
     * @return \Magento\Sales\Api\Data\OrderInterface|false
     */
    protected function _initOrder($orderId)
    {
        try {
            $order = $this->orderRepository->get($orderId);
        } catch (NoSuchEntityException $e) {
            $this->messageManager->addError(__('This order no longer exists.'));
            $this->_actionFlag->set('', self::FLAG_NO_DISPATCH, true);
            $customMessage = $this->customMessageInterface->create();
            $customMessage->setMessage(__('This order no longer exists.'));
            $customMessage->setStatus(false);
            return $customMessage;
        } catch (InputException $e) {
            $this->messageManager->addError(__('This order no longer exists.'));
            $this->_actionFlag->set('', self::FLAG_NO_DISPATCH, true);
            $customMessage = $this->customMessageInterface->create();
            $customMessage->setMessage(__('This order no longer exists.'));
            $customMessage->setStatus(false);
            return $customMessage;
        }
        return $order;
    }

    /**
     * {@inheritdoc}
     */
    public function cancelFullOrderByCustomer($orderId, $cancelOrderReason = null, $cancelOrderComment = null)
    {
        try {
            $order = $this->orderRepository->get($orderId);
            // Order Cancel Process.
            if (($order->getStatus() == 'pending') && ($order->canCancel())) {
                $this->orderManagement->cancel($order);
                $order = $this->orderRepository->get($order->getId());
                $order->setData('order_cancel_reason', $cancelOrderReason);
                $this->orderRepository->save($order);
                //   set comment with cancel order
                $history = $order->addStatusHistoryComment($cancelOrderComment, 'canceled');
                $history->setIsVisibleOnFront(true);
                $history->setIsCustomerNotified(true);
                $history->save();

                /** @var OrderCommentSender $orderCommentSender */
                $orderCommentSender = $this->_objectManager
                    ->create(\Magento\Sales\Model\Order\Email\Sender\OrderCommentSender::class);
                $orderCommentSender->send($order, true, $cancelOrderComment);

                // If the full order cancel then sent email to customer
                $this->cancelSubOrders($order);
                $customMessage = $this->customMessageInterface->create();
                $customMessage->setMessage(__('The order has been successfully cancelled.'));
                $customMessage->setStatus(true);
                return $customMessage;
            }
            $customMessage = $this->customMessageInterface->create();
            $customMessage->setMessage(__('Sorry, you cannot cancel this order now.'));
            $customMessage->setStatus(false);
            return $customMessage;
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->logger->critical($e);
            $customMessage = $this->customMessageInterface->create();
            $customMessage->setMessage($e->getMessage());
            $customMessage->setStatus(false);
        } catch (\Exception $e) {
            $this->logger->critical($e);
            $customMessage = $this->customMessageInterface->create();
            $customMessage->setMessage(__('We can\'t process your request right now. Please try after some time.'));
            $customMessage->setStatus(false);
            return $customMessage;
        }
    }

    /**
     * Cancel Vendor/Sub Orders.
     * @param integer $orderId
     * @return void
     */
    public function cancelSubOrders($order)
    {
        $vendorOrderIds = [];
        $vendorOrders = $this->vendorOrderCollectionFactory->create()
            ->addFieldToFilter("order_id", $order->getId());
        foreach ($vendorOrders as $vendorOrder) {
            $vendorOrder->setData('cancelled_by', CancelledBy::BUYER);
            $vendorOrder->registerCancel($order);
            $this->_objectManager->create(
                \Magento\Framework\DB\Transaction::class
            )
            ->addObject($vendorOrder)
            ->save();
            $vendorOrderIds[] = $vendorOrder->getVendorOrderId();
        }
        $this->_eventManager->dispatch('customer_orders_cancel_after', ['vendor_order_ids' => $vendorOrderIds]);
    }

    /**
     * {@inheritdoc}
     */
    public function cancelOrderItemByCustomer(
        $orderId,
        $orderItemId,
        $cancelOrderItemReason = null,
        $cancelOrderItemComment = null
    ) {
        $isCancelFlag = false;
        $isNotifiyOrderItem = false;
        $cancelItemArray = [];
        $comment = __("Customer Canceled Order.");

        try {
            $vendorOrderIds = [];
            $order = $this->orderRepository->get($orderId);
            foreach ($order->getAllVisibleItems() as $item) {
                if (($item->getItemId() == $orderItemId) && ($item->getQtyCanceled() == '0') &&
                    ($item->getQtyShipped() == '0') && ($item->getQtyInvoiced() == '0')) {
                    $vendorOrder = $this->vendorOrderRepository->getById(
                        $item->getVendorOrderId()
                    );
                    $vendorOrder->registerCancel($order);
                    $vendorOrder->setData('cancelled_by', CancelledBy::BUYER);
                    $vendorOrderIds[] = $vendorOrder->getVendorOrderId();
                    $this->_objectManager->create(
                        \Magento\Framework\DB\Transaction::class
                    )
                    ->addObject($vendorOrder)
                    ->addObject($order)
                    ->save();
                    
                    $item->setData('cancel_item_reason', $cancelOrderItemReason);
                    $item->setData('cancel_item_comment', $cancelOrderItemComment);
                    $item->save();
                    $isNotifiyOrderItem = true;
                    $isCancelFlag = true;

                    $cancelItemArray['id'] = $item->getItemId();
                    $cancelItemArray['name'] = $item->getName();
                    $cancelItemArray['qty'] = $item->getQtyCanceled();
                    $cancelItemArray['price'] = $item->getPrice();
                    $cancelItemArray['vendorId'] = $item->getVendorId();
                }
            }
            $cancelledItems = [];
            $itemsCount = [];
            $order->setData('is_confirmed', '1');
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
                $this->orderRepository->save($order);
            }
            $history = $order->addStatusHistoryComment($comment, 'canceled');
            $history->setIsVisibleOnFront(false);
            $history->setIsCustomerNotified(true);
            $history->save();
            $isNotifiyOrderItem = false;

            if ($isCancelFlag) {
                /* Genrate Event for order cancel item email to customer vendor and admin. */
                if ($isNotifiyOrderItem) {
                    $this->eventManager->dispatch(
                        'rb_customer_order_cancel_item',
                        ['order' => $order,
                         'cancel_item_array' => $cancelItemArray]
                    );
                } else {
                    $comment = "";
                    $orderCommentSender = $this->_objectManager
                        ->create(\Magento\Sales\Model\Order\Email\Sender\OrderCommentSender::class);
                    $orderCommentSender->send($order, true, $comment);
                }
                if (count($vendorOrderIds) > 0) {
                    $this->eventManager->dispatch('customer_orders_cancel_after', ['vendor_order_ids' => [$vendorOrderIds]]);
                }
                $customMessage = $this->customMessageInterface->create();
                $customMessage->setMessage(__('The order item has been Canceled successfully.'));
                $customMessage->setStatus(true);
                return $customMessage;
            } else {
                $customMessage = $this->customMessageInterface->create();
                $customMessage->setMessage(__('We can\'t process your request right now. Please Try after some time.'));
                $customMessage->setStatus(false);
                return $customMessage;
            }
        } catch (\Exception $e) {
            $customMessage = $this->customMessageInterface->create();
            $customMessage->setMessage(__('We can\'t process your request right now. Please Try after some time.'.$e->getMessage()));
            $customMessage->setStatus(false);
            return $customMessage;
        }
    }
}
