<?php

namespace Ktpl\Helpdesk\Model;

use \Ktpl\Helpdesk\Api\Data\OrderDataInterface;

class OrderData extends \Magento\Framework\Model\AbstractModel implements OrderDataInterface
{
    /**
     * {@inheritdoc}
     */
    public function getOrderId()
    {
        return $this->getData(self::KEY_ORDER_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setOrderId($orderId)
    {
        return $this->setData(self::KEY_ORDER_ID, $orderId);
    }

    /**
     * {@inheritdoc}
     */
    public function getOrderValue()
    {
        return $this->getData(self::KEY_ORDER_VALUE);
    }

    /**
     * {@inheritdoc}
     */
    public function setOrderValue($orderVal)
    {
        return $this->setData(self::KEY_ORDER_VALUE, $orderVal);
    }
}