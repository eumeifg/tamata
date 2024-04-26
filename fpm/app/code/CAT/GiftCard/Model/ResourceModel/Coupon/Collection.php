<?php

namespace CAT\GiftCard\Model\ResourceModel\Coupon;

use Magento\Backend\Block\Widget\Grid\Column;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use CAT\GiftCard\Model\GiftCardRule;


class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'coupon_id';

    /**
     * @inheritDoc
     */
    public function _construct()
    {
        parent::_construct();
        $this->_init(\CAT\GiftCard\Model\Coupon::class, \CAT\GiftCard\Model\ResourceModel\Coupon::class);
    }

    /**
     * Add rule to filter
     *
     * @param GiftCardRule|int $rule
     * @return \CAT\GiftCard\Model\ResourceModel\Coupon\Collection
     */
    public function addRuleToFilter($rule)
    {
        if ($rule instanceof GiftCardRule) {
            $ruleId = $rule->getId();
        } else {
            $ruleId = (int)$rule;
        }

        $this->addFieldToFilter('rule_id', $ruleId);

        return $this;
    }

    /**
     * Add rule IDs to filter
     *
     * @param array $ruleIds
     * @return $this
     */
    public function addRuleIdsToFilter(array $ruleIds)
    {
        $this->addFieldToFilter('rule_id', ['in' => $ruleIds]);
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

    /**
     * Callback function that filters collection by field "Used" from grid
     *
     * @param AbstractCollection $collection
     * @param Column $column
     * @return void
     */
    public function addIsUsedFilterCallback($collection, $column)
    {
        $filterValue = $column->getFilter()->getCondition();

        $expression = $this->getConnection()->getCheckSql('main_table.times_used > 0', 1, 0);
        $conditionSql = $this->_getConditionSql($expression, $filterValue);
        $collection->getSelect()->where($conditionSql);
    }
}