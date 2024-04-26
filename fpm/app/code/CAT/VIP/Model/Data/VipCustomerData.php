<?php

namespace CAT\VIP\Model\Data;

use CAT\VIP\Api\Data\VipCustomerDataInterface;
use Magento\Framework\DataObject;

class VipCustomerData extends DataObject implements VipCustomerDataInterface
{

    /**
     * @inheritDoc
     */
    public function getIsVip()
    {
        return $this->getData(self::IS_VIP);
    }

    /**
     * @inheritDoc
     */
    public function setIsVip($isVip)
    {
        return $this->setData(self::IS_VIP, $isVip);
    }

    /**
     * @inheritDoc
     */
    public function getThresholdVipOrderCount()
    {
        return $this->getData(self::THRESHOLD_VIP_ORDER_COUNT);
    }

    /**
     * @inheritDoc
     */
    public function setThresholdVipOrderCount($thresholdVipOrderCount)
    {
        return $this->setData(self::THRESHOLD_VIP_ORDER_COUNT, $thresholdVipOrderCount);
    }

    /**
     * @inheritDoc
     */
    public function getCustomerVipOrderCount()
    {
        return $this->getData(self::CUSTOMER_VIP_ORDER_COUNT);
    }

    /**
     * @inheritDoc
     */
    public function setCustomerVipOrderCount($customerVipOrderCount)
    {
        return $this->setData(self::CUSTOMER_VIP_ORDER_COUNT, $customerVipOrderCount);
    }
}
