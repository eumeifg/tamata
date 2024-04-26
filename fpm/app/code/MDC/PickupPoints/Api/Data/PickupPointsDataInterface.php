<?php

declare (strict_types = 1);

namespace MDC\PickupPoints\Api\Data;

interface PickupPointsDataInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    const PICKUPPOINTS = "pickuppoints";
    /**
     * Get the list of pickup points
     *
     * @return \MDC\PickupPoints\Api\Data\PickupPointsObjInterface[]
     */
    public function getPickupPoints();

    /**
     * Set the list of pickup points
     *
     * @param \MDC\PickupPoints\Api\Data\PickupPointsObjInterface[] $data
     * @return $this
     */
    public function setPickupPoints(array $data = null);
}
