<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Vendor
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Vendor\Api\Data;

/**
 * @api
 */

interface VendorRatingDataInterface
{
    /**#@+
     * Constants defined for keys of the data array. Identical to the name of the getter in snake case
     */
    const ENTITY_ID = 'entity_id';
    const VENDOR_RATING_ID = 'vendor_rating_id';
    const OPTION_ID = 'option_id';
    const RATING_VALUE = 'rating_value';
    const RATING_AVG = 'rating_avg';
    const STORE_ID = 'store_id';
    const CREATED_AT = 'created_at';
    const RATING_CODE = 'rating_code';

    /**
     * Get Entity Id
     *
     * @return int
     */
    public function getEntityId();

    /**
     * Set Entity Id
     *
     * @param int $id
     * @return $this
     */
    public function setEntityId($id);

    /**
     * Get Vendor Rating ID
     * @return int
     */
    public function getVendorRatingId();

    /**
     * Set Vendor Rating ID
     * @param int $vendorRatingId
     * @return $this
     */
    public function setVendorRatingId($vendorRatingId);

    /**
     * Get Option Id
     *
     * @return int
     */
    public function getOptionId();

    /**
     * Set Option Id
     * @param int $optionId
     * @return $this
     */
    public function setOptionId($optionId);

    /**
     * Get Rating Value
     *
     * @return int
     */
    public function getRatingValue();

    /**
     * Set Rating Value
     * @param int $value
     * @return $this
     */
    public function setRatingValue($value);

    /**
     * Get Rating Average
     * @return string
     */
    public function getRatingAvg();

    /**
     * Set Rating Average
     * @param string $value
     * @return $this
     */
    public function setRatingAvg($value);

    /**
     * Get Rating Code
     * @return string
     */
    public function getRatingCode();

    /**
     * Set Rating Code
     * @param string $value
     * @return $this
     */
    public function setRatingCode($value);

    /**
     * Get Store Id
     * @return int
     */
    public function getStoreId();

    /**
     * Get Store Id
     * @param int $storeId
     * @return $this
     */
    public function setStoreId($storeId);

    /**
     * Set Created At
     * @param string $createdAt
     * @return $this
     */
    public function setCreatedAt($createdAt);

    /**
     * Get Created At
     * @return string
     */
    public function getCreatedAt();
}
