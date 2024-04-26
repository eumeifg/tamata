<?php
/**
 * Copyright © MDC, All rights reserved.
 */
declare(strict_types=1);

namespace MDC\OrderDeviceId\Api\Data;

/**
 * Interface OrderDeviceIdInterface
 * @api
 */
interface OrderDeviceIdInterface
{
    const COLUMN_NAME = 'device_id';

    /**
     * @return string|null
     */
    public function getOrderDeviceId();

    /**
     * @param string $orderDeviceId
     * @return OrderDeviceIdInterface
     */
    public function setOrderDeviceId($orderDeviceId);
}
