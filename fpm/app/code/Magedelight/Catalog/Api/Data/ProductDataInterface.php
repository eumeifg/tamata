<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Catalog
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Catalog\Api\Data;

interface ProductDataInterface
{
    /**
     * Get Additional Information
     *
     * @return \Magedelight\Catalog\Api\Data\ProductAdditionalAttributeDataInterface[]
     */
    public function getAdditionalInformation();

    /**
     * Set Additional Information
     * @param \Magedelight\Catalog\Api\Data\ProductAdditionalAttributeDataInterface[] array $additionalInformation
     * @return $this
     */
    public function setAdditionalInformation($additionalInformation);

    /**
     * Get Media Path
     *
     * @return string
     */
    public function getMediaPath();

    /**
     * Set Media Path
     * @param string $mediaPath
     * @return $this
     */
    public function setMediaPath(string $mediaPath);

    /**
     * Get Vendor Logo Path
     *
     * @return string
     */
    public function getVendorLogoPath();

    /**
     * Set Media Path
     * @param string $vendorLogoPath
     * @return $this
     */
    public function setVendorLogoPath(string $vendorLogoPath);

    /**
     * Get Default Vendor Data
     *
     * @return \Magedelight\Catalog\Api\Data\ProductVendorDataInterface[] array $defaultVendorData
     */
    public function getDefaultVendorData();

    /**
     * Set Default Vendor Data
     * @param \Magedelight\Catalog\Api\Data\ProductVendorDataInterface[] array $defaultVendorData
     * @return $this
     */
    public function setDefaultVendorData(array $defaultVendorData);

    /**
     * Get Other Vendor Data
     *
     * @return \Magedelight\Catalog\Api\Data\ProductVendorDataInterface[] array $defaultVendorData
     */
    public function getOtherVendorData();

    /**
     * Set Other Vendor Data
     * @param \Magedelight\Catalog\Api\Data\ProductVendorDataInterface[] array $defaultVendorData
     * @return $this
     */
    public function setOtherVendorData(array $defaultVendorData);

    /**
     * Get Wishlisted Flag
     *
     * @return bool $wishlistFlag
     */
    public function getWishlistFlag();

    /**
     * Set Wishlisted Flag
     * @param bool $wishlistFlag
     * @return $this
     */
    public function setWishlistFlag(bool $wishlistFlag);

    /**
     * Get Review Count
     *
     * @return int $reviewCount
     */
    public function getReviewCount();

    /**
     * Set Review Count
     * @param int $reviewCount
     * @return $this
     */
    public function setReviewCount(int $reviewCount);

     /**
      * Get Review Count
      *
      * @return int $ratingSummary
      */
    public function getRatingSummary();

    /**
     * Set Review Count
     * @param int $ratingSummary
     * @return $this
     */
    public function setRatingSummary(int $ratingSummary);


    /**
     * Get Related Product for Rule - only for enterprise edition
     *
     * @return \Magento\Catalog\Api\Data\ProductInterface[]
     */
    public function getRuleRelatedProducts();


    /**
     * Set Related Product for Rule - only for enterprise edition
     *
     * @param \Magento\Catalog\Api\Data\ProductInterface[] $relatedProducts
     * @return $this
     */
    public function setRuleRelatedProducts($relatedProducts);
}
