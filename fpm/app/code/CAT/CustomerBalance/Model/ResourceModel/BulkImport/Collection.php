<?php

namespace CAT\CustomerBalance\Model\ResourceModel\BulkImport;

/**
 * Class Collection
 * @package CAT\CustomerBalance\Model\ResourceModel\BulkImport
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected $_idFieldName = 'import_id';

    public function _construct()
    {
        $this->_init('CAT\CustomerBalance\Model\BulkImport', 'CAT\CustomerBalance\Model\ResourceModel\BulkImport');
    }
}