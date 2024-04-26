<?php

namespace CAT\Custom\Model\ResourceModel;

/**
 * Class Automation
 * @package CAT\Custom\Model\ResourceModel
 */
class Automation extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Automation constructor.
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context
    ) {
        parent::__construct($context);
    }

    protected function _construct() {
        $this->_init('automation_import_history', 'import_id');
    }
}