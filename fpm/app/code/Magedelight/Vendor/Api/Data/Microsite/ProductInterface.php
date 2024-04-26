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
namespace Magedelight\Vendor\Api\Data\Microsite;

use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * @api
 * @since 100.0.2
 */
interface ProductInterface extends ExtensibleDataInterface
{
    /**#@+
     * Constants defined for keys of  data array
     */
    const SKU = 'sku';

    const NAME = 'name';

    const PRICE = 'price';

    const TIER_PRICES = 'tier_prices';

    const TYPE_ID = 'type_id';

    const MEDIA_PATH = 'media_path';

    const MEDIA_GALLERY_ENTRIES = 'media_gallery_entries';

    const VENDOR_ID = 'vendor_id';

    const VENDOR_PRICE = 'vendor_price';

    const VENDOR_SPECIAL_PRICE = 'vendor_special_price';

    const REVIEW_COUNT = 'review_count';

    const RATING_SUMMARY = 'rating_summary';

    const WISHLIST_FLAG = 'wishlist_flag';

    const PRODUCT_LABELS = 'product_labels';
    /**#@-*/

    /**
     * Product id
     *
     * @return int|null
     */
    public function getId();

    /**
     * Set product id
     *
     * @param int $id
     * @return $this
     */
    public function setId($id);

    /**
     * Product sku
     *
     * @return string
     */
    public function getSku();

    /**
     * Set product sku
     *
     * @param string $sku
     * @return $this
     */
    public function setSku($sku);

    /**
     * Product name
     *
     * @return string|null
     */
    public function getName();

    /**
     * Set product name
     *
     * @param string $name
     * @return $this
     */
    public function setName($name);

    /**
     * Product price
     *
     * @return float|null
     */
    public function getPrice();

    /**
     * Set product price
     *
     * @param float $price
     * @return $this
     */
    public function setPrice($price);

    /**
     * Product price
     *
     * @return float|NULL
     */
    public function getVendorPrice();

    /**
     * Set product price
     *
     * @param float|NULL $price
     * @return $this
     */
    public function setVendorPrice($price);

    /**
     * Get vendor Id
     *
     * @return int|NULL
     */
    public function getVendorId();

    /**
     * Set vendor Id
     *
     * @param int|NULL $vendorId
     * @return $this
     */
    public function setVendorId($vendorId);

    /**
     * Set product price
     *
     * @param float|NULL $price
     * @return $this
     */
    public function setVendorSpecialPrice($price);

    /**
     * Product price
     *
     * @return float|NULL
     */
    public function getVendorSpecialPrice();

    /**
     * Product type id
     *
     * @return string|null
     */
    public function getTypeId();

    /**
     * Set product type id
     *
     * @param string $typeId
     * @return $this
     */
    public function setTypeId($typeId);

    /**
     * Set product image path
     *
     * @param string $path
     * @return $this
     */
    public function setMediaPath($path);

    /**
     * Product image path
     *
     * @return string
     */
    public function getMediaPath();

    /**
     * Get media gallery entries
     *
     * @return \Magento\Catalog\Api\Data\ProductAttributeMediaGalleryEntryInterface[]|null
     */
    public function getMediaGalleryEntries();

    /**
     * Set media gallery entries
     *
     * @param \Magento\Catalog\Api\Data\ProductAttributeMediaGalleryEntryInterface[] $mediaGalleryEntries
     * @return $this
     */
    public function setMediaGalleryEntries(array $mediaGalleryEntries = null);

    /**
     * Gets list of product tier prices
     *
     * @return \Magento\Catalog\Api\Data\ProductTierPriceInterface[]|null
     */
    public function getTierPrices();

    /**
     * Sets list of product tier prices
     *
     * @param \Magento\Catalog\Api\Data\ProductTierPriceInterface[] $tierPrices
     * @return $this
     */
    public function setTierPrices(array $tierPrices = null);

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
    public function setReviewCount($reviewCount);

    /**
     * Get Review Count
     *
     * @return int $ratingSummary
     */
    public function getRatingSummary();

    /**
     * Get Wishlist Flag
     *
     * @return bool $wishlistFlag
     */
    public function getWishlistFlag();

    /**
     * Set Wishlist Flag
     * @param bool $wishlistFlag
     * @return $this
     */
    public function setWishlistFlag($wishlistFlag);

    /**
     * Set Review Count
     * @param int $ratingSummary
     * @return $this
     */
    public function setRatingSummary($ratingSummary);

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Magedelight\Vendor\Api\Data\Microsite\ProductExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Magedelight\Vendor\Api\Data\Microsite\ProductExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Magedelight\Vendor\Api\Data\Microsite\ProductExtensionInterface $extensionAttributes
    );
}
