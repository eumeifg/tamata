<?php

namespace CAT\VIP\Api\Data;

interface VipCustomerDataInterface
{
    const IS_VIP = 'is_vip';
    const THRESHOLD_VIP_ORDER_COUNT = 'threshold_vip_order_count';
    const CUSTOMER_VIP_ORDER_COUNT = 'customer_vip_order_count';

    /**
     * Get Is VIP
     * @return bool
     */
    public function getIsVip();

    /**
     * @param bool $isVip
     * @return $this
     */
    public function setIsVip($isVip);

    /**
     * Get Threshold VIP Order Count
     * @return int
     */
    public function getThresholdVipOrderCount();

    /**
     * @param int $thresholdVipOrderCount
     * @return $this
     */
    public function setThresholdVipOrderCount($thresholdVipOrderCount);

    /**
     * @return int
     */
    public function getCustomerVipOrderCount();

    /**
     * @param int $customerVipOrderCount
     * @return $this
     */
    public function setCustomerVipOrderCount($customerVipOrderCount);
}
