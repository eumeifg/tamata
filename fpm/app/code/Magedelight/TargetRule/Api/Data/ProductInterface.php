<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_TargetRule
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\TargetRule\Api\Data;

/**
 * @api
 * @since 100.0.2
 */
interface ProductInterface
{
    /**#@+
     * Constants defined for keys of  data array
     */
    const NAME = 'name';

    const PRICE = 'price';

    const TYPE_ID = 'type_id';

    const MEDIA_GALLERY = 'media_gallery';

    const VENDOR_ID = 'vendor_id';

    const VENDOR_PRICE = 'vendor_price';

    const VENDOR_SPECIAL_PRICE = 'vendor_special_price';

    const REVIEW_COUNT = 'review_count';

    const RATING_SUMMARY = 'rating_summary';

    const PRODUCT_LABELS = 'product_labels';

    const WISHLIST_FLAG = 'wishlist_flag';

    const ONLY_X_LEFT = 'only_x_left';
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
     * Product image url
     *
     * @return string|null
     */
    public function getImageUrl();

    /**
     * Set product image url
     *
     * @param string $imageUrl
     * @return $this
     */
    public function setImageUrl($imageUrl);

    /**
     * Retrieve current product label.
     *
     * @return \Ktpl\ProductLabel\Api\Data\ProductLabelInterface
     */
    public function getProductLabels();

    /**
     * Retrieve current product label.
     * @param \Ktpl\ProductLabel\Api\Data\ProductLabelInterface[] $productLabels
     * @return $this
     */
    public function setProductLabels($productLabels);

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
     * Set Review Count
     * @param int $ratingSummary
     * @return $this
     */
    public function setRatingSummary($ratingSummary);

    /**
     * Get Wishlist Flag
     *
     * @return boolean
     */
    public function getWishlistFlag();

    /**
     * Set Wishlist Flag
     * @param boolean $wishlistFlag
     * @return $this
     */
    public function setWishlistFlag($wishlistFlag);



    /**
     * Product onlyxleft
     *
     * @return string|null
     */
    public function getOnlyXLeft();

    /**
     * Set product onlyxleft
     *
     * @param string $onlyxleft
     * @return $this
     */
    public function setOnlyXLeft($onlyxleft);
}
