<?php

namespace MDC\Sales\Model\ResourceModel\OrderLog;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    /**
     * Define model & resource model
     */
    protected function _construct()
    {
        $this->_init(
            'MDC\Sales\Model\OrderLog',
            'MDC\Sales\Model\ResourceModel\OrderLog'
        );
    }
}