<?php

namespace Ktpl\Helpdesk\Model;

use \Ktpl\Helpdesk\Api\Data\CreateTicketInterface;

class CreateTicket extends \Magento\Framework\Model\AbstractModel implements CreateTicketInterface
{
    /**
     * {@inheritdoc}
     */
    public function getPriority()
    {
        return $this->getData(self::KEY_PRIORITY);
    }

    /**
     * {@inheritdoc}
     */
    public function setPriority($priority)
    {
        return $this->setData(self::KEY_PRIORITY, $priority);
    }

    /**
     * {@inheritdoc}
     */
    public function getDepartment()
    {
        return $this->getData(self::KEY_DEPARTMENT);
    }

    /**
     * {@inheritdoc}
     */
    public function setDepartment($department)
    {
        return $this->setData(self::KEY_DEPARTMENT, $department);
    }

    /**
     * {@inheritdoc}
     */
    public function getOrder()
    {
        return $this->getData(self::KEY_ORDER);
    }

    /**
     * {@inheritdoc}
     */
    public function setOrder($order)
    {
        return $this->setData(self::KEY_ORDER, $order);
    }

    /**
     * {@inheritdoc}
     */
    public function getCustomerId()
    {
        return $this->getData(self::KEY_CUSTOMER_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setCustomerId($customerId)
    {
        return $this->setData(self::KEY_CUSTOMER_ID, $customerId);
    }
}
