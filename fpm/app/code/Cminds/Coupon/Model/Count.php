<?php
namespace Cminds\Coupon\Model;

use Cminds\Coupon\Api\Data\CountInterface;
use Magento\Framework\DataObject\IdentityInterface;

class Count extends \Magento\Framework\Model\AbstractModel implements CountInterface, IdentityInterface
{

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Cminds\Coupon\Model\ResourceModel\Count');
    }

    /**
     * Get ID
     *
     * @return int|null
     */
    public function getId()
    {
        return $this->getData(self::ID);
    }

    /**
     * Get ID
     *
     * @return int|null
     */
    public function getRuleId()
    {
        return $this->getData(self::RULE_ID);
    }

    /**
     * Get ID
     *
     * @return int|null
     */
    public function getCouponId()
    {
        return $this->getData(self::COUPON_ID);
    }

    /**
     * Get URL Key
     *
     * @return string|null
     */
    public function getCouponNotApplyRule()
    {
        return $this->getData(self::COUPON_NOT_APPLY_RULE);
    }

    /**
     * Get URL Key
     *
     * @return string|null
     */
    public function getCouponExpired()
    {
        return $this->getData(self::COUPON_EXPIRED);
    }

    /**
     * Get URL Key
     *
     * @return string|null
     */
    public function getCustomerNotBelongGroup()
    {
        return $this->getData(self::CUSTOMER_NOT_BELONG_GROUP);
    }

    /**
     * Get URL Key
     *
     * @return string|null
     */
    public function getCouponUsedMultiple()
    {
        return $this->getData(self::COUPON_USED_MULTIPLE);
    }

    /**
     * Get URL Key
     *
     * @return string|null
     */
    public function getCouponUsedMultipleCustomerGroup()
    {
        return $this->getData(self::COUPON_USED_MULTIPLE_CUSTOMER_GROUP);
    }

    /**
     * Get URL Key
     *
     * @return string|null
     */
    public function getCouponOtherMessages()
    {
        return $this->getData(self::COUPON_OTHER_MESSAGES);
    }

    /**
     * Get URL Key
     *
     * @return string|null
     */
    public function getLastOccured()
    {
        return $this->getData(self::LAST_OCCURED);
    }

    /**
     * Get URL Key
     *
     * @return string|null
     */
    public function getIdentities()
    {
        return $this->getData();
    }

    /**
     * Set ID
     *
     * @param int $id
     * @return \Cminds\Coupon\Api\Data\CountInterface
     */
    public function setId($id)
    {

        return $this->setData(self::ID, $id);
    }

    /**
     * Set ID
     *
     * @param int $rule_id
     * @return \Cminds\Coupon\Api\Data\CountInterface
     */
    public function setRuleId($rule_id)
    {

        return $this->setData(self::RULE_ID, $rule_id);
    }

    /**
     * Set ID
     *
     * @param int $coupon_id
     * @return \Cminds\Coupon\Api\Data\CountInterface
     */
    public function setCouponId($coupon_id)
    {

        return $this->setData(self::COUPON_ID, $coupon_id);
    }

    /**
     * Set ID
     *
     * @param string $couponNotApplyRule
     * @return \Cminds\Coupon\Api\Data\CountInterface
     */
    public function setCouponNotApplyRule($couponNotApplyRule)
    {

        return $this->setData(self::COUPON_NOT_APPLY_RULE, $couponNotApplyRule);
    }

    /**
     * Set ID
     *
     * @param string $couponExpired
     * @return \Cminds\Coupon\Api\Data\CountInterface
     */
    public function setCouponExpired($couponExpired)
    {

        return $this->setData(self::COUPON_EXPIRED, $couponExpired);
    }

    /**
     * Set ID
     *
     * @param string $customerNotBelongGroup
     * @return \Cminds\Coupon\Api\Data\CountInterface
     */
    public function setCustomerNotBelongGroup($customerNotBelongGroup)
    {

        return $this->setData(self::CUSTOMER_NOT_BELONG_GROUP, $customerNotBelongGroup);
    }

    /**
     * Set ID
     *
     * @param string $couponUsedMultiple
     * @return \Cminds\Coupon\Api\Data\CountInterface
     */
    public function setCouponUsedMultiple($couponUsedMultiple)
    {

        return $this->setData(self::COUPON_USED_MULTIPLE, $couponUsedMultiple);
    }

    /**
     * Set ID
     *
     * @param string $couponUsedMultipleCustomerGroup
     * @return \Cminds\Coupon\Api\Data\CountInterface
     */
    public function setCouponUsedMultipleCustomerGroup($couponUsedMultipleCustomerGroup)
    {

        return $this->setData(self::COUPON_USED_MULTIPLE_CUSTOMER_GROUP, $couponUsedMultipleCustomerGroup);
    }

    /**
     * Set ID
     *
     * @param string $couponOtherMessages
     * @return \Cminds\Coupon\Api\Data\CountInterface
     */
    public function setCouponOtherMessages($couponOtherMessages)
    {

        return $this->setData(self::COUPON_OTHER_MESSAGES, $couponOtherMessages);
    }

    /**
     * Set ID
     *
     * @param string $identities
     * @return \Magento\Framework\DataObject\IdentityInterface
     */
    public function setIdenities($identities)
    {

        return $this->setData(self::COUPON_OTHER_MESSAGES, $identities);
    }

    /**
     * Set ID
     *
     * @param int $lastOccured
     * @return \Cminds\Coupon\Api\Data\CountInterface
     */
    public function setLastOccured($lastOccured)
    {

        return $this->setData(self::LAST_OCCURED, $lastOccured);
    }
}