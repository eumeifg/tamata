<?php

namespace CAT\Custom\Model\WebApi;

use CAT\Custom\Api\SortOrderInterface;
use Magento\Catalog\Api\ProductRepositoryInterfaceFactory;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\UrlInterface;
use Magento\Framework\Webapi\Rest\Request;
use Magento\Store\Model\StoreManagerInterface;

class SortOrder implements SortOrderInterface
{
    /**
     * @var Request
     */
    protected $_request;

    /**
     * @var ResourceConnection
     */
    protected $resourceConnection;

    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var ProductRepositoryInterfaceFactory
     */
    protected $_productRepositoryFactory;

    /**
     * @var AdapterInterface
     */
    protected $connection;

    /**
     * @param Request $request
     * @param ResourceConnection $resourceConnection
     * @param StoreManagerInterface $storeManager
     * @param ProductRepositoryInterfaceFactory $productRepositoryFactory
     */
    public function __construct(
        Request $request,
        ResourceConnection $resourceConnection,
        StoreManagerInterface $storeManager,
        ProductRepositoryInterfaceFactory $productRepositoryFactory
    ) {
        $this->_request = $request;
        $this->resourceConnection = $resourceConnection;
        $this->_storeManager = $storeManager;
        $this->_productRepositoryFactory = $productRepositoryFactory;
        $this->connection = $this->resourceConnection->getConnection();
    }

    /**
     * @inheritDoc
     */
    public function getOrders()
    {
        $sku = $this->_request->getParam('sku');
        $page = !empty($this->_request->getParam('page_size')) ? $this->_request->getParam('page_size') : null;
        $rowCount = !empty($this->_request->getParam('current_page')) ? $this->_request->getParam('current_page') : 0;
        $orderBy = !empty($this->_request->getParam('order_by')) ? $this->_request->getParam('order_by') : null;
        $dir = !empty($this->_request->getParam('dir')) ? ' '.$this->_request->getParam('dir') : null;
        $totalCount = 0;
        //
        try {
            if (array_key_exists('sku', $this->_request->getparams()) && !empty($sku)) {
                $selectQuery = $this->connection->select()
                    ->from('sales_order_item', ['order_id'])
                    ->where('sku=?', $sku);
                $this->filterByDate($selectQuery, $orderBy, $dir);
                $selectQuery->where('parent_item_id IS NULL');
                $result = $this->connection->fetchCol($selectQuery);
                $totalCount = count($result);
                if (!empty($result)) {
                    $where = 'so.entity_id IN ('.implode(',', $result).')';
                    $orderSelect = $this->connection->select()->from(['so' => 'sales_order'], ['so.entity_id', 'so.increment_id', 'so.total_item_count', 'so.created_at', 'so.sorting_history', 'so.customer_email', 'so.customer_group_id']);
                    $orderSelect->joinLeft(['soa' => 'sales_order_address'], 'soa.parent_id = so.entity_id and soa.address_type = "shipping"', ['address_id' => 'soa.entity_id']);
                    $orderSelect->joinLeft(['mcsfoa' => 'magento_customercustomattributes_sales_flat_order_address'], 'mcsfoa.entity_id = soa.entity_id', ['latitude', 'longitude']);
                    $orderSelect->where($where)->limit($page, $page*$rowCount);
                    $this->filterByDate($orderSelect, $orderBy, $dir);
                    $orderResult = $this->connection->fetchAll($orderSelect);

                    $_order = [];
                    foreach ($orderResult as $order) {
                        /** Get Sub Order Data */
                        $_order[$order['entity_id']] = $this->getSubOrderData($order);
                    }
                }
            } elseif(array_key_exists('vendor_id', $this->_request->getparams())) {
                $vendorId = $this->_request->getparam('vendor_id');
                $select = $this->connection->select()->from(['mvo' => 'md_vendor_order'], 'mvo.order_id');
                //$select->joinLeft(['mvwd' => 'md_vendor_website_data'], 'mvo.vendor_id = mvwd.vendor_id', '');
                $select->group('mvo.order_id');
                $select->where('mvo.vendor_id=?',$vendorId);
                if (array_key_exists('item_status', $this->_request->getparams())) {
                    $select->where('mvo.status=?',$this->_request->getparam('item_status'));
                } else {
                    //Adding a status array if item_status value is empty
                    $itemStatus = ["pending", "confirmed", "processing"];
                    $where = "mvo.status IN ('".implode("','", $itemStatus)."')";
                    $select->where($where);
                }

                $result = $this->connection->fetchCol($select);
                $totalCount = count($result);
                if (!empty($result)) {
                    $where = 'so.entity_id IN ('.implode(',', $result).')';
                    $orderSelect = $this->connection->select();
                    $orderSelect->from(['so' => 'sales_order'], ['so.entity_id', 'so.increment_id', 'so.total_item_count', 'so.created_at', 'so.sorting_history', 'so.customer_email', 'so.customer_group_id']);
                    $orderSelect->joinLeft(['soa' => 'sales_order_address'], 'soa.parent_id = so.entity_id and soa.address_type = "shipping"', ['address_id' => 'soa.entity_id']);
                    $orderSelect->joinLeft(['mcsfoa' => 'magento_customercustomattributes_sales_flat_order_address'], 'mcsfoa.entity_id = soa.entity_id', ['latitude', 'longitude']);
                    if (!empty($orderBy) && $orderBy === 'sku') {
                        $orderSelect->joinLeft(['soi' => 'sales_order_item'], 'soi.order_id = so.entity_id', ['']);
                        $orderSelect->group('so.entity_id');
                        $orderSelect->order($orderBy . $dir);
                    }
                    $this->filterByDate($orderSelect, $orderBy, $dir);
                    $orderSelect->where($where);
                    $orderSelect->limit($page, $page*$rowCount);
                    $orderResult = $this->connection->fetchAll($orderSelect);
                    $_order = [];
                    foreach ($orderResult as $order) {
                        /** Get Sub Order Data */
                        $_order[$order['entity_id']] = $this->getSubOrderData($order);
                    }
                }
            } elseif(array_key_exists('item_status', $this->_request->getParams())) {
                $itemStatus = $this->_request->getParam('item_status');
                $select = $this->connection->select()->from(['mvo' => 'md_vendor_order'], 'mvo.order_id');
                $select->group('mvo.order_id');
                $select->where('mvo.status=?',$itemStatus);
                $result = $this->connection->fetchCol($select);
                $totalCount = count($result);
                if (!empty($result)) {
                    $where = 'so.entity_id IN ('.implode(',', $result).')';
                    $orderSelect = $this->connection->select()
                        ->from(['so' => 'sales_order'], ['so.entity_id', 'so.increment_id', 'so.total_item_count', 'so.created_at', 'so.sorting_history', 'so.customer_email', 'so.customer_group_id']);
                    $orderSelect->joinLeft(['soa' => 'sales_order_address'], 'soa.parent_id = so.entity_id and soa.address_type = "shipping"', ['address_id' => 'soa.entity_id']);
                    $orderSelect->joinLeft(['mcsfoa' => 'magento_customercustomattributes_sales_flat_order_address'], 'mcsfoa.entity_id = soa.entity_id', ['latitude', 'longitude']);

                    $this->filterByDate($orderSelect, $orderBy, $dir);
                    $orderSelect->where($where)->limit($page, $page*$rowCount);
                    $orderResult = $this->connection->fetchAll($orderSelect);
                    $_order = [];
                    foreach ($orderResult as $order) {
                        /** Get Sub Order Data */
                        $_order[$order['entity_id']] = $this->getSubOrderData($order);
                    }
                }
            } elseif (array_key_exists('orderId', $this->_request->getParams()) && !empty($this->_request->getParam('orderId'))) {
                $orderId = $this->_request->getParam('orderId');
                $orderSelect = $this->connection->select()
                    ->from(['so' => 'sales_order'], ['so.entity_id', 'so.increment_id', 'so.total_item_count', 'so.created_at', 'so.sorting_history', 'so.customer_email', 'so.customer_group_id']);
                $orderSelect->joinLeft(['soa' => 'sales_order_address'], 'soa.parent_id = so.entity_id and soa.address_type = "shipping"', ['address_id' => 'soa.entity_id']);
                $orderSelect->joinLeft(['mcsfoa' => 'magento_customercustomattributes_sales_flat_order_address'], 'mcsfoa.entity_id = soa.entity_id', ['latitude', 'longitude']);

                $this->filterByDate($orderSelect, $orderBy, $dir);
                $orderSelect->where('so.increment_id=?', $orderId)->limit($page, $page*$rowCount);
                $orderResult = $this->connection->fetchAll($orderSelect);
                $totalCount = count($orderResult);
                $_order = [];
                foreach ($orderResult as $order) {
                    /** Get Sub Order Data */
                    $_order[$order['entity_id']] = $this->getSubOrderData($order);
                }
            } elseif (array_key_exists('skus', $this->_request->getParams())) {
                $skus = $this->_request->getParam('skus');
                if (!empty($skus)) {
                    $selectQuery = $this->connection->select()
                        ->from('sales_order_item', ['order_id'])
                        ->where('sku IN ('. $skus . ')')
                        ->where('parent_item_id IS NULL')
                        ->distinct();
                    $this->filterByDate($selectQuery, $orderBy, $dir);
                    $result = $this->connection->fetchCol($selectQuery);
                    $totalCount = count($result);

                    if (!empty($result)) {
                        $where = 'so.entity_id IN ('.implode(',', $result).')';
                        $orderSelect = $this->connection->select()
                            ->from(['so' => 'sales_order'], ['so.entity_id', 'so.increment_id', 'so.total_item_count', 'so.created_at', 'so.sorting_history', 'so.customer_email', 'so.customer_group_id']);
                        $orderSelect->joinLeft(['soa' => 'sales_order_address'], 'soa.parent_id = so.entity_id and soa.address_type = "shipping"', ['address_id' => 'soa.entity_id']);
                        $orderSelect->joinLeft(['mcsfoa' => 'magento_customercustomattributes_sales_flat_order_address'], 'mcsfoa.entity_id = soa.entity_id', ['latitude', 'longitude']);
                        $orderSelect->where($where)
                            ->limit($page, $page*$rowCount);
                        $this->filterByDate($orderSelect, $orderBy, $dir);
                        $orderResult = $this->connection->fetchAll($orderSelect);
                        $_order = [];
                        foreach ($orderResult as $order) {
                            /** Get Sub Order Data */
                            $_order[$order['entity_id']] = $this->getSubOrderData($order);
                        }
                    }
                }
            }
            $_order['search']['total'] = $totalCount;
            $_order['search']['page_size'] = $page;
            $_order['search']['current_page'] = $rowCount;
            $_order['search']['order_by'] = $orderBy;
            $_order['search']['dir'] = $dir;
            return $_order;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * @param $order
     * @return array
     * @throws NoSuchEntityException
     */
    public function getSubOrderData($order): array
    {
        $mediaUrl = $this->_storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA).'catalog/product';
        $subOrderQuery = $this->connection->select()
            ->from(['mvo' => 'md_vendor_order'], ['vendor_order_id', 'increment_id', 'status', 'vendor_order_with_classification', 'is_sorted', 'is_sorted_timestamp', 'is_picked_up_timestamp'])
            ->joinLeft(['so' => 'sales_order_item'], 'so.vendor_order_id = mvo.vendor_order_id', ['product_id', 'name', 'sku', 'qty_ordered', 'product_options', 'product_type', 'price', 'original_price'])
            ->joinLeft(['mvwd' => 'md_vendor_website_data'], 'mvwd.vendor_id = mvo.vendor_id', ['vendor_name' => 'mvwd.name'])
            ->joinLeft(['cpe' => 'catalog_product_entity'], 'cpe.entity_id = so.product_id', '')
            ->joinLeft(['cpev' => 'catalog_product_entity_varchar'], 'cpe.row_id = cpev.row_id AND cpev.attribute_id = 87', '')
            ->joinLeft(['in_warehouse' => 'catalog_product_entity_varchar'], 'cpe.row_id = in_warehouse.row_id AND in_warehouse.attribute_id = 432', ['in_warehouse' => 'in_warehouse.value'])
            ->columns("CONCAT('".$mediaUrl."', cpev.value) as image_url")
            ->columns(['attr_json' => new \Zend_Db_Expr('JSON_EXTRACT(so.product_options, "$.attributes_info")')])
            ->joinLeft(['cpe1' => 'catalog_product_entity'], 'cpe1.sku = so.sku', '')
            ->joinLeft(['cpev1' => 'catalog_product_entity_varchar'], 'cpe1.row_id = cpev1.row_id AND cpev1.attribute_id = 87', '')
            ->columns("CONCAT('".$mediaUrl."', cpev1.value) as product_image")
            ->joinLeft(['mvol' => 'md_vendor_order_log'], '(mvol.vendor_order_id = mvo.vendor_order_id) AND mvol.status_change_to = \'confirmed\'', ['confirmation_date' => 'updated_at'])
            ->where('mvo.order_id=?', $order['entity_id'])
            ->where('so.parent_item_id IS NULL')  /*->where('mvol.status_change_to =?', 'confirmed')*/;
        $subOrderQuery->group('mvo.vendor_order_id');

        if (array_key_exists('sku', $this->_request->getparams())) {
            $itemStatus = ["pending", "confirmed", "processing"];
            $where = "mvo.status IN ('".implode("','", $itemStatus)."')";
            $subOrderQuery->where($where);
        }
        //echo $subOrderQuery; die();
        $subOrderResult =  $this->connection->fetchAll($subOrderQuery);

        $_orders['entity_id'] = $order['entity_id'];
        $_orders['increment_id'] = $order['increment_id'];
        $_orders['total_item_count'] = $order['total_item_count'];
        $_orders['created_at'] = $order['created_at'];
        $_orders['sorting_history'] = $order['sorting_history'];
        $_orders['customer_email'] = $order['customer_email'];
        $_orders['latitude'] = $order['latitude'];
        $_orders['longitude'] = $order['longitude'];
        $_orders['customer_group_id'] = $order['customer_group_id'];
        $_orders['items'] = $subOrderResult;
        return $_orders;
    }

    /**
     * @param $orderSelect
     * @param $orderBy
     * @param $dir
     * @return mixed
     */
    public function filterByDate($orderSelect, $orderBy, $dir) {
        if (!empty($orderBy) && $orderBy === 'created_at') {
            $orderSelect->order($orderBy . $dir);
        } else {
            $orderSelect->order('created_at desc');
        }
        return $orderSelect;
    }
}
