<?php declare(strict_types=1);


namespace Ktpl\Pushnotification\Api\Data;


interface KtplDevicetokensInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{

    const CREATED_AT = 'created_at';
    const ID = 'id';
    const STATUS = 'status';
    const DEVICE_TYPE = 'device_type';
    const CUSTOMER_ID = 'customer_id';
    const DEVICE_TOKEN = 'device_token';
    const UPDATED_AT = 'updated_at';
    //const KTPL_DEVICETOKENS_ID = 'ktpl_devicetokens_id';

    /**
     * Get ktpl_devicetokens_id
     * @return string|null
     */
    //public function getKtplDevicetokensId();

    /**
     * Set ktpl_devicetokens_id
     * @param string $ktplDevicetokensId
     * @return \Ktpl\Pushnotification\Api\Data\KtplDevicetokensInterface
     */
    //public function setKtplDevicetokensId($ktplDevicetokensId);

    /**
     * Get status
     * @return bool|null
     */
    public function getStatus();

    /**
     * Set status
     * @param bool $status
     * @return \Ktpl\Pushnotification\Api\Data\KtplDevicetokensInterface
     */
    public function setStatus($status);

    /**
     * Get id
     * @return string|null
     */
    public function getId();

    /**
     * Set id
     * @param string $id
     * @return \Ktpl\Pushnotification\Api\Data\KtplDevicetokensInterface
     */
    public function setId($id);

    /**
     * Retrieve existing extension attributes object or create a new one.
     * @return \Ktpl\Pushnotification\Api\Data\KtplDevicetokensExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     * @param \Ktpl\Pushnotification\Api\Data\KtplDevicetokensExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Ktpl\Pushnotification\Api\Data\KtplDevicetokensExtensionInterface $extensionAttributes
    );

    /**
     * Get device_type
     * @return string|null
     */
    public function getDeviceType();

    /**
     * Set device_type
     * @param string $deviceType
     * @return \Ktpl\Pushnotification\Api\Data\KtplDevicetokensInterface
     */
    public function setDeviceType($deviceType);

    /**
     * Get device_token
     * @return string|null
     */
    public function getDeviceToken();

    /**
     * Set device_token
     * @param string $deviceToken
     * @return \Ktpl\Pushnotification\Api\Data\KtplDevicetokensInterface
     */
    public function setDeviceToken($deviceToken);

    /**
     * Get customer_id
     * @return string|null
     */
    public function getCustomerId();

    /**
     * Set customer_id
     * @param string $customerId
     * @return \Ktpl\Pushnotification\Api\Data\KtplDevicetokensInterface
     */
    public function setCustomerId($customerId);

    /**
     * Get created_at
     * @return string|null
     */
    public function getCreatedAt();

    /**
     * Set created_at
     * @param string $createdAt
     * @return \Ktpl\Pushnotification\Api\Data\KtplDevicetokensInterface
     */
    public function setCreatedAt($createdAt);

    /**
     * Get updated_at
     * @return string|null
     */
    public function getUpdatedAt();

    /**
     * Set updated_at
     * @param string $updatedAt
     * @return \Ktpl\Pushnotification\Api\Data\KtplDevicetokensInterface
     */
    public function setUpdatedAt($updatedAt);
}

