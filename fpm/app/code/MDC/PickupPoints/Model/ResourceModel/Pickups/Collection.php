<?php

namespace MDC\PickupPoints\Model\ResourceModel\Pickups;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use MDC\PickupPoints\Model\Pickups as PickupsModel;
use MDC\PickupPoints\Model\ResourceModel\Pickups as PickupsResourceModel;

class Collection extends AbstractCollection
{
	/**
     * ID Field Name
     *
     * @var string
     */
    protected $_idFieldName = 'pickup_point_id';

     /**
     * Define resource model
     *
     * @return void
     */     
    protected function _construct()
    {
        $this->_init(PickupsModel::class, PickupsResourceModel::class);
    }
}