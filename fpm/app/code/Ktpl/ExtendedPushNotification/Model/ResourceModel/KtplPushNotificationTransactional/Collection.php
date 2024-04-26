<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Ktpl\ExtendedPushNotification\Model\ResourceModel\KtplPushNotificationTransactional;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init(
            \Ktpl\ExtendedPushNotification\Model\KtplPushNotificationTransactional::class,
            \Ktpl\ExtendedPushNotification\Model\ResourceModel\KtplPushNotificationTransactional::class
        );
    }
}
