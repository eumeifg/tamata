<?php

namespace MDC\PickupPoints\Model;

use Magento\Framework\Model\AbstractModel;
use MDC\PickupPoints\Model\ResourceModel\Pickups as PickupsResourceModel;

class Pickups extends \Magento\Framework\Model\AbstractModel
{
    protected function _construct()
    {
        $this->_init(PickupsResourceModel::class);
    }
}