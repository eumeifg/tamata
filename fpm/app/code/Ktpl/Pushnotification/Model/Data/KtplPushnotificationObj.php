<?php

declare (strict_types = 1);

namespace Ktpl\Pushnotification\Model\Data;

use Ktpl\Pushnotification\Api\Data\KtplPushnotificationObjInterface;

class KtplPushnotificationObj extends \Magento\Framework\Api\AbstractExtensibleObject implements KtplPushnotificationObjInterface
{
    /**
     * Get notification id
     * @return string|bool
     */
    public function getNotificationId()
    {
        $notifyId = $this->_get(self::NOTIFICATION_ID);
        return $notifyId ? $notifyId : false;
    }

    /**
     * Set notification id
     * @param string $id
     * @return \Ktpl\Pushnotification\Api\Data\KtplPushnotificationObjInterface
     */
    public function setNotificationId($id)
    {
        return $this->setData(self::NOTIFICATION_ID, $id);
    }

    /**
     * Get notification title
     * @return string|bool
     */
    public function getNotificationTitle()
    {
        $notifyTitle = $this->_get(self::NOTIFICATION_TITLE);
        return $notifyTitle ? $notifyTitle : false;
    }

    /**
     * Set notification title
     * @param string $title
     * @return \Ktpl\Pushnotification\Api\Data\KtplPushnotificationObjInterface
     */
    public function setNotificationTitle($title)
    {
        return $this->setData(self::NOTIFICATION_TITLE, $title);
    }

    /**
     * Get notification description
     * @return string|bool
     */
    public function getNotificationDescription()
    {
        $notifyDescription = $this->_get(self::NOTIFICATION_DESCRIPTION);
        return $notifyDescription ? $notifyDescription : false;
    }

    /**
     * Set notification description
     * @param string $description
     * @return \Ktpl\Pushnotification\Api\Data\KtplPushnotificationObjInterface
     */
    public function setNotificationDescription($description)
    {
        return $this->setData(self::NOTIFICATION_DESCRIPTION, $description);
    }

    /**
     * Get redirection type id
     * @return string|bool
     */
    public function getRedirectionTypeId()
    {
        $redirectionTypeId = $this->_get(self::REDIRECTION_TYPEID);
        return $redirectionTypeId ? $redirectionTypeId : false;
    }

    /**
     * Set redirection type id
     * @param string $redirectionTypeId
     * @return \Ktpl\Pushnotification\Api\Data\KtplPushnotificationObjInterface
     */
    public function setRedirectionTypeId($redirectionTypeId)
    {
        return $this->setData(self::REDIRECTION_TYPEID, $redirectionTypeId);
    }

    /**
     * Get notification type
     * @return string|bool
     */
    public function getNotificationType()
    {
        $nofiyType = $this->_get(self::NOTIFICATION_TYPE);
        return $nofiyType ? $nofiyType : false;
    }

    /**
     * Set notification type
     * @param string $notificationType
     * @return \Ktpl\Pushnotification\Api\Data\KtplPushnotificationObjInterface
     */
    public function setNotificationType($notificationType)
    {
        return $this->setData(self::NOTIFICATION_TYPE, $notificationType);
    }

    /**
     * Get notification image url
     * @return string|bool
     */
    public function getNotificationImgUrl()
    {
        $imageUrl = $this->_get(self::NOTIFICATION_IMGURL);
        return $imageUrl ? $imageUrl : false;
    }

    /**
     * Set notification image url
     * @param string|null $notificationImgUrl
     * @return $this
     */
    public function setNotificationImgUrl($notificationImgUrl = "")
    {
        return $this->setData(self::NOTIFICATION_IMGURL, $notificationImgUrl);
    }

    /**
     * Get notification date
     * @return string|bool
     */
    public function getNotificationCreatedAt()
    {
        $notifyCreatedAt = $this->_get(self::NOTIFICATION_CREATED_AT);
        return $notifyCreatedAt ? $notifyCreatedAt : false;
    }

    /**
     * Set notification date
     * @param string $createdAt
     * @return \Ktpl\Pushnotification\Api\Data\KtplPushnotificationObjInterface
     */
    public function setNotificationCreatedAt($createdAt)
    {
        return $this->setData(self::NOTIFICATION_CREATED_AT, $createdAt);
    }
}
