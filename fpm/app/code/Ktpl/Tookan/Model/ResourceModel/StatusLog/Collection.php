<?php

namespace Ktpl\Tookan\Model\ResourceModel\StatusLog;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    /**
     * Define model & resource model
     */
    protected function _construct()
    {
        $this->_init(
            'Ktpl\Tookan\Model\StatusLog',
            'Ktpl\Tookan\Model\ResourceModel\StatusLog'
        );
    }
}