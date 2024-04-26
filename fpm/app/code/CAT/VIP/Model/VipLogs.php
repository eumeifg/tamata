<?php

namespace CAT\VIP\Model;

use Magento\Framework\Model\AbstractExtensibleModel;

class VipLogs extends AbstractExtensibleModel
{
    public function _construct() {
        $this->_init('CAT\VIP\Model\ResourceModel\VipLogs');
    }
}
