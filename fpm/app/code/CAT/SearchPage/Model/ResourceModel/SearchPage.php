<?php

namespace CAT\SearchPage\Model\ResourceModel;

class SearchPage extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Define main table
     */
    protected function _construct()
    {
        $this->_init('search_page', 'search_page_id');
    }
}
