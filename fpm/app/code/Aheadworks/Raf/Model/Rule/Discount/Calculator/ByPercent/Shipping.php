<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Raf\Model\Rule\Discount\Calculator\ByPercent;

use Aheadworks\Raf\Model\Rule\Discount\Calculator\Shipping\Processor;
use Aheadworks\Raf\Model\Rule\Discount\Calculator\Shipping\Validator;
use Aheadworks\Raf\Model\Rule\Discount\Calculator\ShippingCalculatorInterface;

/**
 * Class Shipping
 *
 * @package Aheadworks\Raf\Model\Rule\Discount\Calculator\ByPercent
 */
class Shipping implements ShippingCalculatorInterface
{
    /**
     * @var Validator
     */
    private $validator;

    /**
     * @var Processor
     */
    private $processor;

    /**
     * @param Validator $validator
     * @param Processor $processor
     */
    public function __construct(
        Validator $validator,
        Processor $processor
    ) {
        $this->validator = $validator;
        $this->processor = $processor;
    }

    /**
     * {@inheritdoc}
     */
    public function calculate($address, $metadataRule)
    {
        $rulePercent = null;
        $shippingAmountDiscount = $baseShippingAmountDiscount = 0;
        if ($this->validator->canApplyDiscount($address, $metadataRule)) {
            $shippingAmount = $this->processor->getTotalShippingAmount($address);
            $baseShippingAmount = $this->processor->getTotalBaseShippingAmount($address);
            $rulePercent = $metadataRule->getShippingDiscountAmount();
            $rulePrc = $rulePercent / 100;

            $shippingAmountDiscount = $shippingAmount * $rulePrc;
            $baseShippingAmountDiscount = $baseShippingAmount * $rulePrc;
        }

        return [$rulePercent, $shippingAmountDiscount, $baseShippingAmountDiscount];
    }
}
