<?php

namespace CAT\GiftCard\Model\ResourceModel\Rule;

/**
 * Class Customer
 * @package CAT\GiftCard\Model\ResourceModel\Rule
 */
class Customer extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('giftcard_customer', 'rule_customer_id');
    }

    /**
     * Get rule usage record for a customer
     *
     * @param \CAT\GiftCard\Model\Rule\Customer $rule
     * @param int $customerId
     * @param int $ruleId
     * @return \CAT\GiftCard\Model\ResourceModel\Rule\Customer
     */
    public function loadByCustomerRule($rule, $customerId, $ruleId)
    {
        $connection = $this->getConnection();
        $select = $connection->select()->from(
            $this->getMainTable()
        )->where(
            'customer_id = :customer_id'
        )->where(
            'rule_id = :rule_id'
        );
        $data = $connection->fetchRow($select, [':rule_id' => $ruleId, ':customer_id' => $customerId]);
        if (false === $data) {
            // set empty data, as an existing rule object might be used
            $data = [];
        }
        $rule->setData($data);
        return $this;
    }
}