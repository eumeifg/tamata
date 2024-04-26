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
 * Vendor interface.
 */
interface VendorProfileInterface extends ExtensibleDataInterface
{
    /**#@+
     * Constants defined for keys of the data array. Identical to the name of the getter in snake case
     */
    const PERSONAL_INFO = 'personal_information';
    const BUSINESS_INFO = 'business_information';
    const LOGIN_INFO= 'login_info';
    const CATEGORY_DATA = 'category';
    const STATUS_INFO = 'status_info';
    const SHIPPING_INFO = 'shipping_information';
    const BANKING_INFO = 'banking_info';

    /**
     * Get vendor personal information
     *
     * @api
     * @return \Magedelight\Vendor\Api\Data\PersonalDataInterface
     */
    public function getPersonalInformation();

    /**
     * Set vendor id
     *
     * @api
     * @param \Magedelight\Vendor\Api\Data\PersonalDataInterface $personalData
     * @return $this
     */
    public function setPersonalInformation($personalData);

    /**
     * Get Business Information
     *
     * @api
     * @return \Magedelight\Vendor\Api\Data\BusinessDataInterface $businessData
     */
    public function getBusinessInformation();

    /**
     * Set Business Information
     *
     * @api
     * @param \Magedelight\Vendor\Api\Data\BusinessDataInterface $businessData
     * @return $this
     */
    public function setBusinessInformation($businessData);

    /**
     * Get Login Information
     *
     * @api
     * @return \Magedelight\Vendor\Api\Data\LoginDataInterface
     */
    public function getLoginInformation();

    /**
     * Set Login Information
     *
     * @api
     * @param \Magedelight\Vendor\Api\Data\LoginDataInterface $loginData
     * @return $this
     */
    public function setLoginInformation($loginData);

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
     * Get Status Information
     *
     * @api
     * @return \Magedelight\Vendor\Api\Data\StatusDataInterface
     */
    public function getStatusInformation();

    /**
     * Set Status Information
     *
     * @api
     * @param \Magedelight\Vendor\Api\Data\StatusDataInterface $statusData
     * @return $this
     */
    public function setStatusInformation($statusData);

    /**
     * Get Shipping Data
     *
     * @api
     * @return \Magedelight\Vendor\Api\Data\ShippingDataInterface
     */
    public function getShippingInformation();

    /**
     * Set Shipping Data
     *
     * @api
     * @param \Magedelight\Vendor\Api\Data\ShippingDataInterface $shippingData
     * @return $this
     */
    public function setShippingInformation($shippingData);

    /**
     * Get Banking Data
     *
     * @api
     * @return \Magedelight\Vendor\Api\Data\BankDataInterface
     */
    public function getBankingInformation();

    /**
     * Get Banking Data
     *
     * @api
     * @param \Magedelight\Vendor\Api\Data\BankDataInterface $bankingData
     * @return $this
     */
    public function setBankingInformation($bankingData);

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Magedelight\Vendor\Api\Data\VendorProfileExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Magedelight\Vendor\Api\Data\VendorProfileExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Magedelight\Vendor\Api\Data\VendorProfileExtensionInterface $extensionAttributes
    );
}
