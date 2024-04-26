<?php

namespace CAT\Custom\Model\ResourceModel\Automation;

/**
 * Class Collection
 * @package CAT\Custom\Model\ResourceModel\Automation
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected $_idFieldName = 'import_id';

    public function _construct()
    {
        $this->_init('CAT\Custom\Model\Automation', 'CAT\Custom\Model\ResourceModel\Automation');
    }
}