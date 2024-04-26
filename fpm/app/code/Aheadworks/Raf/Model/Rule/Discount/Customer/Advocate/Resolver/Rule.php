<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Raf\Model\Rule\Discount\Customer\Advocate\Resolver;

use Aheadworks\Raf\Api\AdvocateBalanceManagementInterface;
use Aheadworks\Raf\Api\Data\RuleInterface;
use Aheadworks\Raf\Model\Metadata\Rule as RuleMetadata;
use Aheadworks\Raf\Model\Rule\Discount\Customer\Resolver\AbstractRule;
use Magento\Framework\ObjectManagerInterface;
use Aheadworks\Raf\Model\Rule\Discount\Customer\Advocate\Resolver\Rule\Balance as AdvocateBalanceResolver;

/**
 * Class Rule
 *
 * @package Aheadworks\Raf\Model\Rule\Discount\Customer\Advocate\Resolver
 */
class Rule extends AbstractRule
{
    /**
     * @var AdvocateBalanceManagementInterface
     */
    private $advocateBalanceManagement;

    /**
     * @var AdvocateBalanceResolver
     */
    private $advocateBalanceResolver;

    /**
     * @param ObjectManagerInterface $objectManager
     * @param AdvocateBalanceManagementInterface $advocateBalanceManagement
     * @param AdvocateBalanceResolver $advocateBalanceResolver
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        AdvocateBalanceManagementInterface $advocateBalanceManagement,
        AdvocateBalanceResolver $advocateBalanceResolver
    ) {
        parent::__construct($objectManager);
        $this->advocateBalanceManagement = $advocateBalanceManagement;
        $this->advocateBalanceResolver = $advocateBalanceResolver;
    }

    /**
     * {@inheritdoc}
     */
    protected function prepareData($quote, $address)
    {
        /** @var RuleInterface $rule */
        $rule = $quote->getAwRafRuleToApply();
        $customerId = $quote->getCustomerId();
        $websiteId = $quote->getStore()->getWebsiteId();
        $balance = $this->advocateBalanceManagement->getBalance($customerId, $websiteId);
        $balanceType = $this->advocateBalanceManagement->getDiscountType($customerId, $websiteId);
        $resolvedBalance = $this->advocateBalanceResolver->resolve($quote, $balance, $balanceType);
        $shippingBalance = $this->advocateBalanceResolver->resolveShipping(
            $address,
            $balance,
            $resolvedBalance,
            $balanceType
        );

        $ruleData = [
            RuleMetadata::ID => $rule->getId(),
            RuleMetadata::DISCOUNT_AMOUNT => $resolvedBalance,
            RuleMetadata::DISCOUNT_TYPE => $balanceType,
            RuleMetadata::IS_APPLY_TO_SHIPPING => $rule->isApplyToShipping(),
            RuleMetadata::SHIPPING_DISCOUNT_AMOUNT => $shippingBalance,
            RuleMetadata::CAN_FIX_SHIPPING_DISCOUNT_AMOUNT => true
        ];

        return $ruleData;
    }
}
