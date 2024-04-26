<?php

namespace MDC\Sales\Model;

use Magento\Framework\Model\AbstractModel;

class OrderLog extends AbstractModel
{
    /**
     * Define resource model
     */
    protected function _construct()
    {
        $this->_init('MDC\Sales\Model\ResourceModel\OrderLog');
    }
}