<?php namespace Cminds\Coupon\Model\ResourceModel\Coupon;

class Collection extends \Magento\SalesRule\Model\ResourceModel\Coupon\Collection
{
    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
    }

    public function addCountedErrors()
    {
        $this->getSelect()
            ->joinLeft($this->getTable('coupon_error_messages_count') . ' AS c', 'main_table.coupon_id = c.coupon_id and main_table.rule_id = c.rule_id');
        return $this;
    }

    public function addRuleToFilter($rule)
    {
        if ($rule instanceof \Magento\SalesRule\Model\Rule) {
            $ruleId = $rule->getId();
        } else {
            $ruleId = (int)$rule;
        }

        $this->addFieldToFilter('main_table.rule_id', $ruleId);

        return $this;
    }

    /**
     * Filter collection to be filled with auto-generated coupons only
     *
     * @return $this
     */
    public function addGeneratedCouponsFilter()
    {
        $this->addFieldToFilter('is_primary', ['null' => 1])->addFieldToFilter('type', '1');
        return $this;
    }

}