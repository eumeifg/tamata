<?php


namespace CAT\SearchPage\Model\ResourceModel\SearchPage;
 
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected $_idFieldName = 'search_page_id';
    /**
     * Define model & resource model
     */
    protected function _construct()
    {
        $this->_init(
            'CAT\SearchPage\Model\SearchPage',
            'CAT\SearchPage\Model\ResourceModel\SearchPage'
        );
    }
}
