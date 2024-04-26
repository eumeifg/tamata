<?php declare(strict_types=1);


namespace Ktpl\Pushnotification\Model\Data;

use Ktpl\Pushnotification\Api\Data\KtplDevicetokensInterface;


class KtplDevicetokens extends \Magento\Framework\Api\AbstractExtensibleObject implements KtplDevicetokensInterface
{
    /**
     * Get id
     * @return bool|null
     */
    public function getStatus()
    {
        return $this->_get(self::STATUS);
    }

    /**
     * Set id
     * @param bool $status
     * @return \Ktpl\Pushnotification\Api\Data\KtplDevicetokensInterface
     */
    public function setStatus($status)
    {
        return $this->setData(self::STATUS, $status);
    }

    /**
     * Get id
     * @return string|null
     */
    public function getId()
    {
        return $this->_get(self::ID);
    }

    /**
     * Set id
     * @param string $id
     * @return \Ktpl\Pushnotification\Api\Data\KtplDevicetokensInterface
     */
    public function setId($id)
    {
        return $this->setData(self::ID, $id);
    }

    /**
     * Retrieve existing extension attributes object or create a new one.
     * @return \Ktpl\Pushnotification\Api\Data\KtplDevicetokensExtensionInterface|null
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * Set an extension attributes object.
     * @param \Ktpl\Pushnotification\Api\Data\KtplDevicetokensExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Ktpl\Pushnotification\Api\Data\KtplDevicetokensExtensionInterface $extensionAttributes
    ) {
        return $this->_setExtensionAttributes($extensionAttributes);
    }

    /**
     * Get device_type
     * @return string|null
     */
    public function getDeviceType()
    {
        return $this->_get(self::DEVICE_TYPE);
    }

    /**
     * Set device_type
     * @param string $deviceType
     * @return \Ktpl\Pushnotification\Api\Data\KtplDevicetokensInterface
     */
    public function setDeviceType($deviceType)
    {
        return $this->setData(self::DEVICE_TYPE, $deviceType);
    }

    /**
     * Get device_token
     * @return string|null
     */
    public function getDeviceToken()
    {
        return $this->_get(self::DEVICE_TOKEN);
    }

    /**
     * Set device_token
     * @param string $deviceToken
     * @return \Ktpl\Pushnotification\Api\Data\KtplDevicetokensInterface
     */
    public function setDeviceToken($deviceToken)
    {
        return $this->setData(self::DEVICE_TOKEN, $deviceToken);
    }

    /**
     * Get customer_id
     * @return string|null
     */
    public function getCustomerId()
    {
        return $this->_get(self::CUSTOMER_ID);
    }

    /**
     * Set customer_id
     * @param string $customerId
     * @return \Ktpl\Pushnotification\Api\Data\KtplDevicetokensInterface
     */
    public function setCustomerId($customerId)
    {
        return $this->setData(self::CUSTOMER_ID, $customerId);
    }

    /**
     * Get created_at
     * @return string|null
     */
    public function getCreatedAt()
    {
        return $this->_get(self::CREATED_AT);
    }

    /**
     * Set created_at
     * @param string $createdAt
     * @return \Ktpl\Pushnotification\Api\Data\KtplDevicetokensInterface
     */
    public function setCreatedAt($createdAt)
    {
        return $this->setData(self::CREATED_AT, $createdAt);
    }

    /**
     * Get updated_at
     * @return string|null
     */
    public function getUpdatedAt()
    {
        return $this->_get(self::UPDATED_AT);
    }

    /**
     * Set updated_at
     * @param string $updatedAt
     * @return \Ktpl\Pushnotification\Api\Data\KtplDevicetokensInterface
     */
    public function setUpdatedAt($updatedAt)
    {
        return $this->setData(self::UPDATED_AT, $updatedAt);
    }
}

