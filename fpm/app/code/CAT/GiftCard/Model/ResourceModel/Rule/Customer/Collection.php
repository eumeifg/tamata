<?php

namespace CAT\GiftCard\Model\ResourceModel\Rule\Customer;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Collection constructor
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init(
            \CAT\GiftCard\Model\Rule\Customer::class,
            \CAT\GiftCard\Model\ResourceModel\Rule\Customer::class
        );
    }
}