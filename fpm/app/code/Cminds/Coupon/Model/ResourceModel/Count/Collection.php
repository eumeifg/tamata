<?php namespace Cminds\Coupon\Model\ResourceModel\Count;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Cminds\Coupon\Model\Count', 'Cminds\Coupon\Model\ResourceModel\Count');
    }
}