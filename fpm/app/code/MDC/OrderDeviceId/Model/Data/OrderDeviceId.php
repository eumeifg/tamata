<?php
/**
 * Copyright © MDC, All rights reserved.
 */
declare(strict_types=1);

namespace MDC\OrderDeviceId\Model\Data;

use MDC\OrderDeviceId\Api\Data\OrderDeviceIdInterface;
use Magento\Framework\Api\AbstractSimpleObject;

class OrderDeviceId extends AbstractSimpleObject implements OrderDeviceIdInterface
{
    /**
     * {@inheritDoc}
     */
    public function getOrderDeviceId()
    {
        return $this->_get(self::COLUMN_NAME);
    }

    /**
     * {@inheritDoc}
     */
    public function setOrderDeviceId($comment)
    {
        return $this->setData(self::COLUMN_NAME, $comment);
    }
}
