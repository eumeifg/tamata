<?php namespace Cminds\Coupon\Model\ResourceModel\Error;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Cminds\Coupon\Model\Error', 'Cminds\Coupon\Model\ResourceModel\Error');
    }

}