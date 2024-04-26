<?php

namespace CAT\Custom\Model\ResourceModel\CustomerFeedback;

/**
 * Class Collection
 * @package CAT\Custom\Model\ResourceModel\CustomerFeedback
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected $_idFieldName = 'feedback_id';

    public function _construct()
    {
        $this->_init('CAT\Custom\Model\CustomerFeedback', 'CAT\Custom\Model\ResourceModel\CustomerFeedback');
    }
}