<?php

namespace CAT\Custom\Model\ResourceModel\Order\Grid;

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
        $this->_map['fields']['customer_score'] = 'customer_feedback_by_admin.score';
    }

    protected function _renderFiltersBefore()
    {
        $connection = $this->getConnection();

        $joinTable = $this->getTable('customer_feedback_by_admin');
        $this->getSelect()->joinLeft($joinTable, 'main_table.customer_id = customer_feedback_by_admin.customer_id', ['customer_feedback_by_admin.score as customer_score'])->group('main_table.entity_id');

        parent::_renderFiltersBefore();
    }
}
