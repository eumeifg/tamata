<?php

declare (strict_types = 1);

namespace Ktpl\Pushnotification\Model\Data;

use Ktpl\Pushnotification\Api\Data\KtplPushnotificationDataInterface;

class KtplPushnotificationData extends \Magento\Framework\Api\AbstractExtensibleObject implements KtplPushnotificationDataInterface
{
    /**
     * Get the list of notifications
     * @return array|null
     */
    public function getNotifications()
    {
        return $this->_get(self::NOTIFICATIONS);
    }

    /**
     * Set the list of notifications
     * @param \Ktpl\Pushnotification\Api\Data\KtplPushnotificationObjInterface[] $data
     * @return \Ktpl\Pushnotification\Api\Data\KtplPushnotificationObjInterface
     */
    public function setNotifications(array $data = null)
    {
        return $this->setData(self::NOTIFICATIONS, $data);
    }
}
