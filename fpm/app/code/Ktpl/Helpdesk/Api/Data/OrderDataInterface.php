<?php

namespace Ktpl\Helpdesk\Api\Data;

interface OrderDataInterface
{
    const KEY_ORDER_ID = 'order_id';
    const KEY_ORDER_VALUE = 'order_value';

    /**
     * @return string
     */
    public function getOrderId();

    /**
     * @param int $orderId
     * @return $this
     */
    public function setOrderId($OrderId);

    /**
     * @return string
     */
    public function getOrderValue();

    /**
     * @param string $orderVal
     * @return $this
     */
    public function setOrderValue($orderVal);
}