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
 * Vendor Personal interface.
 * @api
 */
interface PersonalDataInterface extends ExtensibleDataInterface
{

    /**
     * Constants defined for keys of the data array. Identical to the name of the getter in snake case
     */
    const ID = 'vendor_id';
    const NAME = 'name';
    const EMAIL = 'email';
    const ADDRESS1 = 'address1';
    const ADDRESS2 = 'address2';
    const CITY = 'city';
    const REGION_ID = 'region_id';
    const REGION = 'region';
    const COUNTRY = 'country_id';
    const PINCODE = "pincode";

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
     * Get first name
     * @return string
     */
    public function getName();

    /**
     * Set first name
     * @param string|null $name
     * @return $this
     */
    public function setName($name);

    /**
     * Get email address
     * @return string
     */
    public function getEmail();

    /**
     * Set email address
     * @param string|null $email
     * @return $this
     */
    public function setEmail($email);

    /**
     * Get address1
     * @return string
     */
    public function getAddress1();

    /**
     * Set address1
     * @param string|null $var
     * @return $this
     */
    public function setAddress1($var);

    /**
     * Get address2
     * @return string
     */
    public function getAddress2();

    /**
     * Set address2
     * @param string|null $var
     * @return $this
     */
    public function setAddress2($var);

    /**
     * Get country
     * @return string
     */
    public function getCountryId();

    /**
     * Set country
     * @param string|null $var
     * @return $this
     */
    public function setCountryId($var);

    /**
     * Get region
     * @return string
     */
    public function getRegion();

    /**
     * Set region
     * @param string|null $var
     * @return $this
     */
    public function setRegion($var);

    /**
     * Get region id
     * @return int
     */
    public function getRegionId();

    /**
     * Set region id
     * @param int|null $var
     * @return $this
     */
    public function setRegionId($var);

    /**
     * Get city
     * @return string
     */
    public function getCity();

    /**
     * Set city
     * @param string|null $var
     * @return $this
     */
    public function setCity($var);

    /**
     * Get Pincode
     * @return string
     */
    public function getPincode();

    /**
     * Set Pincode
     * @param string|null $var
     * @return $this
     */
    public function setPincode($var);

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Magedelight\Vendor\Api\Data\PersonalDataExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Magedelight\Vendor\Api\Data\PersonalDataExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Magedelight\Vendor\Api\Data\PersonalDataExtensionInterface $extensionAttributes
    );
}
