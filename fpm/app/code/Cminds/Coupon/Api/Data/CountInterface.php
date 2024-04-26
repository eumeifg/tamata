<?php
namespace Cminds\Coupon\Api\Data;

interface CountInterface
{
    /**
     * Constants for keys of data array. Identical to the name of the getter in snake case
     */
    const ID = 'id';
    const RULE_ID = 'rule_id';
    const COUPON_ID = 'coupon_id';
    const COUPON_NOT_APPLY_RULE = 'coupon_not_apply_rule';
    const COUPON_EXPIRED = 'coupon_expired';
    const CUSTOMER_NOT_BELONG_GROUP = 'customer_not_belong_group';
    const COUPON_USED_MULTIPLE = 'coupon_used_multiple';
    const COUPON_USED_MULTIPLE_CUSTOMER_GROUP = 'coupon_used_multiple_customer_group';
    const COUPON_OTHER_MESSAGES = 'coupon_other_messages';
    const LAST_OCCURED = 'last_occured';

    /**
     * Get ID
     *
     * @return int|null
     */
    public function getId();

    /**
     *
     *
     * @return int|null
     */
    public function getRuleId();

    /**
     *
     *
     * @return int|null
     */
    public function getCouponId();

    /**
     *
     *
     * @return string|null
     */
    public function getCouponNotApplyRule();

    /**
     *
     *
     * @return string|null
     */
    public function getCouponExpired();

    /**
     *
     *
     * @return string|null
     */
    public function getCustomerNotBelongGroup();

    /**
     *
     *
     * @return string|null
     */
    public function getCouponUsedMultiple();

    /**
     *
     *
     * @return string|null
     */
    public function getCouponUsedMultipleCustomerGroup();

    /**
     *
     *
     * @return string|null
     */
    public function getCouponOtherMessages();

    /**
     *
     *
     * @return string|null
     */
    public function getLastOccured();

    /**
     * Set ID
     *
     * @param int $id
     * @return \Cminds\Coupon\Api\Data\CountInterface
     */
    public function setId($id);

    /**
     * Set ID
     *
     * @param int $rule_id
     * @return \Cminds\Coupon\Api\Data\CountInterface
     */
    public function setRuleId($rule_id);

    /**
     * Set ID
     *
     * @param int $coupon_id
     * @return \Cminds\Coupon\Api\Data\CountInterface
     */
    public function setCouponId($coupon_id);

    /**
     * Set ID
     *
     * @param string $couponNotApplyRule
     * @return \Cminds\Coupon\Api\Data\CountInterface
     */
    public function setCouponNotApplyRule($couponNotApplyRule);

    /**
     * Set ID
     *
     * @param string $couponExpired
     * @return \Cminds\Coupon\Api\Data\CountInterface
     */
    public function setCouponExpired($couponExpired);

    /**
     * Set ID
     *
     * @param string $customerNotBelongGroup
     * @return \Cminds\Coupon\Api\Data\CountInterface
     */
    public function setCustomerNotBelongGroup($customerNotBelongGroup);

    /**
     * Set ID
     *
     * @param string $couponUsedMultiple
     * @return \Cminds\Coupon\Api\Data\CountInterface
     */
    public function setCouponUsedMultiple($couponUsedMultiple);

    /**
     * Set ID
     *
     * @param string $couponUsedMultipleCustomerGroup
     * @return \Cminds\Coupon\Api\Data\CountInterface
     */
    public function setCouponUsedMultipleCustomerGroup($couponUsedMultipleCustomerGroup);

    /**
     * Set ID
     *
     * @param string $couponOtherMessages
     * @return \Cminds\Coupon\Api\Data\CountInterface
     */
    public function setCouponOtherMessages($couponOtherMessages);

    /**
     * Set ID
     *
     * @param string $lastOccured
     * @return \Cminds\Coupon\Api\Data\CountInterface
     */
    public function setLastOccured($lastOccured);


}