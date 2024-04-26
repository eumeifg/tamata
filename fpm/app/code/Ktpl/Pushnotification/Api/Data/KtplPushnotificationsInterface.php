<?php declare(strict_types=1);


namespace Ktpl\Pushnotification\Api\Data;


interface KtplPushnotificationsInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{

    const CREATED_AT = 'created_at';
    const ID = 'id';
    const DESCRIPTION = 'description';
    const STATUS = 'status';
    const SEND_TO_CUSTOMER_GROUP = 'send_to_customer_group';
    const SEND_TO_CUSTOMER = 'send_to_customer';
    const UPDATED_AT = 'updated_at';
    const IMAGE_URL = 'image_url';
    //const KTPL_PUSHNOTIFICATIONS_ID = 'id';
    const TITLE = 'title';

    /**
     * Get id
     * @return string|null
     */
    //public function getKtplPushnotificationsId();

    /**
     * Set id
     * @param string $ktplPushnotificationsId
     * @return \Ktpl\Pushnotification\Api\Data\KtplPushnotificationsInterface
     */
    //public function setKtplPushnotificationsId($ktplPushnotificationsId);

    /**
     * Get id
     * @return string|null
     */
    public function getId();

    /**
     * Set id
     * @param string $id
     * @return \Ktpl\Pushnotification\Api\Data\KtplPushnotificationsInterface
     */
    public function setId($id);

    /**
     * Retrieve existing extension attributes object or create a new one.
     * @return \Ktpl\Pushnotification\Api\Data\KtplPushnotificationsExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     * @param \Ktpl\Pushnotification\Api\Data\KtplPushnotificationsExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Ktpl\Pushnotification\Api\Data\KtplPushnotificationsExtensionInterface $extensionAttributes
    );

    /**
     * Get title
     * @return string|null
     */
    public function getTitle();

    /**
     * Set title
     * @param string $title
     * @return \Ktpl\Pushnotification\Api\Data\KtplPushnotificationsInterface
     */
    public function setTitle($title);

    /**
     * Get description
     * @return string|null
     */
    public function getDescription();

    /**
     * Set description
     * @param string $description
     * @return \Ktpl\Pushnotification\Api\Data\KtplPushnotificationsInterface
     */
    public function setDescription($description);

    /**
     * Get image_url
     * @return string|null
     */
    public function getImageUrl();

    /**
     * Set image_url
     * @param string $imageUrl
     * @return \Ktpl\Pushnotification\Api\Data\KtplPushnotificationsInterface
     */
    public function setImageUrl($imageUrl);

    /**
     * Get send_to_customer_group
     * @return string|null
     */
    public function getSendToCustomerGroup();

    /**
     * Set send_to_customer_group
     * @param string $sendToCustomerGroup
     * @return \Ktpl\Pushnotification\Api\Data\KtplPushnotificationsInterface
     */
    public function setSendToCustomerGroup($sendToCustomerGroup);

    /**
     * Get send_to_customer
     * @return string|null
     */
    public function getSendToCustomer();

    /**
     * Set send_to_customer
     * @param string $sendToCustomer
     * @return \Ktpl\Pushnotification\Api\Data\KtplPushnotificationsInterface
     */
    public function setSendToCustomer($sendToCustomer);

    /**
     * Get status
     * @return string|null
     */
    public function getStatus();

    /**
     * Set status
     * @param string $status
     * @return \Ktpl\Pushnotification\Api\Data\KtplPushnotificationsInterface
     */
    public function setStatus($status);

    /**
     * Get created_at
     * @return string|null
     */
    public function getCreatedAt();

    /**
     * Set created_at
     * @param string $createdAt
     * @return \Ktpl\Pushnotification\Api\Data\KtplPushnotificationsInterface
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
     * @return \Ktpl\Pushnotification\Api\Data\KtplPushnotificationsInterface
     */
    public function setUpdatedAt($updatedAt);
}

