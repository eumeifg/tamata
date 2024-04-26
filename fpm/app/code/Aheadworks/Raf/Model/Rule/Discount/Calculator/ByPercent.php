<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Raf\Model\Rule\Discount\Calculator;

use Aheadworks\Raf\Model\Metadata\Rule\DiscountFactory as MetadataRuleDiscountFactory;
use Aheadworks\Raf\Model\Rule\Discount\Calculator\ByPercent\Items as ItemsCalculator;
use Aheadworks\Raf\Model\Rule\Discount\Calculator\ByPercent\Shipping as ShippingCalculator;

/**
 * Class ByPercent
 *
 * @package Aheadworks\Raf\Model\Rule\Discount\Calculator
 */
class ByPercent extends AbstractCalculator implements DiscountCalculatorInterface
{
    /**
     * @param MetadataRuleDiscountFactory $metadataRuleDiscountFactory
     * @param ItemsCalculator $itemsCalculator
     * @param ShippingCalculator $shippingCalculator
     */
    public function __construct(
        MetadataRuleDiscountFactory $metadataRuleDiscountFactory,
        ItemsCalculator $itemsCalculator,
        ShippingCalculator $shippingCalculator
    ) {
        parent::__construct($metadataRuleDiscountFactory, $itemsCalculator, $shippingCalculator);
    }

    /**
     * {@inheritdoc}
     */
    protected function fixShippingDiscount($metadataRule, $baseAmountDiscount)
    {
        return $metadataRule;
    }
}
