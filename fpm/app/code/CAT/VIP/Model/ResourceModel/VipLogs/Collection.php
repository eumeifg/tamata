<?php

namespace CAT\VIP\Model\ResourceModel\VipLogs;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected $_idFieldName = 'log_id';

    protected function _construct()
    {
        $this->_init(
            'CAT\VIP\Model\VipLogs',
            'CAT\VIP\Model\ResourceModel\VipLogs'
        );
    }

}
