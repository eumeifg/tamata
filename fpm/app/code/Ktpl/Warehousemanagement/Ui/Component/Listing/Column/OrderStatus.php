<?php
namespace Ktpl\Warehousemanagement\Ui\Component\Listing\Column;

use \Magento\Sales\Api\OrderRepositoryInterface;
use \Magento\Framework\View\Element\UiComponent\ContextInterface;
use \Magento\Framework\View\Element\UiComponentFactory;
use \Magento\Ui\Component\Listing\Columns\Column;
use \Magento\Framework\Api\SearchCriteriaBuilder;
use \Magento\Sales\Model\Order\Shipment;

class OrderStatus extends Column
{

    protected $_orderRepository;
    protected $_searchCriteria;
    protected $_customfactory;
    protected $_shipmentCollection;

    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        OrderRepositoryInterface $orderRepository,
        SearchCriteriaBuilder $criteria,
        Shipment $shipmentCollection,
        array $components = [], array $data = [])
    {
        $this->_orderRepository = $orderRepository;
        $this->_searchCriteria  = $criteria;
        $this->_shipmentCollection = $shipmentCollection;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $main_order_id = $item["main_order_id"];

                if (!empty($main_order_id)) {
                    if ((substr($item["main_order_id"], 0, 1)===\Ktpl\Tookan\Model\ResourceModel\ReturnsExport\Grid\Collection::RETURN_ID_PREFIX )|| (substr($item["main_order_id"], 0, 1)===\Ktpl\Tookan\Model\ResourceModel\OrderExport\Grid\Collection::ORDER_ID_PREFIX) ) {
                        $main_order_id= substr($item["main_order_id"], 1);
                    }
                    $shipment = $this->_shipmentCollection->load($main_order_id);
                    $orderId = $shipment->getOrderId();

                    if (!empty($orderId)) {
                        $order  = $this->_orderRepository->get($orderId);
                        $order_status = $order->getStatus();

                        if (!empty($order_status))
                            $item[$this->getData('name')] = $order_status;
                    }
                }
            }
        }
        return $dataSource;
    }
}
