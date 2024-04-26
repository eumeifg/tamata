<?php

namespace Ktpl\Helpdesk\Model;

use \Ktpl\Helpdesk\Api\Data\PriorityInterface;

class Priority extends \Magento\Framework\Model\AbstractModel implements PriorityInterface
{
    /**
     * {@inheritdoc}
     */
    public function getPriorityId()
    {
        return $this->getData(self::KEY_PRIORITY_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setPriorityId($priorityId)
    {
        return $this->setData(self::KEY_PRIORITY_ID, $priorityId);
    }

    /**
     * {@inheritdoc}
     */
    public function getPriorityValue()
    {
        return $this->getData(self::KEY_PRIORITY_VALUE);
    }

    /**
     * {@inheritdoc}
     */
    public function setPriorityValue($priorityVal)
    {
        return $this->setData(self::KEY_PRIORITY_VALUE, $priorityVal);
    }
}