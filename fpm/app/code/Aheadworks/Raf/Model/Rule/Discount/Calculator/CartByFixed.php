<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Raf\Model\Rule\Discount\Calculator;

use Aheadworks\Raf\Model\Metadata\Rule\DiscountFactory as MetadataRuleDiscountFactory;
use Aheadworks\Raf\Model\Rule\Discount\Calculator\CartByFixed\Items as ItemsCalculator;
use Aheadworks\Raf\Model\Rule\Discount\Calculator\CartByFixed\Shipping as ShippingCalculator;

/**
 * Class CartByFixed
 *
 * @package Aheadworks\Raf\Model\Rule\Discount\Calculator
 */
class CartByFixed extends AbstractCalculator
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
        if ($metadataRule->canFixShippingDiscountAmount()) {
            $baseAmountDiscountLeft = $metadataRule->getDiscountAmount() - $baseAmountDiscount;
            $baseAmountDiscountLeft = $baseAmountDiscountLeft > 0 ? $baseAmountDiscountLeft : 0;

            $fixShippingDiscount = $baseAmountDiscountLeft + $metadataRule->getShippingDiscountAmount();

            $metadataRule->setShippingDiscountAmount($fixShippingDiscount);
        }

        return $metadataRule;
    }
}
