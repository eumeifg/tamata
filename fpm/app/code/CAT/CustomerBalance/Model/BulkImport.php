<?php

namespace CAT\CustomerBalance\Model;

/**
 * Class BulkImport
 * @package CAT\CustomerBalance\Model
 */
class BulkImport extends \Magento\Framework\Model\AbstractModel
{
    /**
     *
     */
    public function _construct()
    {
        $this->_init('CAT\CustomerBalance\Model\ResourceModel\BulkImport');
    }
}