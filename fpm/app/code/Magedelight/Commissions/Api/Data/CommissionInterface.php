<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Commissions
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Commissions\Api\Data;

/**
 * @api
 */
interface CommissionInterface
{
    /**#@+
     * Constants defined for keys of  data array
     */
    const CALCULATION_TYPE = 'calculation_type';
    const COMMISSION_VALUE = 'commission_value';
    const MARKETPLACE_FEE_TYPE = 'marketplace_fee_type';
    const MARKETPLACE_FEE = 'marketplace_fee';
    const PRODUCT_CATEGORY = 'product_category';
    const STATUS = 'status';

    /**
     * Commission id
     *
     * @return int|null
     */
    public function getId();

    /**
     * Set Commission id
     *
     * @param int $id
     * @return $this
     */
    public function setId($id);

    /**
     * Commission calculation type
     *
     * @return int
     */
    public function getCalculationType();

    /**
     * Set Commission calculation type
     *
     * @param int $calculation_type
     * @return $this
     */
    public function setCalculationType($calculation_type);

    /**
     * Commission value
     *
     * @return float
     */
    public function getCommissionValue();

    /**
     * Set Commission value
     *
     * @param float $value
     * @return $this
     */
    public function setCommissionValue($value);
    
    /**
     * Marketplace fee type
     *
     * @return int
     */
    public function getMarketplaceFeeType();

    /**
     * Set marketplace fee type
     *
     * @param int $marketplace_fee_type
     * @return $this
     */
    public function setMarketplaceFeeType($marketplace_fee_type);

    /**
     * Marketplace Fee for product listing
     *
     * @return float
     */
    public function getMarketplaceFee();

    /**
     * Set Marketplace Fee for product listing
     *
     * @param float $fee
     * @return $this
     */
    public function setMarketplaceFee($fee);

    /**
     * product category
     *
     * @return int
     */
    public function getProductCategory();

    /**
     * Set product category
     *
     * @param int $category_id
     * @return $this
     */
    public function setProductCategory($category_id);

    /**
     * status
     *
     * @return boolean
     */
    public function getStatus();

    /**
     * Set status
     *
     * @param boolean $status
     * @return $this
     */
    public function setStatus($status);
}
