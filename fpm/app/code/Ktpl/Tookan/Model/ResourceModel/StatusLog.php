<?php

namespace Ktpl\Tookan\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class StatusLog extends AbstractDb
{
    /**
     * Define main table
     */
    protected function _construct()
    {
        $this->_init('md_vendor_order_status_log', 'id');
    }
}