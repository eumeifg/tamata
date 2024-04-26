<?php namespace Cminds\Coupon\Model\ResourceModel\Log;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Cminds\Coupon\Model\Log', 'Cminds\Coupon\Model\ResourceModel\Log');
    }

}