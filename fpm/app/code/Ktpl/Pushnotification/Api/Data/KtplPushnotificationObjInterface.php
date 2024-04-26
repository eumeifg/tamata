<?php

declare (strict_types = 1);

namespace Ktpl\Pushnotification\Api\Data;

interface KtplPushnotificationObjInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    const KEY_DATA = "notification_list";
    const NOTIFICATION_ID = "notification_id";
    const NOTIFICATION_TITLE = 'notification_title';
    const NOTIFICATION_DESCRIPTION = 'notification_description';
    const REDIRECTION_TYPEID = "redirection_typeid";
    const NOTIFICATION_TYPE = "notification_type";
    const NOTIFICATION_IMGURL = "notification_imgUrl";
    const NOTIFICATION_CREATED_AT = "created_at";

    /**
     * Get notification id
     * @return string|bool
     */
    public function getNotificationId();

    /**
     * Set notification id
     * @param string $id
     * @return \Ktpl\Pushnotification\Api\Data\KtplPushnotificationObjInterface
     */
    public function setNotificationId($id);

    /**
     * Get notification title
     * @return string|bool
     */
    public function getNotificationTitle();

    /**
     * Set notification title
     * @param string $title
     * @return \Ktpl\Pushnotification\Api\Data\KtplPushnotificationObjInterface
     */
    public function setNotificationTitle($title);

    /**
     * Get notification description
     * @return string|bool
     */
    public function getNotificationDescription();

    /**
     * Set notification description
     * @param string $description
     * @return \Ktpl\Pushnotification\Api\Data\KtplPushnotificationObjInterface
     */
    public function setNotificationDescription($description);

    /**
     * Get redirection type id
     * @return string|bool
     */
    public function getRedirectionTypeid();

    /**
     * Set redirection type id
     * @param string $redirectionTypeId
     * @return \Ktpl\Pushnotification\Api\Data\KtplPushnotificationObjInterface
     */
    public function setRedirectionTypeid($redirectionTypeId);

    /**
     * Get notification type
     * @return string|bool
     */
    public function getNotificationType();

    /**
     * Set notification type
     * @param string $notificationType
     * @return \Ktpl\Pushnotification\Api\Data\KtplPushnotificationObjInterface
     */
    public function setNotificationType($notificationType);

    /**
     * Get notification image url
     * @return string|bool
     */
    public function getNotificationImgUrl();

    /**
     * Set notification image url
     * @param string $notificationImgUrl
     * @return $this
     */
    public function setNotificationImgUrl($notificationImgUrl);

    /**
     * Get notification date
     * @return string|bool
     */
    public function getNotificationCreatedAt();

    /**
     * Set notification date
     * @param string $createdAt
     * @return \Ktpl\Pushnotification\Api\Data\KtplPushnotificationObjInterface
     */
    public function setNotificationCreatedAt($createdAt);
}
