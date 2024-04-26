<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Ktpl\ExtendedPushNotification\Model;

class KtplPushNotificationTransactional extends \Magento\Framework\Model\AbstractModel
{
    protected function _construct()
    {
        $this->_init(\Ktpl\ExtendedPushNotification\Model\ResourceModel\KtplPushNotificationTransactional::class);
    }
}
