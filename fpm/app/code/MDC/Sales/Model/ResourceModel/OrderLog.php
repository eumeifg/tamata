<?php

namespace MDC\Sales\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class OrderLog extends AbstractDb
{
    /**
     * Define main table
     */
    protected function _construct()
    {
        $this->_init('md_vendor_order_log', 'id');
    }
}