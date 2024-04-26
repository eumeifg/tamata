<?php

namespace CAT\VIP\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;

class VipLogs extends AbstractDb
{
    public function __construct(
        Context $context
    ) {
        parent::__construct($context);
    }

    public function _construct()
    {
        $this->_init('cat_vip_logs', 'log_id');
    }
}
