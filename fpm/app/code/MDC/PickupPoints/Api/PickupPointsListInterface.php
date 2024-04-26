<?php

declare (strict_types = 1);

namespace MDC\PickupPoints\Api;

interface PickupPointsListInterface
{
    /**
     * List of pickup points added by admin     
     * @return \MDC\PickupPoints\Api\Data\PickupPointsDataInterface
     * @throws \Magento\Framework\Exception\LocalizedException.
     */
    public function getPickupPointsList();
}
