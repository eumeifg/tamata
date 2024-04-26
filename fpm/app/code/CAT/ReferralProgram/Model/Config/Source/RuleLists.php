<?php

namespace CAT\ReferralProgram\Model\Config\Source;

use Magento\SalesRule\Model\RuleFactory;

/**
 * Class RuleLists
 * @package CAT\ReferralProgram\Model\Config\Source
 */
class RuleLists implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * @var RuleFactory
     */
    protected $ruleFactory;

    /**
     * RuleLists constructor.
     * @param RuleFactory $ruleFactory
     */
    public function __construct(
        RuleFactory $ruleFactory
    ) {
        $this->ruleFactory = $ruleFactory;
    }

    public function toOptionArray()
    {
        return $this->getAllCartRules();
    }

    public function getAllCartRules() {
        $ruleCollection = $this->ruleFactory->create()->getCollection();
        $ruleCollection->addFieldToSelect(['rule_id', 'name']);
        $ruleCollection->addFieldToFilter('is_active', ['eq' => 1]);
        $ruleCollection->setOrder('rule_id', 'DESC');
        $ruleOptions = [['value' => '', 'label' => __('----- Please Select -----')]];
        if ($ruleCollection->getSize()) {
            foreach ($ruleCollection as $rule) {
                $ruleOptions[] = ['value' => $rule->getRuleId(), 'label' => $rule->getName()];
            }
            return $ruleOptions;
        }
        return $ruleOptions;
    }
}
