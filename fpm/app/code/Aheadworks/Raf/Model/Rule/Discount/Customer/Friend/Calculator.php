<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Raf\Model\Rule\Discount\Customer\Friend;

use Aheadworks\Raf\Model\Rule\Discount\Customer\AbstractCalculator;
use Aheadworks\Raf\Model\Rule\Discount\Calculator\Pool as CalculatorPool;
use Aheadworks\Raf\Model\Metadata\Rule\DiscountFactory as MetadataRuleDiscountFactory;
use Aheadworks\Raf\Model\Rule\Discount\Customer\Friend\Resolver\Rule as RuleResolver;

/**
 * Class Calculator
 *
 * @package Aheadworks\Raf\Model\Rule\Discount\Customer\Friend
 */
class Calculator extends AbstractCalculator
{
    /**
     * @param MetadataRuleDiscountFactory $metadataRuleDiscountFactory
     * @param CalculatorPool $calculatorPool
     * @param Validator $validator
     * @param RuleResolver $ruleResolver
     */
    public function __construct(
        MetadataRuleDiscountFactory $metadataRuleDiscountFactory,
        CalculatorPool $calculatorPool,
        Validator $validator,
        RuleResolver $ruleResolver
    ) {
        parent::__construct($metadataRuleDiscountFactory, $calculatorPool, $validator, $ruleResolver);
    }

    /**
     * {@inheritdoc}
     */
    public function calculateDiscount($items, $address, $quote, $rule)
    {
        $metadataRuleDiscount = parent::calculateDiscount($items, $address, $quote, $rule);
        $metadataRuleDiscount->setIsFriendDiscount(true);

        return $metadataRuleDiscount;
    }
}
