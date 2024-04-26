<?php
namespace Cminds\Coupon\Api\Data;

interface LogInterface
{
    /**
     * Constants for keys of data array. Identical to the name of the getter in snake case
     */
    const ID = 'id';
    const RULE_ID = 'rule_id';
    const COUPON_ID = 'coupon_id';
    const ERROR_TYPE = 'error_type';
    const DATETIME = 'datetime';

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
    public function getCouponId();

    /**
     *
     *
     * @return string|null
     */
    public function getErrorType();

    /**
     *
     *
     * @return string|null
     */
    public function getDatetime();

    /**
     * Set ID
     *
     * @param int $id
     * @return \Cminds\Coupon\Api\Data\LogInterface
     */
    public function setId($id);

    /**
     * Set ID
     *
     * @param int $coupon_id
     * @return \Cminds\Coupon\Api\Data\LogInterface
     */
    public function setCouponId($coupon_id);

    /**
     * Set ID
     *
     * @param string $errorType
     * @return \Cminds\Coupon\Api\Data\LogInterface
     */
    public function setErrorType($errorType);

    /**
     * Set ID
     *
     * @param string $datetime
     * @return \Cminds\Coupon\Api\Data\LogInterface
     */
    public function setDatetime($datetime);
}