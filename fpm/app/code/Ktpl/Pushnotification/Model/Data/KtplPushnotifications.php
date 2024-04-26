<?php declare(strict_types=1);


namespace Ktpl\Pushnotification\Model\Data;

use Ktpl\Pushnotification\Api\Data\KtplPushnotificationsInterface;


class KtplPushnotifications extends \Magento\Framework\Api\AbstractExtensibleObject implements KtplPushnotificationsInterface
{
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
     * @return \Ktpl\Pushnotification\Api\Data\KtplPushnotificationsInterface
     */
    public function setId($id)
    {
        return $this->setData(self::ID, $id);
    }

    /**
     * Retrieve existing extension attributes object or create a new one.
     * @return \Ktpl\Pushnotification\Api\Data\KtplPushnotificationsExtensionInterface|null
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * Set an extension attributes object.
     * @param \Ktpl\Pushnotification\Api\Data\KtplPushnotificationsExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Ktpl\Pushnotification\Api\Data\KtplPushnotificationsExtensionInterface $extensionAttributes
    ) {
        return $this->_setExtensionAttributes($extensionAttributes);
    }

    /**
     * Get title
     * @return string|null
     */
    public function getTitle()
    {
        return $this->_get(self::TITLE);
    }

    /**
     * Set title
     * @param string $title
     * @return \Ktpl\Pushnotification\Api\Data\KtplPushnotificationsInterface
     */
    public function setTitle($title)
    {
        return $this->setData(self::TITLE, $title);
    }

    /**
     * Get description
     * @return string|null
     */
    public function getDescription()
    {
        return $this->_get(self::DESCRIPTION);
    }

    /**
     * Set description
     * @param string $description
     * @return \Ktpl\Pushnotification\Api\Data\KtplPushnotificationsInterface
     */
    public function setDescription($description)
    {
        return $this->setData(self::DESCRIPTION, $description);
    }

    /**
     * Get image_url
     * @return string|null
     */
    public function getImageUrl()
    {
        return $this->_get(self::IMAGE_URL);
    }

    /**
     * Set image_url
     * @param string $imageUrl
     * @return \Ktpl\Pushnotification\Api\Data\KtplPushnotificationsInterface
     */
    public function setImageUrl($imageUrl)
    {
        return $this->setData(self::IMAGE_URL, $imageUrl);
    }

    /**
     * Get send_to_customer_group
     * @return string|null
     */
    public function getSendToCustomerGroup()
    {
        return $this->_get(self::SEND_TO_CUSTOMER_GROUP);
    }

    /**
     * Set send_to_customer_group
     * @param string $sendToCustomerGroup
     * @return \Ktpl\Pushnotification\Api\Data\KtplPushnotificationsInterface
     */
    public function setSendToCustomerGroup($sendToCustomerGroup)
    {
        return $this->setData(self::SEND_TO_CUSTOMER_GROUP, $sendToCustomerGroup);
    }

    /**
     * Get send_to_customer
     * @return string|null
     */
    public function getSendToCustomer()
    {
        return $this->_get(self::SEND_TO_CUSTOMER);
    }

    /**
     * Set send_to_customer
     * @param string $sendToCustomer
     * @return \Ktpl\Pushnotification\Api\Data\KtplPushnotificationsInterface
     */
    public function setSendToCustomer($sendToCustomer)
    {
        return $this->setData(self::SEND_TO_CUSTOMER, $sendToCustomer);
    }

    /**
     * Get status
     * @return string|null
     */
    public function getStatus()
    {
        return $this->_get(self::STATUS);
    }

    /**
     * Set status
     * @param string $status
     * @return \Ktpl\Pushnotification\Api\Data\KtplPushnotificationsInterface
     */
    public function setStatus($status)
    {
        return $this->setData(self::STATUS, $status);
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
     * @return \Ktpl\Pushnotification\Api\Data\KtplPushnotificationsInterface
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
     * @return \Ktpl\Pushnotification\Api\Data\KtplPushnotificationsInterface
     */
    public function setUpdatedAt($updatedAt)
    {
        return $this->setData(self::UPDATED_AT, $updatedAt);
    }
}

