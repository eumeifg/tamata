<?php

namespace CAT\CustomerBalance\Model\ResourceModel;

/**
 * Class BulkImport
 * @package CAT\CustomerBalance\Model\ResourceModel
 */
class BulkImport extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * BulkImport constructor.
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context
    ) {
        parent::__construct($context);
    }

    protected function _construct() {
        $this->_init('cat_customerbalance', 'import_id');
    }
}