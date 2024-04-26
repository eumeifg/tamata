<?php

namespace CAT\Custom\Model;

/**
 * Class Automation
 * @package CAT\Custom\Model
 */
class Automation extends \Magento\Framework\Model\AbstractModel
{
    /**
     *
     */
    public function _construct()
    {
        $this->_init('CAT\Custom\Model\ResourceModel\Automation');
    }
}