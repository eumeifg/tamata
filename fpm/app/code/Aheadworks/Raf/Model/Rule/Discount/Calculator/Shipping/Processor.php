<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Raf\Model\Rule\Discount\Calculator\Shipping;

use Magento\Quote\Api\Data\AddressInterface;

/**
 * Class Processor
 *
 * @package Aheadworks\Raf\Model\Rule\Discount\Calculator\Shipping
 */
class Processor
{
    /**
     * Retrieve base shipping amount
     *
     * @param AddressInterface $address
     * @return float
     */
    public function getTotalBaseShippingAmount($address)
    {
        if ($address->getBaseShippingAmountForDiscount() !== null) {
            $baseShippingAmount = $address->getBaseShippingAmountForDiscount();
        } else {
            $baseShippingAmount = $address->getBaseShippingAmount();
        }
        $baseShippingAmount = $baseShippingAmount - $address->getBaseShippingDiscountAmount()
            - $address->getBaseAwRewardPointsShippingAmount();

        return $baseShippingAmount;
    }

    /**
     * Retrieve shipping amount
     *
     * @param AddressInterface $address
     * @return float
     */
    public function getTotalShippingAmount($address)
    {
        if ($address->getShippingAmountForDiscount() !== null) {
            $shippingAmount = $address->getShippingAmountForDiscount();
        } else {
            $shippingAmount = $address->getShippingAmount();
        }
        $shippingAmount = $shippingAmount - $address->getShippingDiscountAmount()
            - $address->getAwRewardPointsShippingAmount();

        return $shippingAmount;
    }
}
