<?php
namespace Cminds\Coupon\Model;

use Cminds\Coupon\Api\Data\LogInterface;

class Log extends \Magento\Framework\Model\AbstractModel implements LogInterface
{

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Cminds\Coupon\Model\ResourceModel\Log');
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
    public function getCouponId()
    {
        return $this->getData(self::COUPON_ID);
    }

    /**
     * Get URL Key
     *
     * @return string|null
     */
    public function getErrorType()
    {
        return $this->getData(self::ERROR_TYPE);
    }

    /**
     * Get URL Key
     *
     * @return string|null
     */
    public function getDatetime()
    {
        return $this->getData(self::DATETIME);
    }

    /**
     * Set ID
     *
     * @param int $id
     * @return \Cminds\Coupon\Api\Data\LogInterface
     */
    public function setId($id)
    {

        return $this->setData(self::ID, $id);
    }

    /**
     * Set ID
     *
     * @param int $coupon_id
     * @return \Cminds\Coupon\Api\Data\LogInterface
     */
    public function setCouponId($coupon_id)
    {

        return $this->setData(self::COUPON_ID, $coupon_id);
    }

    /**
     * Set ID
     *
     * @param string $errorType
     * @return \Cminds\Coupon\Api\Data\LogInterface
     */
    public function setErrorType($errorType)
    {

        return $this->setData(self::ERROR_TYPE, $errorType);
    }

    /**
     * Set ID
     *
     * @param string $datetime
     * @return \Cminds\Coupon\Api\Data\LogInterface
     */
    public function setDatetime($datetime)
    {

        return $this->setData(self::DATETIME, $datetime);
    }
}