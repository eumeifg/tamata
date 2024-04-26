<?php

/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/**
 * Report collection abstract model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */

namespace Magedelight\SocialLogin\Model\ResourceModel\Report\Collection;

class AbstractCollection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{

    /**
     * From date
     *
     * @var string
     */
    protected $_from = null;

    /**
     * To date
     *
     * @var string
     */
    protected $_to = null;

    /**
     * Period
     *
     * @var string
     */
    protected $_period = null;

    /**
     * Category
     *
     * @var string
     */
    protected $_category = null;


    /**
     * Store ids
     *
     * @var int|array
     */
    protected $_storesIds = 0;

    /**
     * Is totals
     *
     * @var bool
     */
    protected $_isTotals = false;

    /**
     * Is subtotals
     *
     * @var bool
     */
    protected $_isSubTotals = false;

    /**
     * Aggregated columns
     *
     * @var array
     */
    protected $_aggregatedColumns = [];

    /**
     * Order status
     *
     * @var string
     */
    protected $_orderStatus = null;

    protected $_socialsite = null;
    
    /**
     * @param \Magento\Framework\Data\Collection\EntityFactory $entityFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Sales\Reports\Model\ResourceModel\Report $resource
     * @param null $connection
     */
    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactory $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magedelight\SocialLogin\Model\ResourceModel\Report $resource,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null
    ) {
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
        $this->setModel('Magento\Reports\Model\Item');
    }

    /**
     * Set array of columns that should be aggregated
     * @codeCoverageIgnore
     *
     * @param array $columns
     * @return $this
     */
    public function setAggregatedColumns(array $columns)
    {

        $this->_aggregatedColumns = $columns;
        return $this;
    }

    /**
     * Retrieve array of columns that should be aggregated
     * @codeCoverageIgnore
     *
     * @return array
     */
    public function getAggregatedColumns()
    {
        return $this->_aggregatedColumns;
    }

    /**
     * Set date range
     * @codeCoverageIgnore
     *
     * @param mixed $from
     * @param mixed $to
     * @return $this
     */
    public function setDateRange($from = null, $to = null)
    {
        $this->_from = $from;
        $this->_to = $to;
        return $this;
    }

    /**
     * Set category name
     * @codeCoverageIgnore
     *
     * @param string $category
     * @return $this
     */
    public function setSocialLogin($socialsite)
    {
        $this->_socialsite = $socialsite;
        if ($this->_socialsite != '*') {
            $this->getSelect()->where('magedelight_sociallogin.type = ?', $this->_socialsite);
        }
        return $this;
    }

    
    /**
     * Apply date range filter
     *
     * @return $this
     */
    protected function _applyDateRangeFilter()
    {
        if ($this->_from !== null) {
            $this->getSelect()->where('customer_entity.created_at >= ?', $this->_from);
        }
        if ($this->_to !== null) {
            $this->getSelect()->where('customer_entity.created_at <= ?', $this->_to);
        }

        return $this;
    }

    /**
     * Set store ids
     *
     * @param mixed $storeIds (null, int|string, array, array may contain null)
     * @return $this
     */
    public function addStoreFilter($storeIds)
    {
        $this->_storesIds = $storeIds;
        return $this;
    }

    /**
     * Apply stores filter to select object
     *
     * @param \Magento\Framework\DB\Select $select
     * @return $this
     */
    protected function _applyStoresFilterToSelect(\Magento\Framework\DB\Select $select)
    {

        $nullCheck = false;
        $storeIds = $this->_storesIds;

        if (!is_array($storeIds)) {
            $storeIds = [$storeIds];
        }

        $storeIds = array_unique($storeIds);

        $index = array_search(null, $storeIds);
        if ($index) {
            unset($storeIds[$index]);
            $nullCheck = true;
        }

        if ($nullCheck) {
            $select->where('customer_entity.store_id IN(?) OR customer_entity.store_id IS NULL', $storeIds);
        } else {
            $select->where('customer_entity.store_id IN(?)', $storeIds);
        }

        return $this;
    }

    /**
     * Apply stores filter
     *
     * @return $this
     */
    protected function _applyStoresFilter()
    {
        return $this->_applyStoresFilterToSelect($this->getSelect());
    }

       
    
    /** Custom Query using join clause
     * Get data of according to category : category name, product name, order date, invoice,
     * subtotal, total, invoiced and refunded
     * @return $this
     */
    protected function _initSelect()
    {

        $this->getSelect()->from(
            ['customer_entity' => $this->getTable('customer_entity')]
        );
               
        $this->getSelect()->joinleft(
            ['magedelight_sociallogin' => $this->getTable('magedelight_sociallogin')],
            'customer_entity.entity_id = magedelight_sociallogin.customer_id',
            ['customer_entity.entity_id','customer_entity.firstname','customer_entity.lastname','customer_entity.created_at','CONCAT(UCASE(LEFT(type, 1)),SUBSTRING(type, 2)) as sociallogin_type']
        )->group('customer_entity.entity_id');
        $this->getSelect()->joinleft(
            ['store_website' => $this->getTable('store_website')],
            'customer_entity.website_id = store_website.website_id',
            ['name as website_name']
        );
        
        $this->getSelect()->joinleft(
            ['store' => $this->getTable('store')],
            'store.store_id = customer_entity.store_id',
            ['store.name as store_name']
        );

      
        return $this;
    }

    /**
     * Apply filters common to reports
     *
     * @return $this
     */
    protected function _beforeLoad()
    {
        parent::_beforeLoad();
        $this->_applyDateRangeFilter();
        $this->_applyStoresFilter();
        return $this;
    }
}
