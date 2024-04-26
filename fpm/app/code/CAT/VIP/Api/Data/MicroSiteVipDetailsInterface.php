<?php

namespace CAT\VIP\Api\Data;

interface MicroSiteVipDetailsInterface
{
    public function getVipPrice();

    /**
     * @param $value
     * @return mixed
     */
    public function setVipPrice($value);
}
