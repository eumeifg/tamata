<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Raf\Model\Rule\Discount\Calculator\CartByFixed;

use Aheadworks\Raf\Model\Rule\Discount\Calculator\Shipping\Processor;
use Aheadworks\Raf\Model\Rule\Discount\Calculator\Shipping\Validator;
use Aheadworks\Raf\Model\Rule\Discount\Calculator\ShippingCalculatorInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;

/**
 * Class Shipping
 *
 * @package Aheadworks\Raf\Model\Rule\Discount\Calculator\CartByFixed
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
     * @var PriceCurrencyInterface
     */
    private $priceCurrency;

    /**
     * @param Validator $validator
     * @param Processor $processor
     * @param PriceCurrencyInterface $priceCurrency
     */
    public function __construct(
        Validator $validator,
        Processor $processor,
        PriceCurrencyInterface $priceCurrency
    ) {
        $this->validator = $validator;
        $this->processor = $processor;
        $this->priceCurrency = $priceCurrency;
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

            $shippingAmountDiscount = min(
                $shippingAmount,
                $this->priceCurrency->convertAndRound($metadataRule->getShippingDiscountAmount())
            );
            $baseShippingAmountDiscount = min(
                $baseShippingAmount,
                $metadataRule->getShippingDiscountAmount()
            );
        }

        return [$rulePercent, $shippingAmountDiscount, $baseShippingAmountDiscount];
    }
}
