<?php

namespace MDC\Sales\Plugin;

use Magedelight\Sales\Model\Order;
use Magento\Sales\Api\Data\OrderExtensionFactory;
use Magento\Sales\Api\Data\OrderExtensionInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderSearchResultInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Framework\App\ResourceConnection;

class OrderCustomDataGet
{
    const SALES_ORDER_TABLE = 'sales_order';

    /**
     * @var OrderExtensionFactory
     */
    protected $_orderExtensionFactory;

    /**
     * @var ResourceConnection
     */
    protected $_resourceConnection;

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    protected $_connection;

    /**
     * OrderCustomDataGet constructor.
     * @param OrderExtensionFactory $orderExtensionFactory
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(
        OrderExtensionFactory $orderExtensionFactory,
        ResourceConnection $resourceConnection
    ) {
        $this->_orderExtensionFactory = $orderExtensionFactory;
        $this->_resourceConnection = $resourceConnection;
        $this->_connection = $this->_resourceConnection->getConnection();
    }

    /**
     * @param OrderRepositoryInterface $subject
     * @param OrderInterface $order
     * @return OrderInterface
     */
    public function afterGet(
        OrderRepositoryInterface $subject,
        OrderInterface $order
    ) {
        $extensionAttributes = $order->getExtensionAttributes();
        $orderExtension = $extensionAttributes ? $extensionAttributes : $this->_orderExtensionFactory->create();

        $orderExtension->setDeliveredCount($this->getDeliveredCount($order));
        $orderExtension->setPendingCount($this->getPendingCount($order));
        $orderExtension->setOrderComment($order->getOrderComment());
        $orderExtension->setOrderConfirmCode($order->getOrderConfirmCode());
        $order->setExtensionAttributes($orderExtension);
        return $order;
    }

    /**
     * @param OrderRepositoryInterface $subject
     * @param OrderSearchResultInterface $searchResult
     * @return OrderSearchResultInterface
     */
    public function afterGetList(
        OrderRepositoryInterface $subject,
        OrderSearchResultInterface $searchResult
    ) {
        $orders = $searchResult->getItems();
        foreach ($orders as &$order) {
            $extensionAttributes = $order->getExtensionAttributes();
            $extensionAttributes = $extensionAttributes ? $extensionAttributes : $this->extensionFactory->create();
            $extensionAttributes->setDeliveredCount($this->getDeliveredCount($order));
            $extensionAttributes->setPendingCount($this->getPendingCount($order));
            $order->setExtensionAttributes($extensionAttributes);
        }
        return $searchResult;
    }

    /**
     * @param $order
     * @return int|void
     */
    public function getDeliveredCount($order) {
        $deliveredQuery = $this->_connection->select()
            ->from(['so' => self::SALES_ORDER_TABLE], ['customer_email', 'so.entity_id', 'so.increment_id', 'order_status' =>'so.status'])
            ->joinLeft(['mvo' => 'md_vendor_order'],'mvo.order_id = so.entity_id', ['vendor_status' => 'mvo.status'])
            ->where('customer_email=?', $order->getCustomerEmail())
            ->where('mvo.status=?',Order::STATUS_COMPLETE)
            ->group('so.entity_id');
        $deliveredResult = $this->_connection->fetchAll($deliveredQuery);
        return count($deliveredResult);
    }

    /**
     * @param $order
     * @return int|void
     */
    public function getPendingCount($order) {
        $pendingQuery = $this->_connection->select()
            ->from(['so' => self::SALES_ORDER_TABLE], ['customer_email', 'so.entity_id', 'so.increment_id', 'order_status' =>'so.status'])
            ->joinLeft(['mvo' => 'md_vendor_order'],'mvo.order_id = so.entity_id', ['vendor_status' => 'mvo.status'])
            ->where('customer_email=?', $order->getCustomerEmail())
            ->where('mvo.status=?',Order::STATUS_PENDING)
            ->group('so.entity_id');
        $pendingResult = $this->_connection->fetchAll($pendingQuery);
        return count($pendingResult);
    }
}