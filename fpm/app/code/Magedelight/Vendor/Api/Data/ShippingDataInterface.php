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

use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * Vendor Shipping Data interface.
 * @api
 */
interface ShippingDataInterface extends ExtensibleDataInterface
{

    /**
     * Constants defined for keys of the data array. Identical to the name of the getter in snake case
     */
    const ID = 'vendor_id';
    const PICKUP_ADDRESS1 = 'pickup_address1';
    const PICKUP_ADDRESS2 = 'pickup_address2';
    const PICKUP_CITY = 'pickup_city';
    const PICKUP_REGION_ID = 'pickup_region_id';
    const PICKUP_REGION = 'pickup_region';
    const PICKUP_COUNTRY = 'pickup_country_id';
    const PICKUP_PINCODE = "pickup_pincode";

    /**
     * Get vendor id
     * @return int
     */
    public function getId();

    /**
     * Set vendor id
     * @param int|null $id
     * @return $this
     */
    public function setId($id);

    /**
     * Get Pickup address1
     * @return string
     */
    public function getPickupAddress1();

    /**
     * Set Pickup address1
     * @param string|null $var
     * @return $this
     */
    public function setPickupAddress1($var);

    /**
     * Get Pickup address2
     * @return string
     */
    public function getPickupAddress2();

    /**
     * Set Pickup address2
     * @param string|null $var
     * @return $this
     */
    public function setPickupAddress2($var);

    /**
     * Get Pickup country
     * @return string
     */
    public function getPickupCountry();

    /**
     * Set Pickup country
     * @param string|null $var
     * @return $this
     */
    public function setPickupCountry($var);

    /**
     * Get Pickup region
     * @return string
     */
    public function getPickupRegion();

    /**
     * Set Pickup region
     * @param string|null $var
     * @return $this
     */
    public function setPickupRegion($var);

    /**
     * Get Pickup region id
     * @return int
     */
    public function getPickupRegionId();

    /**
     * Set Pickup region id
     * @param int|null $var
     * @return $this
     */
    public function setPickupRegionId($var);

    /**
     * Get Pickup city
     * @return string
     */
    public function getPickupCity();

    /**
     * Set Pickup city
     * @param string|null $var
     * @return $this
     */
    public function setPickupCity($var);

    /**
     * Get Pickup Pincode
     * @return string
     */
    public function getPickupPincode();

    /**
     * Set Pickup Pincode
     * @param string|null $var
     * @return $this
     */
    public function setPickupPincode($var);

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Magedelight\Vendor\Api\Data\ShippingDataExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Magedelight\Vendor\Api\Data\ShippingDataExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Magedelight\Vendor\Api\Data\ShippingDataExtensionInterface $extensionAttributes
    );
}
