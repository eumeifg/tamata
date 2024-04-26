<?php

namespace CAT\SearchPage\Model;

use Magento\Framework\Model\AbstractModel;

class SearchPage extends AbstractModel
{
    /**
     * Define resource model
     */
    protected function _construct()
    {
        $this->_init('CAT\SearchPage\Model\ResourceModel\SearchPage');
    }
}
