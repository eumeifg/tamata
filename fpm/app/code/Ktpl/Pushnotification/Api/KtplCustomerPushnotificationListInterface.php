<?php

declare (strict_types = 1);

namespace Ktpl\Pushnotification\Api;

interface KtplCustomerPushnotificationListInterface
{
    /**
     * Get All Push Notification List By Customer Id
     * @param int $customerId The Customer ID.
     * @return array|null
     * @throws \Magento\Framework\Exception\LocalizedException.
     */
    public function getNotificationList($customerId);
}
