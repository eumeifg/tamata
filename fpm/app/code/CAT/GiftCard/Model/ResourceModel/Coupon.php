<?php

namespace CAT\GiftCard\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\Context;

/**
 * Class Coupon
 * @package CAT\GiftCard\Model\ResourceModel
 */
class Coupon extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Coupon constructor.
     * @param Context $context
     */
    public function __construct(
        Context $context
    ) {
        parent::__construct($context);
    }

    /**
     * @inheritDoc
     */
    protected function _construct()
    {
        $this->_init('giftcard_coupon', 'coupon_id');
        $this->addUniqueField(['field' => 'code', 'title' => __('Coupon with the same code')]);
    }

    public function exists($code)
    {
        $connection = $this->getConnection();
        $select = $connection->select();
        $select->from($this->getMainTable(), 'code');
        $select->where('code = :code');

        if ($connection->fetchOne($select, ['code' => $code]) === false) {
            return false;
        }
        return true;
    }
}