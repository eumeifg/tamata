<?php

namespace CAT\Custom\Model;

/**
 * Class CustomerFeedback
 * @package CAT\Custom\Model
 */
class CustomerFeedback extends \Magento\Framework\Model\AbstractModel
{
    /**
     *
     */
    public function _construct()
    {
        $this->_init('CAT\Custom\Model\ResourceModel\CustomerFeedback');
    }
}