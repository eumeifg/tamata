<?php

namespace CAT\GiftCard\Model\Rule;

/**
 * SalesRule Rule Customer Model
 *
 * @method int getRuleId()
 * @method \CAT\GiftCard\Model\Rule\Customer setRuleId(int $value)
 * @method int getCustomerId()
 * @method \CAT\GiftCard\Model\Rule\Customer setCustomerId(int $value)
 * @method int getTimesUsed()
 * @method \CAT\GiftCard\Model\Rule\Customer setTimesUsed(int $value)
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Customer extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init(\CAT\GiftCard\Model\ResourceModel\Rule\Customer::class);
    }

    /**
     * Load by customer rule
     *
     * @param int $customerId
     * @param int $ruleId
     * @return \CAT\GiftCard\Model\Rule\Customer
     */
    public function loadByCustomerRule($customerId, $ruleId)
    {
        $this->_getResource()->loadByCustomerRule($this, $customerId, $ruleId);
        return $this;
    }
}