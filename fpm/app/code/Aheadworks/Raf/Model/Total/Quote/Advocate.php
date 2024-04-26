<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Raf\Model\Total\Quote;

use Aheadworks\Raf\Api\RuleManagementInterface;
use Aheadworks\Raf\Model\Rule\Discount\Customer\Advocate\Calculator as RuleAdvocateCalculator;
use Aheadworks\Raf\Model\Rule\Discount\ItemsApplier as RuleItemsApplier;
use Magento\Framework\Registry;

/**
 * Class Advocate
 *
 * @package Aheadworks\Raf\Model\Total\Quote
 */
class Advocate extends AbstractDiscount
{
    /**
     * @param RuleManagementInterface $ruleManagement
     * @param RuleAdvocateCalculator $ruleCustomerCalculator
     * @param RuleItemsApplier $ruleItemsApplier
     * @param Registry $registry
     */
    public function __construct(
        RuleManagementInterface $ruleManagement,
        RuleAdvocateCalculator $ruleCustomerCalculator,
        RuleItemsApplier $ruleItemsApplier,
        Registry $registry
    ) {
        parent::__construct($ruleManagement, $ruleCustomerCalculator, $ruleItemsApplier, $registry);
    }

    /**
     * {@inheritdoc}
     */
    protected function canProcess()
    {
        return !$this->registry->registry(self::IS_FRIEND_DISCOUNT);
    }
}
