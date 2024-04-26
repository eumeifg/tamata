<?php

namespace CAT\VIP\Api;

interface VipCustomerInterface
{
    /**
     * @api
     * @return Data\VipCustomerDataInterface
     */
    public function getVipForCustomer();
}
