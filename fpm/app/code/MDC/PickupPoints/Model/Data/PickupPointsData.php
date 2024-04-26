<?php

declare (strict_types = 1);

namespace MDC\PickupPoints\Model\Data;

use MDC\PickupPoints\Api\Data\PickupPointsDataInterface;

class PickupPointsData extends \Magento\Framework\Api\AbstractExtensibleObject implements PickupPointsDataInterface
{
    /**
     * Get the list of pickupoints
     * @return array|null
     */
    public function getPickupPoints()
    {
        return $this->_get(self::PICKUPPOINTS);
    }

    /**
     * Set the list of pickupoints
     * @param \MDC\PickupPoints\Api\Data\PickupPointsObjInterface[] $data
     * @return \MDC\PickupPoints\Api\Data\PickupPointsObjInterface
     */
    public function setPickupPoints(array $data = null)
    {
        return $this->setData(self::PICKUPPOINTS, $data);
    }
}
