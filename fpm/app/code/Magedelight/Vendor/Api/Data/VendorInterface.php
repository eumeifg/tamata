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

interface VendorInterface
{
    /**#@+
     * Constants defined for keys of the data array. Identical to the name of the getter in snake case
     */
    const ID = 'vendor_id';
    const CONFIRMATION = 'confirmation';
    const NAME = 'name';
    const EMAIL = 'email';
    const MOBILE = 'mobile';
    const LOGO = 'logo';
    const PASSWORD = 'password';
    const PASSWORD_HASH = 'password_hash';
    const ADDRESS1 = 'address1';
    const ADDRESS2 = 'address2';
    const CITY = 'city';
    const REGION_ID = 'region_id';
    const REGION = 'region';
    const COUNTRY = 'country_id';
    const PINCODE = "pincode";
    const PICKUP_ADDRESS1 = 'pickup_address1';
    const PICKUP_ADDRESS2 = 'pickup_address2';
    const PICKUP_CITY = 'pickup_city';
    const PICKUP_REGION_ID = 'pickup_region_id';
    const PICKUP_REGION = 'pickup_region';
    const PICKUP_COUNTRY = 'pickup_country_id';
    const PICKUP_PINCODE = "pickup_pincode";
    const CATEGORY = "category";
    const OTHER_MARKETPLACE_PROFILE ='other_marketplace_profile';
    const RP_TOKEN = 'rp_token';
    const RP_TOKEN_CREATED_AT = 'rp_token_created_at';
    const EMAIL_VERIFICATION_CODE = 'email_verification_code';
    const EMAIL_VERIFIED = 'email_verified';
    const SUFFIX = 'suffix';
    const TAXVAT = 'taxvat';
    const VAT = 'vat';
    const VAT_DOC = 'vat_doc';
    const WEBSITE_ID = 'website_id';
    const DEFAULT_BILLING = 'default_billing';
    const DEFAULT_SHIPPING = 'default_shipping';
    const KEY_ADDRESSES = 'addresses';
    const DISABLE_AUTO_GROUP_CHANGE = 'disable_auto_group_change';
    const BUSINESS_NAME = 'business_name';
    const BANK_ACCOUNT_NAME = 'bank_account_name';
    const BANK_ACCOUNT_NUMBER = 'bank_account_number';
    const BANK_NAME = 'bank_name';
    const IFSC = 'ifsc';
    const STATUS = 'status';
    const VACATION_FROM_DATE = 'vacation_from_date';
    const VACATION_TO_DATE = 'vacation_to_date';
    const VACATION_MESSAGE = 'vacation_message';
    const APP_LINK = 'app_link';

    /**
     * Get vendor id
     *
     * @api
     * @return int
     */
    public function getId();

    /**
     * Set vendor id
     *
     * @api
     * @param int|null $id
     * @return $this
     */
    public function setId($id);

    /**
     * Get first name
     *
     * @api
     * @return string
     */
    public function getName();

    /**
     * Set first name
     *
     * @api
     * @param string|null $firstname
     * @return $this
     */
    public function setName($firstname);

    /**
     * Get email address
     *
     * @api
     * @return string
     */
    public function getEmail();

    /**
     * Set email address
     *
     * @api
     * @param string|null $email
     * @return $this
     */
    public function setEmail($email);

    /**
     * Get mobile number
     *
     * @api
     * @return int
     */
    public function getMobile();

    /**
     * Set mobile number
     *
     * @api
     * @param int|null $mobile
     * @return $this
     */
    public function setMobile($mobile);

    /**
     * Get rp_toekn
     *
     * @api
     * @return string
     */
    public function getRpToken();

    /**
     * Set rp_toekn
     *
     * @api
     * @param string|null $token
     * @return $this
     */
    public function setRpToken($token);

    /**
     * Get email_verification_code
     *
     * @api
     * @return string
     */
    public function getEmailVerificationCode();

    /**
     * Set email_verification_code
     *
     * @api
     * @param string|null $token
     * @return $this
     */
    public function setEmailVerificationCode($token);

    /**
     * Get email_verified
     *
     * @api
     * @return boolean
     */
    public function getEmailVerified();

    /**
     * Set email_verified
     *
     * @api
     * @param boolean|null $var
     * @return $this
     */
    public function setEmailVerified($var);

    /**
     * @return mixed
     */
    public function getVat();

    /**
     * @param $vat
     * @return mixed|null
     */
    public function setVat($vat);

    /**
     * @return mixed
     */
    public function getVatDoc();

    /**
     * @param $vat_doc
     * @return mixed|null
     */
    public function setVatDoc($vat_doc);

    /**
     * Get address1
     *
     * @api
     * @return string
     */
    public function getAddress1();

    /**
     * Set address1
     *
     * @api
     * @param string|null $var
     * @return $this
     */
    public function setAddress1($var);

    /**
     * Get address2
     *
     * @api
     * @return string
     */
    public function getAddress2();

    /**
     * Set address2
     *
     * @api
     * @param string|null $var
     * @return $this
     */
    public function setAddress2($var);

    /**
     * Get country
     *
     * @api
     * @return string
     */
    public function getCountry();

    /**
     * Set country
     *
     * @api
     * @param string|null $var
     * @return $this
     */
    public function setCountry($var);

    /**
     * Get region
     *
     * @api
     * @return string
     */
    public function getRegion();

    /**
     * Set region
     *
     * @api
     * @param string|null $var
     * @return $this
     */
    public function setRegion($var);

    /**
     * Get region id
     *
     * @api
     * @return int
     */
    public function getRegionId();

    /**
     * Set region id
     *
     * @api
     * @param int|null $var
     * @return $this
     */
    public function setRegionId($var);

    /**
     * Get city
     *
     * @api
     * @return int
     */
    public function getCity();

    /**
     * Set city
     *
     * @api
     * @param int|null $var
     * @return $this
     */
    public function setCity($var);

    /**
     * Get Pincode
     *
     * @api
     * @return string
     */
    public function getPincode();

    /**
     * Set Pincode
     *
     * @api
     * @param string|null $var
     * @return $this
     */
    public function setPincode($var);

    /**
     * Get Pickup address1
     *
     * @api
     * @return string
     */
    public function getPickupAddress1();

    /**
     * Set Pickup address1
     *
     * @api
     * @param string|null $var
     * @return $this
     */
    public function setPickupAddress1($var);

    /**
     * Get Pickup address2
     *
     * @api
     * @return string
     */
    public function getPickupAddress2();

    /**
     * Set Pickup address2
     *
     * @api
     * @param string|null $var
     * @return $this
     */
    public function setPickupAddress2($var);

    /**
     * Get Pickup country
     *
     * @api
     * @return string
     */
    public function getPickupCountry();

    /**
     * Set Pickup country
     *
     * @api
     * @param string|null $var
     * @return $this
     */
    public function setPickupCountry($var);

    /**
     * Get Pickup region
     *
     * @api
     * @return string
     */
    public function getPickupRegion();

    /**
     * Set Pickup region
     *
     * @api
     * @param string|null $var
     * @return $this
     */
    public function setPickupRegion($var);

    /**
     * Get Pickup region id
     *
     * @api
     * @return int
     */
    public function getPickupRegionId();

    /**
     * Set Pickup region id
     *
     * @api
     * @param int|null $var
     * @return $this
     */
    public function setPickupRegionId($var);

    /**
     * Get Pickup city
     *
     * @api
     * @return int
     */
    public function getPickupCity();

    /**
     * Set Pickup city
     *
     * @api
     * @param int|null $var
     * @return $this
     */
    public function setPickupCity($var);

    /**
     * Get Pickup Pincode
     *
     * @api
     * @return string
     */
    public function getPickupPincode();

    /**
     * Set Pickup Pincode
     *
     * @api
     * @param string|null $var
     * @return $this
     */
    public function setPickupPincode($var);

    /**
     * Get Categories
     *
     * @api
     * @return array
     */
    public function getCategory();

    /**
     * Set
     *
     * @api
     * @param array|null $var
     * @return $this
     */
    public function setCategory($var);

    /**
     * Get product URL if sell in other Marketplace
     *
     * @api
     * @return string
     */
    public function getOtherMarketPlaceUrl();

    /**
     * Set product URL if sell in other Marketplace
     *
     * @api
     * @param string|null $var
     * @return $this
     */
    public function setOtherMarketPlaceUrl($var);

    /**
     * Get BusinessName
     *
     * @api
     * @return string
     */
    public function getBusinessName();

    /**
     * Set BusinessName
     *
     * @api
     * @param string|null $var
     * @return $this
     */
    public function setBusinessName($var);

    /**
     * Get bank account name
     *
     * @api
     * @return string
     */
    public function getBankAccountName();

    /**
     * Set bank account name
     *
     * @api
     * @param string|null $var
     * @return $this
     */
    public function setBankAccountName($var);

    /**
     * Get bank account number
     *
     * @api
     * @return int
     */
    public function getBankAccountNumber();

    /**
     * Set bank account number
     *
     * @api
     * @param int|null $var
     * @return $this
     */
    public function setBankAccountNumber($var);

    /**
     * Get getBankName
     *
     * @api
     * @return string
     */
    public function getBankName();

    /**
     * Set getBankName
     *
     * @api
     * @param string|null $var
     * @return $this
     */
    public function setBankName($var);

    /**
     * Get IFSC CODE
     *
     * @api
     * @return string
     */
    public function getIfsc();

    /**
     * Set IFSC CODE
     *
     * @api
     * @param string|null $var
     * @return $this
     */
    public function setIfsc($var);

    /**
     * Get Logo
     *
     * @api
     * @return string
     */
    public function getLogo();

    /**
     * Set Logo
     *
     * @api
     * @param string|null $var
     * @return $this
     */
    public function setLogo($var);

    /**
     * Get associate Categorie ids
     *
     * @api
     * @return array
     */
    public function getCategoryIds();

    /**
     * Get associate Delivery Zone ids
     *
     * @api
     * @return array
     * DeliveryZone module has disabled
     */
    /*public function getDeliveryZoneIds();*/

    /**
     * Check if email already registered.
     * @param string|null $email
     * @return boolean
     */
    public function isEmailExist($email);

    /**
     * Get Status
     *
     * @api
     * @return int
     */
    public function getStatus();

    /**
     * Set Status
     *
     * @api
     * @param int|null $var
     * @return $this
     */
    public function setStatus($var);

    /**
     * Get Vacation from date
     *
     * @api
     * @return string
     */
    public function getVacationFromDate();

    /**
     * Set Vacation From Date
     *
     * @api
     * @param string|null $var
     * @return $this
     */
    public function setVacationFromDate($var);

    /**
     * Get Vacation To date
     *
     * @api
     * @return string
     */
    public function getVacationToDate();

    /**
     * Set Vacation To Date
     *
     * @api
     * @param string|null $var
     * @return $this
     */
    public function setVacationToDate($var);

    /**
     * Get Vacation Message
     *
     * @api
     * @return string
     */
    public function getVacationMessage();

    /**
     * Set Vacation Message
     *
     * @api
     * @param string|null $var
     * @return $this
     */
    public function setVacationMessage($var);

    /**
     * set associate Category ids
     *
     * @api
     * @return array|null
     */
    public function setCategoryIds($var);

    /**
     * @param string|null $statusMsg
     * @return $this
     */
    public function setStatusText($statusMsg);

    /**
     * @return string|null
     */
    public function getStatusText();

    /**
     * Set Category data.
     *
     * @param \Magedelight\Vendor\Api\Data\CategoryDataInterface[]|is_null(var) $categoryData
     * @return $this
     */
    public function setCategoryItems($categoryData);

    /**
     * Get Category data.
     *
     * @return \Magedelight\Vendor\Api\Data\CategoryDataInterface[]
     */
    public function getCategoryItems();

    /**
     * @param $categoryData
     * @return void
     */
    public function addCategoryItem($categoryData);

    /**
     * Get app nalinkme
     *
     * @api
     * @return string
     */
    public function getAppLink();

    /**
     * Set app link
     *
     * @api
     * @param string|null $appLink
     * @return $this
     */
    public function setAppLink($appLink);
}
