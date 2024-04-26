<?php

declare (strict_types = 1);

namespace Ktpl\Pushnotification\Api\Data;

interface KtplPushnotificationDataInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    const NOTIFICATIONS = "notifications";
    /**
     * Get the list of notifications
     *
     * @return \Ktpl\Pushnotification\Api\Data\KtplPushnotificationObjInterface[]
     */
    public function getNotifications();

    /**
     * Set the list of notifications.
     *
     * @param \Ktpl\Pushnotification\Api\Data\KtplPushnotificationObjInterface[] $data
     * @return $this
     */
    public function setNotifications(array $data = null);
}
