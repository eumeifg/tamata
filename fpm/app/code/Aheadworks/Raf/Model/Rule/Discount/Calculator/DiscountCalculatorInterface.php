<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Raf\Model\Rule\Discount\Calculator;

use Aheadworks\Raf\Model\Metadata\Rule as MetadataRule;
use Aheadworks\Raf\Model\Metadata\Rule\Discount as MetadataRuleDiscount;
use Magento\Quote\Model\Quote\Item\AbstractItem;
use Magento\Quote\Api\Data\AddressInterface;

/**
 * Interface DiscountCalculatorInterface
 *
 * @package Aheadworks\Raf\Model\Rule\Discount\Calculator
 */
interface DiscountCalculatorInterface
{
    /**
     * Calculate discount
     *
     * @param AbstractItem[] $items
     * @param AddressInterface $address
     * @param MetadataRule $metadataRule
     * @return MetadataRuleDiscount
     */
    public function calculate($items, $address, $metadataRule);
}
