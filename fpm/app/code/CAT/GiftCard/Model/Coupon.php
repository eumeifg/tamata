<?php

namespace CAT\GiftCard\Model;

/**
 * Class Coupon
 * @package CAT\GiftCard\Model
 */
class Coupon extends \Magento\Framework\Model\AbstractModel
{
    public function _construct()
    {
        parent::_construct();
        $this->_init(\CAT\GiftCard\Model\ResourceModel\Coupon::class);
        $this->setIdFieldName('coupon_id');
    }
}