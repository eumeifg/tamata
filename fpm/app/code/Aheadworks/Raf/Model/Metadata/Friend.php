<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Raf\Model\Metadata;

use Aheadworks\Raf\Api\Data\FriendMetadataInterface;
use Magento\Framework\DataObject;

/**
 * Class Friend
 *
 * @package Aheadworks\Raf\Model\Metadata
 */
class Friend extends DataObject implements FriendMetadataInterface
{
    /**
     * {@inheritdoc}
     */
    public function getCustomerId()
    {
        return $this->getData(self::CUSTOMER_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setCustomerId($customerId)
    {
        return $this->setData(self::CUSTOMER_ID, $customerId);
    }

    /**
     * {@inheritdoc}
     */
    public function getCustomerEmail()
    {
        return $this->getData(self::CUSTOMER_EMAIL);
    }

    /**
     * {@inheritdoc}
     */
    public function setCustomerEmail($customerEmail)
    {
        return $this->setData(self::CUSTOMER_EMAIL, $customerEmail);
    }

    /**
     * {@inheritdoc}
     */
    public function getCustomerIp()
    {
        return $this->getData(self::CUSTOMER_IP);
    }

    /**
     * {@inheritdoc}
     */
    public function setCustomerIp($ip)
    {
        return $this->setData(self::CUSTOMER_IP, $ip);
    }
}
