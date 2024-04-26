<?php

namespace MDC\Sales\Model\WebApi;

use MDC\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\OrderFactory;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\FilterGroupBuilder;
use Magento\Customer\Model\CustomerFactory;


/**
 * OrderRepository class
 */
class OrderRepository implements OrderRepositoryInterface {

    const SHIPPED_STATUSES = ['shipped', 'in_transit','out_warehouse', 'complete'];

    const NOT_SHIPPED_STATUSES = ['pending', 'confirmed', 'processing', 'packed'];

    /**
     * @var OrderFactory
     */
    protected $_orderFactory;

    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    protected $_orderRepositoryInterface;

    /**
     * @var ResourceConnection
     */
    protected $resourceConnection;

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    protected $connection;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var FilterBuilder
     */
    protected $filterBuilder;

    /**
     * @var FilterGroupBuilder
     */
    protected $filterGroupBuilder;

    /**
     * @var CustomerFactory
     */
    protected $customerFactory;

    /**
     * @param OrderFactory $orderFactory
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepositoryInterface
     * @param ResourceConnection $resourceConnection
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param FilterBuilder $filterBuilder
     * @param FilterGroupBuilder $filterGroupBuilder
     * @param CustomerFactory $customerFactory
     */
    public function __construct(
        OrderFactory $orderFactory,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepositoryInterface,
        ResourceConnection $resourceConnection,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        FilterBuilder $filterBuilder,
        FilterGroupBuilder $filterGroupBuilder,
        CustomerFactory $customerFactory
    ) {
        $this->_orderFactory = $orderFactory;
        $this->_orderRepositoryInterface = $orderRepositoryInterface;
        $this->resourceConnection = $resourceConnection;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->filterBuilder         = $filterBuilder;
        $this->filterGroupBuilder    = $filterGroupBuilder;
        $this->customerFactory = $customerFactory;
        $this->connection = $this->resourceConnection->getConnection();
    }

    public function getOrderList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria) {
        $filterGroup = $searchCriteria->getFilterGroups();
        $field = [];
        $filterArray = [];
        foreach ($filterGroup as $group) {
            $filters = $group->getFilters();
            foreach ($filters as $filter) {
                //checking the filter value
                $field[] = $filter->getField();
                $filterArray[$filter->getField()] = ['field' => $filter->getField(), 'value' => $filter->getValue(), 'condition_type' => $filter->getConditionType()];
            }
        }
        if (array_key_exists('vendor_name', $filterArray)) {
            $vendorFilterArray = $filterArray['vendor_name'];
            $select = $this->connection->select()->from(['mvo' => 'md_vendor_order'], 'mvo.order_id')
                ->joinLeft(['mvwd' => 'md_vendor_website_data'], 'mvo.vendor_id = mvwd.vendor_id')
                ->where('mvwd.name=?',$vendorFilterArray['value']);

            $result = $this->connection->fetchCol($select);
            unset($filterArray['vendor_name']);
            if (!empty($result)) {
                $result = array_unique($result);
                $orderIds = implode(',', $result);
                $filter1 = $this->filterBuilder->setField('entity_id')
                    ->setValue($orderIds)
                    ->setConditionType('in')
                    ->create();
                $filter_group1 = $this->filterGroupBuilder
                    ->addFilter($filter1)
                    ->create();
                $filterGroup1Array = [$filter_group1];
                $filterGroupArray = [];
                if (count($filterArray) > 0) {
                    $i = 2;
                    foreach ($filterArray as $filterKey => $_filters) {
                        ${"filterKey".$i} = $this->filterBuilder->setField($_filters['field'])
                            ->setValue($_filters['value'])
                            ->setConditionType($_filters['condition_type'])
                            ->create();
                        ${"filter_group".$i} = $this->filterGroupBuilder
                            ->addFilter(${"filterKey".$i})
                            ->create();
                        $filterGroupArray[] = ${"filter_group".$i};
                        $i++;
                    }
                }

                $finalGroupArray = array_merge($filterGroup1Array, $filterGroupArray);

                $searchCriteriaVendorName = $this->searchCriteriaBuilder
                    ->setFilterGroups($finalGroupArray)
                    ->create();
                if ($searchCriteria->getPageSize()) {
                    $searchCriteriaVendorName->setPageSize($searchCriteria->getPageSize());
                }
                if ($searchCriteria->getCurrentPage()) {
                    $searchCriteriaVendorName->setCurrentPage($searchCriteria->getCurrentPage());
                }
            }
            $_searchCriteria = $searchCriteriaVendorName;
        } elseif (array_key_exists('sku', $filterArray)) {
            $select = $this->connection->select()->from('sales_order_item', 'order_id')
                ->where('sku=?',$filter->getValue())
                ->where('parent_item_id IS NULL');
            $result = $this->connection->fetchCol($select);
            unset($filterArray['sku']);
            if (!empty($result)) {
                $result = array_unique($result);
                $orderIds = implode(',', $result);
                $filter1 = $this->filterBuilder->setField('entity_id')
                    ->setValue($orderIds)
                    ->setConditionType('in')
                    ->create();
                $filter_group1 = $this->filterGroupBuilder
                    ->addFilter($filter1)
                    ->create();
                $filterGroup1Array = [$filter_group1];
                $filterGroupArray = [];
                if (count($filterArray) > 0) {
                    $i = 2;
                    foreach ($filterArray as $filterKey => $_filters) {
                        ${"filterKey".$i} = $this->filterBuilder->setField($_filters['field'])
                            ->setValue($_filters['value'])
                            ->setConditionType($_filters['condition_type'])
                            ->create();
                        ${"filter_group".$i} = $this->filterGroupBuilder
                            ->addFilter(${"filterKey".$i})
                            ->create();
                        $filterGroupArray[] = ${"filter_group".$i};
                        $i++;
                    }
                }

                $finalGroupArray = array_merge($filterGroup1Array, $filterGroupArray);

                $searchCriteriaSku = $this->searchCriteriaBuilder
                    ->setFilterGroups($finalGroupArray)
                    ->create();
                if ($searchCriteria->getPageSize()) {
                    $searchCriteriaSku->setPageSize($searchCriteria->getPageSize());
                }
                if ($searchCriteria->getCurrentPage()) {
                    $searchCriteriaSku->setCurrentPage($searchCriteria->getCurrentPage());
                }
            }
            $_searchCriteria = $searchCriteriaSku;
        } else {
            $_searchCriteria = $searchCriteria;
        }
        $getList = $this->_orderRepositoryInterface->getList($_searchCriteria);
        foreach ($getList->getItems() as $items) {
            $this->setIds($items->getEntityId(), $items);
            foreach ($items->getItems() as $item) {
                if ($item->getVendorId()) {
                    $select = $this->connection->select()->from(['mvwd' => 'md_vendor_website_data'], 'mvwd.name')
                        ->where('mvwd.vendor_id=?',$item->getVendorId());
                    $vendorName = $this->connection->fetchOne($select);
                    $item->setVendorName($vendorName);
                }
            }
        }
        return $getList;
    }

    public function setIds($id, $item) {
        $order = $this->_orderFactory->create()->load($id);
        $item->setShelfNumber($order->getShelfNumber());
        $item->setShippingHistory($order->getShippingHistory());
        $item->setSortingHistory($order->getSortingHistory());
        $item->setReadyToShip($order->getReadyToShip());
        $item->setStoreCreditUse($order->getCustomerBalanceAmount());
        $item->setIsLoaded($order->getIsLoaded());
        $item->setIsShelvedTimestamp($order->getIsShelvedTimestamp());
        $item->setReadyToShipTimestamp($order->getReadyToShipTimestamp());
        $item->setContainerType($order->getContainerType());
        $selectQuery = $this->connection->select()->from('md_vendor_order', ['status', 'cancelled_by'])->where('order_id=?',$id);
        $result = $this->connection->fetchAll($selectQuery);

        if(!empty($result)) {
            $status = [];
            $canceledBy = [];
            $inBoxStatuses = [];
            $i = 0;
            foreach ($result as $value) {
                $status[] = $value['status'];
                $canceledBy[] = $value['cancelled_by'];
                if(!in_array($value['status'], ['canceled', 'closed'])) {
                    if(in_array($value['status'], self::SHIPPED_STATUSES)) {
                        $i += 1;
                        $inBoxStatuses[] = 1;
                    } elseif (in_array($value['status'],self::NOT_SHIPPED_STATUSES)) {
                        $inBoxStatuses[] = 0;
                    }
                } else {
                    $inBoxStatuses[] = 2;
                }
            }

            $inBoxStatus = array_unique($inBoxStatuses);
            if(in_array(1, $inBoxStatus) && !in_array(0, $inBoxStatus)) {
                $inboxVal = 'all';
            } elseif(!in_array(1, $inBoxStatus) && in_array(0, $inBoxStatus)) {
                $inboxVal = 'none';
            } elseif(in_array(1, $inBoxStatus) && in_array(0, $inBoxStatus)) {
                $inboxVal = 'partial';
            } else {
                $inboxVal = '';
            }
            if(count($result) > 0) {
                $itemCounter = $i.' of '. count($result);
                $item->setItemCounter($itemCounter);
            }

            $item->setInBoxStatus($inboxVal);
            $statusArray = array_unique($status);
            if(count($statusArray) > 0) {
                $statusStr = implode(',', $statusArray);
                $item->setSubOrderStatuses($statusStr);
            }

            $canceledByArray = array_unique($canceledBy);
            $voCanceledBy = count($canceledByArray) > 0 ? implode(',', $canceledByArray) : null;
            $item->setVoCanceledBy($voCanceledBy);

            $shippingFlat = $this->connection->select()
                ->from(['mcsfoa' => 'magento_customercustomattributes_sales_flat_order_address'], ['latitude', 'longitude'])
                ->where('entity_id=?',$order->getShippingAddress()->getEntityId());
            $shippingResult = $this->connection->fetchRow($shippingFlat);

            if (isset($shippingResult['latitude']) && !empty($shippingResult['latitude'])) {
                $item->setLatitude($shippingResult['latitude']);
                $item->setLongitude($shippingResult['longitude']);
            }
            /*$customerAddressId = (int) $order->getShippingAddress()->getCustomerAddressId();

            $latitude = $order->getShippingAddress()->getLatitude();
            $longitude = $order->getShippingAddress()->getLongitude();
            if (!empty($latitude)) {
                $item->setLatitude($latitude);
                $item->setLongitude($longitude);
            } else {
                if ($customerAddressId) {
                    $customer = $this->customerFactory->create()->load($order->getCustomerId());
                    $customerAddress = $customer->getAddressById($customerAddressId);
                    $latitude = $customerAddress->getCustomAttribute('latitude') ? $customerAddress->getCustomAttribute('latitude')->getValue() : null;
                    $longitude = $customerAddress->getCustomAttribute('longitude') ? $customerAddress->getCustomAttribute('longitude')->getValue() : null;
                    $item->setLatitude($latitude);
                    $item->setLongitude($longitude);
                }
            }*/
        }
    }
}
