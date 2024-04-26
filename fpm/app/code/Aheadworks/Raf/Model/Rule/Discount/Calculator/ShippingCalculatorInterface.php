<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Raf\Model\Rule\Discount\Calculator;

use Aheadworks\Raf\Model\Metadata\Rule as MetadataRule;
use Magento\Quote\Api\Data\AddressInterface;

/**
 * Interface ShippingCalculatorInterface
 *
 * @package Aheadworks\Raf\Model\Rule\Discount\Calculator
 */
interface ShippingCalculatorInterface
{
    /**
     * Calculate shipping discount
     *
     * @param AddressInterface $address
     * @param MetadataRule $metadataRule
     * @return array
     */
    public function calculate($address, $metadataRule);
}
