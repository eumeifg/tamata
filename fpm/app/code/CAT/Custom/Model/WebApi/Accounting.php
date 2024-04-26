<?php

namespace CAT\Custom\Model\WebApi;

use Magento\Framework\Webapi\Rest\Request;
use Magento\Framework\App\ResourceConnection;
use CAT\Custom\Api\AccountingInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Eav\Model\ResourceModel\Entity\Attribute;

class Accounting implements AccountingInterface
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
     * @var AdapterInterface
     */
    protected $connection;

    /**
     * @var Attribute
     */
    protected $_eavAttribute;

    /**
     * @param Request $request
     * @param ResourceConnection $resourceConnection
     * @param Attribute $eavAttribute
     */
    public function __construct(
        Request $request,
        ResourceConnection $resourceConnection,
        Attribute $eavAttribute
    ) {
        $this->_request = $request;
        $this->resourceConnection = $resourceConnection;
        $this->_eavAttribute = $eavAttribute;
        $this->connection = $this->resourceConnection->getConnection();
    }
    /**
     * @inheritDoc
     */
    public function getAccountingInfo()
    {
        $page = !empty($pageSize = $this->_request->getParam('page_size')) ? $pageSize : null;
        $rowCount = !empty($currentPage = $this->_request->getParam('current_page')) ? $currentPage : 0;
        $orderBy = !empty($this->_request->getParam('order_by')) ? $this->_request->getParam('order_by') : null;
        $dir = !empty($this->_request->getParam('dir')) ? ' '.$this->_request->getParam('dir') : null;
        $totalCount = 0;
        /* Filter with  Vendor ID */
        if(array_key_exists('vendor_id', $this->_request->getParams())) {
            $vendorId = $this->_request->getparam('vendor_id');
            $select = $this->connection->select()->from(['mvo' => 'md_vendor_order'], 'mvo.order_id');
            $select->group('mvo.order_id');
            $select->where('mvo.vendor_id=?',$vendorId);

            /* Filter with Item status */
            if (array_key_exists('item_status', $this->_request->getParams())) {
                $select->where('mvo.status=?',$this->_request->getParam('item_status'));
            }
            /* Filter with date range */
            if (array_key_exists('date_from', $this->_request->getParams())) {
                $toDate = !empty($this->_request->getparam('date_to')) ? $this->_request->getparam('date_to') : date('Y-m-d');
                $select->where('mvo.created_at BETWEEN "'.$this->_request->getParam('date_from').'" AND "'.$toDate.'"');
            }
            /* Filter with date */
            if (array_key_exists('date', $this->_request->getParams())) {
                $select->where('mvo.created_at LIKE '."'%".$this->_request->getParam('date')."%'");
            }
            /* Filter with invoice_paid parameter*/
            if (array_key_exists('invoice_paid', $this->_request->getParams())) {
                $where = 'mvo.invoice_paid='.$this->_request->getParam('invoice_paid');
                if (in_array($this->_request->getParam('invoice_paid'), ['null', ''])) {
                    $where = 'mvo.invoice_paid IS NULL';
                }
                $select->where($where);
            }

            $result = $this->connection->fetchCol($select);
            $totalCount = count($result);
            if (!empty($result)) {
                $where = 'so.entity_id IN ('.implode(',', $result).')';
                $orderSelect = $this->connection->select();
                $orderSelect->from(['so' => 'sales_order'], ['so.entity_id', 'so.increment_id', 'so.customer_firstname', 'so.customer_lastname', 'so.customer_email', 'so.created_at']);
                $orderSelect->joinLeft(['soa' => 'sales_order_address'], 'soa.parent_id = so.entity_id', ['region', 'street', 'city', 'country_id', 'telephone']);
                if ($orderBy == 'date') {
                    $orderSelect->order('so.created_at' . $dir);
                } else {
                    $orderSelect->order('so.created_at desc');
                }
                $orderSelect->where('soa.address_type=?', 'shipping');
                $orderSelect->where($where);
                $orderSelect->limit($page, $page*$rowCount);
                $orderResult = $this->connection->fetchAll($orderSelect);

                $_order = [];
                foreach ($orderResult as $order) {
                    /** Get Sub Order Data */
                    $_order[$order['entity_id']] = $this->getSubOrderData($order);
                }
            }
        } elseif (array_key_exists('suborder_increment_id', $this->_request->getParams())) {
            $incrementId = $this->_request->getparam('suborder_increment_id');
            $select = $this->connection->select()->from(['mvo' => 'md_vendor_order'], 'mvo.order_id');
            $select->group('mvo.order_id');
            $select->where('mvo.increment_id=?',$incrementId);
            $result = $this->connection->fetchCol($select);

            $totalCount = count($result);
            if (!empty($result)) {
                $where = 'so.entity_id IN ('.implode(',', $result).')';
                $orderSelect = $this->connection->select();
                $orderSelect->from(['so' => 'sales_order'], ['so.entity_id', 'so.increment_id', 'so.customer_firstname', 'so.customer_lastname', 'so.customer_email', 'so.created_at']);
                $orderSelect->joinLeft(['soa' => 'sales_order_address'], 'soa.parent_id = so.entity_id', ['region', 'street', 'city', 'country_id', 'telephone']);
                if ($orderBy == 'date') {
                    $orderSelect->order('so.created_at' . $dir);
                } else {
                    $orderSelect->order('so.created_at desc');
                }
                $orderSelect->where('soa.address_type=?', 'shipping');
                $orderSelect->where($where);
                $orderSelect->limit($page, $page*$rowCount);
                $orderResult = $this->connection->fetchAll($orderSelect);

                $_order = [];
                foreach ($orderResult as $order) {
                    /** Get Sub Order Data */
                    $_order[$order['entity_id']] = $this->getSubOrderData($order);
                }
            }
        }

        $_order['search']=[
            'total' => $totalCount,
            'page_size' => $page,
            'current_page' => $rowCount,
            'order_by' => $orderBy,
            'dir' => $dir
        ];
        return $_order;
    }

    /**
     * @param $order
     * @return array
     */
    public function getSubOrderData($order):array {
        /* Item Commission Attribute ID */
        $itemCommissionCode = $this->_eavAttribute->getIdByCode('catalog_product', 'item_commesion');
        /* Cost Price Attribute ID */
        $costPriceCode = $this->_eavAttribute->getIdByCode('catalog_product', 'cost_price');
        /* Price Validation Attribute */
        $priceValId = $this->_eavAttribute->getIdByCode('catalog_product', 'price_val');
        $subOrderQuery = $this->connection->select()
            ->from(['mvo' => 'md_vendor_order'], ['increment_id', 'status', 'is_sorted', 'vendor_invoice_number', 'vendor_invoice_amount', 'amount_paid', 'amount_enveloped', 'invoice_paid', 'paid_date', 'in_warehouse_date', 'accounting_notes', 'item_commission'])
            ->joinLeft(['so' => 'sales_order_item'], 'so.vendor_order_id = mvo.vendor_order_id', ['product_id', 'name', 'sku', 'qty_ordered', 'price'])
            ->joinLeft(['mvwd' => 'md_vendor_website_data'], 'mvwd.vendor_id = mvo.vendor_id', ['vendor_name' => 'mvwd.name'])
            ->joinLeft(['mvc' => 'md_vendor_commissions'], 'mvc.vendor_id = mvo.vendor_id', ['com_percentage' => 'vendor_commission_value'])
            ->joinLeft(['cpe' => 'catalog_product_entity'], 'cpe.entity_id = so.product_id', '')
            /*Added item_commission value in the response*/
            ->joinLeft(['cpev' => 'catalog_product_entity_varchar'], 'cpe.row_id = cpev.row_id and cpev.attribute_id = "'.$itemCommissionCode.'"', ['old_item_commission' => 'cpev.value'])
            /*Added item_commission value in the response*/
            ->joinLeft(['cp' => 'catalog_product_entity_varchar'], 'cpe.row_id = cp.row_id and cp.attribute_id = "'.$costPriceCode.'"', ['cost_price' => 'cp.value'])
            /*Added price_val value in the response*/
            ->joinLeft(['cpp' => 'catalog_product_entity_varchar'], 'cpe.row_id = cpp.row_id and cpp.attribute_id = "'.$priceValId.'"', ['price_validation' => 'cpp.value'])
            ->where('mvo.order_id=?', $order['entity_id'])
            ->where('so.parent_item_id IS NULL');
        if(array_key_exists('vendor_id', $this->_request->getParams()) && array_key_exists('cash_orders', $this->_request->getParams()) && $this->_request->getParam('cash_orders') == 1) {
            $subOrderQuery->where('mvo.status IN ("pending", "processing")');
        } elseif(array_key_exists('vendor_id', $this->_request->getParams())) {
            $subOrderQuery->where('mvo.status NOT IN ("canceled", "pending", "processing", "confirmed", "closed")');
        }
        $subOrderQuery->group('mvo.increment_id');
        $subOrderResult =  $this->connection->fetchAll($subOrderQuery);

        // check commission 
        foreach($subOrderResult as $key => $value){
            if(is_null($subOrderResult[$key]['item_commission'])){
                $subOrderResult[$key]['item_commission'] = $subOrderResult[$key]['old_item_commission'];
            }
            unset($subOrderResult[$key]['old_item_commission']);
        }
        // check commission 

        $_orders['entity_id'] = $order['entity_id'];
        $_orders['increment_id'] = $order['increment_id'];
        $_orders['customer_name'] = $order['customer_firstname'].' '.$order['customer_lastname'];
        $_orders['customer_email'] = $order['customer_email'];
        $_orders['created_at'] = $order['created_at'];
        $_orders['address'] = $order['street'] . ' '. $order['city'] . ' ' . $order['region'] . ' ' . $order['country_id'] . ' T. ' . $order['telephone'];
        $_orders['items'] = $subOrderResult;
        return $_orders;
    }
}
