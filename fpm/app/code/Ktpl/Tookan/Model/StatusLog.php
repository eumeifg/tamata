<?php

namespace Ktpl\Tookan\Model;

use Magento\Framework\Model\AbstractModel;

class StatusLog extends AbstractModel
{
    /**
     * Define resource model
     */
    protected function _construct()
    {
        $this->_init('Ktpl\Tookan\Model\ResourceModel\StatusLog');
    }
}