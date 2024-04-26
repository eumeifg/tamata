<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Raf\Model\Rule\Discount\Calculator;

use Aheadworks\Raf\Model\Metadata\Rule\Discount\Item as MetadataRuleDiscountItem;
use Magento\Quote\Model\Quote\Item\AbstractItem;
use Aheadworks\Raf\Model\Metadata\Rule as MetadataRule;

/**
 * Interface ItemsCalculatorInterface
 *
 * @package Aheadworks\Raf\Model\Rule\Discount\Calculator
 */
interface ItemsCalculatorInterface
{
    /**
     * Calculate items discount
     *
     * @param AbstractItem[] $items
     * @param MetadataRule $metadataRule
     * @return MetadataRuleDiscountItem[]
     */
    public function calculate($items, $metadataRule);
}
