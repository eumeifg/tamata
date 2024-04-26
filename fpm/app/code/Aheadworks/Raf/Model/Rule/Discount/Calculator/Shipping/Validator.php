<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Raf\Model\Rule\Discount\Calculator\Shipping;

use Aheadworks\Raf\Model\Metadata\Rule as MetadataRule;
use Magento\Quote\Api\Data\AddressInterface;

/**
 * Class Validator
 *
 * @package Aheadworks\Raf\Model\Rule\Discount\Calculator\Shipping
 */
class Validator
{
    /**
     * Can apply discount on shipping
     *
     * @param AddressInterface $address
     * @param MetadataRule $metadataRule
     * @return bool
     */
    public function canApplyDiscount($address, $metadataRule)
    {
        return $metadataRule->isApplyToShipping() && $metadataRule->getShippingDiscountAmount() > 0;
    }
}
