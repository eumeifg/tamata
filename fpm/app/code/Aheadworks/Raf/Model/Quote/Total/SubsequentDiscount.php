<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Raf\Model\Quote\Total;

use Aheadworks\Raf\Api\RuleManagementInterface;
use Aheadworks\Raf\Model\Rule\Discount\Customer\Advocate\Calculator as RuleAdvocateCalculator;
use Aheadworks\Raf\Model\Rule\Discount\Customer\Friend\Calculator as RuleFriendCalculator;
use Magento\Quote\Model\Quote;
use Magento\Quote\Api\Data\ShippingAssignmentInterface;
use Aheadworks\Raf\Model\Config;

/**
 * Class SubsequentDiscount
 *
 * @package Aheadworks\Raf\Model\Quote\Total
 */
class SubsequentDiscount
{
    /**
     * @var RuleManagementInterface
     */
    private $ruleManagement;

    /**
     * @var RuleAdvocateCalculator
     */
    private $advCalculator;

    /**
     * @var RuleFriendCalculator
     */
    private $friendCalculator;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var bool
     */
    private $result;

    /**
     * @param RuleManagementInterface $ruleManagement
     * @param RuleAdvocateCalculator $advCalculator
     * @param RuleFriendCalculator $friendCalculator
     * @param Config $config
     */
    public function __construct(
        RuleManagementInterface $ruleManagement,
        RuleAdvocateCalculator $advCalculator,
        RuleFriendCalculator $friendCalculator,
        Config $config
    ) {
        $this->ruleManagement = $ruleManagement;
        $this->advCalculator = $advCalculator;
        $this->friendCalculator = $friendCalculator;
        $this->config = $config;
    }

    /**
     * Check if subsequent discounts can be applied
     *
     * @param Quote $quote
     * @param ShippingAssignmentInterface $shippingAssignment
     * @return bool
     */
    public function canBeApplied(Quote $quote, ShippingAssignmentInterface $shippingAssignment)
    {
        if ($this->result !== null) {
            return $this->result;
        }

        $items = $shippingAssignment->getItems();
        if (!count($items)) {
            return true;
        }

        $this->result = true;
        $websiteId = $quote->getStore()->getWebsiteId();
        if (!$this->config->isSubsequentDiscountsAllowed($websiteId)) {
            /** @var \Magento\Quote\Model\Quote\Address $address */
            $address = $shippingAssignment->getShipping()->getAddress();
            try {
                $rule = $this->ruleManagement->getActiveRule($quote->getStore()->getWebsiteId());
                $advDiscount = $this->advCalculator->calculateDiscount($items, $address, $quote, $rule);
                $friendDiscount = $this->friendCalculator->calculateDiscount($items, $address, $quote, $rule);
                $this->result = !($advDiscount->isDiscountAvailable() || $friendDiscount->isDiscountAvailable());
            } catch (\Exception $exception) {
            }
        }

        return $this->result;
    }
}
