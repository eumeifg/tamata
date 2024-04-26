<?php

namespace Ktpl\SortbyPopularity\Plugin\Block;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Select;

/**
* Product list toolbar plugin
*/

class Toolbar {

    const SORT_ORDER_DESC = 'DESC';

    /**
    * @var \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
    */
    protected $_collection = null;

    /**
    * DB connection
    *
    * @var \Magento\Framework\DB\Adapter\AdapterInterface
    */
    protected $_conn;

    /**
    * @var boolean
    */
    protected $_subQueryApplied = false;

    /**
    * Constructor
    *
    * @param \Magento\Framework\App\ResourceConnection $resource
    */
    public function __construct(ResourceConnection $resource) {
        $this->_conn = $resource->getConnection('catalog');
    }

    /**
    * Plugin – Used to modify default Sort By filters
    *
    * @param \Magento\Catalog\Block\Product\ProductList\Toolbar $subject
    * @param null $result
    * @param \Magento\Framework\Data\Collection $collection
    * @return Toolbar
    * @SuppressWarnings(PHPMD.UnusedFormalParameter)
    */
    public function afterSetCollection(\Magento\Catalog\Block\Product\ProductList\Toolbar $subject, $result, $collection) {
        $this->_collection = $collection;
        if ($subject->getCurrentOrder() == 'most_viewed') {
            if (!$this->_subQueryApplied) {
                $reportEventTable = $this->_collection->getResource()->getTable('report_event');

                $subSelect = $this->_conn->select()->from(['report_event_table' => $reportEventTable], 'COUNT(report_event_table.event_id)')->where('report_event_table.object_id = e.entity_id');

                $this->_collection->getSelect()->reset(Select::ORDER)->columns(['views' => $subSelect])->order('views '  . self::SORT_ORDER_DESC);
                $this->_subQueryApplied = true;
            }
        }
        return $this;
    }

}