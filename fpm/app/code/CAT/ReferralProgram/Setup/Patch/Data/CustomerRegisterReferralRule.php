<?php

namespace CAT\ReferralProgram\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\PatchInterface;
use Magento\SalesRule\Model\RuleFactory;

/**
 * Class CustomerRegisterReferralRule
 * @package CAT\ReferralProgram\Setup\Patch\Data
 */
class CustomerRegisterReferralRule implements \Magento\Framework\Setup\Patch\DataPatchInterface
{
    /**
     * @var RuleFactory
     */
    protected $ruleFactory;

    /**
     * CustomerRegisterReferralRule constructor.
     * @param RuleFactory $ruleFactory
     */
    public function __construct(
        RuleFactory $ruleFactory
    ) {
        $this->ruleFactory = $ruleFactory;
    }
    /**
     * @inheritDoc
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function apply()
    {
        $salesRule = $this->ruleFactory->create();
        $salesRule->setData(
            [
                'name' => __('Customer Referral Rule'),
                'description' => __('New Registration Customer Referral Rule'),
                'is_active' => 1,
                'uses_per_customer' => 1,
                'from_date' => date('Y-m-d h:i:s'),
                'to_date' => null,
                'customer_group_ids' => [1],
                'coupon_type' => \Magento\SalesRule\Model\Rule::COUPON_TYPE_AUTO,
                'simple_action' => \Magento\SalesRule\Model\Rule::BY_PERCENT_ACTION,
                'discount_amount' => 500,
                'discount_step' => 0,
                'stop_rules_processing' => 0,
                'is_rss' => 0,
                'times_used' => 0,
                'website_ids' => [1]
            ]
        );
        $salesRule->save();
    }
}
