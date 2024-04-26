<?php

/**
 * Magedelight
 * Copyright (C) 2016 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Mobile_Connector
 * @copyright Copyright (c) 2016 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */

namespace Magedelight\MobileInit\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * @api
 */
interface MobileInitDataInterface extends ExtensibleDataInterface
{

    /**
     * @param string $language
     * @return $this
     */
    public function setCurrentLanguage(string $language);

    /**
     * @return string.
     */
    public function getCurrentLanguage();

    /**
     * @param string $language
     * @return $this
     */
    public function setCurrentCurrency(string $currency);

    /**
     * @return string.
     */
    public function getCurrentCurrency();

    /**
     * @return string
     */
    public function getCurrentCurrencySymbol();

    /**
     * @param string $symbol
     * @return $this
     */
    public function setCurrentCurrencySymbol(string $symbol);

    /**
     * @return bool
     */
    public function getIsAllowedGuestCheckout();

    /**
     * @param bool $isAllowed
     * @return $this
     */
    public function setIsAllowedGuestCheckout(bool $isAllowed);

    /**
     * @return bool
     */
    public function getIsAllowedGuestReview();

    /**
     * @param bool $isAllowed
     * @return $this
     */
    public function setIsAllowedGuestReview(bool $isAllowed);

    /**
     * @return bool
     */
    public function getIsAllowedGuestReferral();

    /**
     * @param bool $isAllowed
     * @return $this
     */
    public function setIsAllowedGuestReferral(bool $isAllowed);

    /**
     * @param int $cartItemCount
     * @return $this
     */
    public function setCartItems(int $cartItemCount);

    /**
     * @param int $cartItemQty
     * @return $this
     */
    public function setCartItemsQuantity(int $cartItemQty);

    /**
     * @return int
     */
    public function getCartItems();

    /**
     * @return $int
     */
    public function getCartItemsQuantity();

    /**
     * Sets Config module data.
     *
     * @param \Magedelight\MobileInit\Api\Data\MobileConfigModuleInterface[] array $configModules
     * @return $this
     */
    public function setConfigModules(array $configModules);

    /**
     * Gets Config module data.
     *
     * @return \Magedelight\MobileInit\Api\Data\MobileConfigModuleInterface[] array $configModules
     */
    public function getConfigModules();

    /**
     * Sets Category data.
     *
     * @param \Magedelight\MobileInit\Api\Data\MobileCategoryDataInterface[] array $categoryData
     * @return $this
     */
    public function setCategoryItems($categoryData);

    /**
     * Gets Category data.
     *
     * @return \Magedelight\MobileInit\Api\Data\MobileCategoryDataInterface[] array $categoryData
     */
    public function getCategoryItems();

    /**
     * Sets available languages.
     *
     * @param \Magedelight\MobileInit\Api\Data\MobileSettingDataInterface[] array $availableLanguages
     * @return $this
     */
    public function setAvailableLanguages(array $availableLanguages);

    /**
     * Gets available languages.
     *
     * @return \Magedelight\MobileInit\Api\Data\MobileSettingDataInterface[] array $availableLanguages
     */
    public function getAvailableLanguages();

    /**
     * Sets Price Format.
     *
     * @param \Magedelight\MobileInit\Api\Data\MobilePriceFormatDataInterface[] array $priceFormat
     * @return $this
     */
    public function setPriceFormat(array $priceFormat);

    /**
     * Gets Price Format.
     *
     * @return \Magedelight\MobileInit\Api\Data\MobilePriceFormatDataInterface[] array $priceFormat
     */
    public function getPriceFormat();

    /**
     * Set Website Id
     * @param int $websiteId
     * @return $this
     */
    public function setWebsiteId($websiteId);
    

    /**
     * Get Website Id
     * @return int $websiteID
     */
    public function getWebsiteId();

    /**
     * Get Deal Zone Category Id
     * @return int $dealzoneCatId|null
     */
    public function getDealZoneCatId();

    /**
     * Set Deal Zone Category Id
     * @param int $dealzoneCatId
     * @return $this
     */
    public function setDealZoneCatId($dealzoneCatId);

    /**
     * Get Deal Zone Category Title
     * @return string $dealzoneCatTitle|null
     */
    public function getDealZoneCatTitle();

    /**
     * Set Deal Zone Category Id
     * @param string $dealzoneCatTitle
     * @return $this
     */
    public function setDealZoneCatTitle($dealzoneCatTitle);

    /**
     * @param string|null $mobileCategoryBanner
     * @return $this
     */
    public function setMobileCategoryBanner($mobileCategoryBanner);

    /**
    * @return string|null
    */
    public function getMobileCategoryBanner();

    /**
     * @param string|null $mobileCategoryImage
     * @return $this
     */
    public function setMobileCategoryImage($mobileCategoryImage);

    /**
    * @return string|null
    */
    public function getMobileCategoryImage();

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Magedelight\MobileInit\Api\Data\MobileInitDataExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Magedelight\MobileInit\Api\Data\MobileInitDataExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(\Magedelight\MobileInit\Api\Data\MobileInitDataExtensionInterface $extensionAttributes);
}
