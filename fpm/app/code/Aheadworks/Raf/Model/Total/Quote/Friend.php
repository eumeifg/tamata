<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Raf\Model\Total\Quote;

use Aheadworks\Raf\Api\RuleManagementInterface;
use Aheadworks\Raf\Model\Rule\Discount\Customer\Friend\Calculator as RuleFriendCalculator;
use Aheadworks\Raf\Model\Rule\Discount\ItemsApplier as RuleItemsApplier;
use Magento\Framework\Registry;

/**
 * Class Friend
 *
 * @package Aheadworks\Raf\Model\Total\Quote
 */
class Friend extends AbstractDiscount
{
    /**
     * @param RuleManagementInterface $ruleManagement
     * @param RuleFriendCalculator $ruleCustomerCalculator
     * @param RuleItemsApplier $ruleItemsApplier
     * @param Registry $registry
     */
    public function __construct(
        RuleManagementInterface $ruleManagement,
        RuleFriendCalculator $ruleCustomerCalculator,
        RuleItemsApplier $ruleItemsApplier,
        Registry $registry
    ) {
        parent::__construct($ruleManagement, $ruleCustomerCalculator, $ruleItemsApplier, $registry);
    }
}
