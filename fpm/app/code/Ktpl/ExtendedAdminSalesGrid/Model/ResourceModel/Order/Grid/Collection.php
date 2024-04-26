<?php

namespace Ktpl\ExtendedAdminSalesGrid\Model\ResourceModel\Order\Grid;

use Magento\Framework\Data\Collection\Db\FetchStrategyInterface as FetchStrategy;
use Magento\Framework\Data\Collection\EntityFactoryInterface as EntityFactory;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Psr\Log\LoggerInterface as Logger;

class Collection extends \Magento\Sales\Model\ResourceModel\Order\Grid\Collection
{
    public function __construct(
        EntityFactory $entityFactory,
        Logger $logger,
        FetchStrategy $fetchStrategy,
        EventManager $eventManager,
        $mainTable = 'sales_order_grid',
        $resourceModel = \Magento\Sales\Model\ResourceModel\Order::class
    ) {
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $mainTable, $resourceModel);
        $this->_map['fields']['md_vendor_order_increment_id'] = 'md_vendor_order.increment_id';
        $this->_map['fields']['md_vendor_order_status'] = 'md_vendor_order.status';
        $this->_map['fields']['md_vendor_name'] = 'md_vendor_order.vendor_id';
        $this->_map['fields']['order_barcode'] = 'md_vendor_order.barcode_number';
    }

    protected function _renderFiltersBefore()
    {
        $connection = $this->getConnection();

        $joinTable = $this->getTable('md_vendor_order');
        $this->getSelect()->joinLeft($joinTable, 'main_table.entity_id = md_vendor_order.order_id', ['md_vendor_order.increment_id as md_vendor_order_increment_id', 'md_vendor_order.status as md_vendor_order_status', 'md_vendor_order.vendor_id as md_vendor_name', 'md_vendor_order.barcode_number as barcode_number'])->group('main_table.entity_id');

        /** Changes By : RH */
        $joinTableSales = $this->getTable('sales_order');
        $this->getSelect()->joinLeft($joinTableSales, 'main_table.entity_id = sales_order.entity_id', ['coupon_rule_name']);
        /** Changes By : RH */

        //echo $this->getSelect(); die;

        parent::_renderFiltersBefore();
    }
}
