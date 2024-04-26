<?php

declare (strict_types = 1);

namespace Ktpl\Pushnotification\Api;

interface KtplPushnotificationListInterface
{
    /**
     * Get All Push Notification List By Customer Id
     * @param int $customerId The Customer ID.
     * @return \Ktpl\Pushnotification\Api\Data\KtplPushnotificationDataInterface
     * @throws \Magento\Framework\Exception\LocalizedException.
     */
    public function getNotificationList($customerId);
}
