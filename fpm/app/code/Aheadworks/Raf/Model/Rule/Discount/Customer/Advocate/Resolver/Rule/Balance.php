<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Raf\Model\Rule\Discount\Customer\Advocate\Resolver\Rule;

use Aheadworks\Raf\Model\Config;
use Aheadworks\Raf\Model\Rule\Discount\Calculator\Shipping\Processor;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Model\Quote;
use Aheadworks\Raf\Model\Source\Rule\AdvocateOffType;

/**
 * Class Balance
 *
 * @package Aheadworks\Raf\Model\Rule\Discount\Customer\Advocate\Resolver\Rule
 */
class Balance
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var Processor
     */
    private $processor;

    /**
     * @param Config $config
     * @param Processor $processor
     */
    public function __construct(Config $config, Processor $processor)
    {
        $this->config = $config;
        $this->processor = $processor;
    }

    /**
     * Resolve balance
     *
     * @param Quote $quote
     * @param float $balance
     * @param string $balanceType
     * @return float
     */
    public function resolve($quote, $balance, $balanceType)
    {
        $resolvedBalance = $balance;
        $maxDiscountApplyToSubtotal = $this->config->getMaximumDiscountToApplyToSubtotal(
            $quote->getStore()->getWebsiteId()
        );
        if ($balanceType == AdvocateOffType::FIXED && $maxDiscountApplyToSubtotal) {
            $maxDiscountApplyToSubtotal = max(0, floatval($maxDiscountApplyToSubtotal));
            $maxSubtotal = $quote->getSubtotal() * $maxDiscountApplyToSubtotal / 100;
            $resolvedBalance = min($balance, $maxSubtotal);
        }

        if ($balanceType == AdvocateOffType::PERCENT) {
            $maxDiscountApplyToSubtotal = $maxDiscountApplyToSubtotal ? : 100;
            $resolvedBalance = min($balance, $maxDiscountApplyToSubtotal);
        }

        return $resolvedBalance;
    }

    /**
     * Resolve shipping balance
     *
     * @param AddressInterface $address
     * @param float $balance
     * @param float $resolvedBalance
     * @param string $balanceType
     * @return float
     */
    public function resolveShipping($address, $balance, $resolvedBalance, $balanceType)
    {
        $shippingBalance = 0;
        $baseShippingAmount = $this->processor->getTotalBaseShippingAmount($address);
        if ($balanceType == AdvocateOffType::FIXED) {
            $availableBalance = $balance - $resolvedBalance;
            $shippingBalance = min($availableBalance, $baseShippingAmount);
        }
        if ($balanceType == AdvocateOffType::PERCENT) {
            $shippingBalance = $resolvedBalance;
        }

        return $shippingBalance;
    }
}
