<?php

namespace CAT\Custom\Model\ResourceModel;

/**
 * Class CustomerFeedback
 * @package CAT\Custom\Model\ResourceModel
 */
class CustomerFeedback extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * CustomerFeedback constructor.
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context
    ) {
        parent::__construct($context);
    }

    protected function _construct() {
        $this->_init('customer_feedback_by_admin', 'feedback_id');
    }
}