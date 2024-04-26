<?php


namespace Ktpl\Productslider\Api\Data;

/**
 * @api
 */

interface ProductsliderInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{

    const AUTOPLAY = 'autoplay';
    const NAV = 'nav';
    const FROM_DATE = 'from_date';
    const LOOP = 'loop';
    const STORE_IDS = 'store_ids';
    const STATUS = 'status';
    const LIMIT_NUMBER = 'limit_number';
    const PRODUCT_TYPE = 'product_type';
    const CATEGORIES_IDS = 'categories_ids';
    const TO_DATE = 'to_date';
    const TITLE = 'title';
    const NAME = 'name';
    const DOTS = 'dots';
    const PRODUCTSLIDER_ID = 'slider_id';
    const DESCRIPTION = 'description';
    const PRODUCT_IDS = 'product_ids';
    const LOCATION = 'location';
    const CUSTOMER_GROUP_IDS = 'customer_group_ids';
    const SLIDER_PRODUCTS = 'slider_products';
    const MEDIA_PATH = 'media_path';
    const SLIDER_VIEW_ALL_LINK = 'slider_view_all_link';

    /**
     * Get slider_id
     * @return int
     */
    public function getSliderId();

    /**
     * Set slider_id
     * @param int $productsliderId
     * @return \Ktpl\Productslider\Api\Data\ProductsliderInterface
     */
    public function setSliderId($productsliderId);

    /**
     * Get Image Path
     * @return string
     */
    public function getMediaPath();

    /**
     * Set Image Path
     * @param string $mediaPath
     * @return \Ktpl\Productslider\Api\Data\ProductsliderInterface
     */
    public function setMediaPath($mediaPath);

    /**
     * Get name
     * @return string|null
     */
    public function getName();

    /**
     * Set name
     * @param string $name
     * @return \Ktpl\Productslider\Api\Data\ProductsliderInterface
     */
    public function setName($name);

    /**
     * Retrieve existing extension attributes object or create a new one.
     * @return \Ktpl\Productslider\Api\Data\ProductsliderExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     * @param \Ktpl\Productslider\Api\Data\ProductsliderExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Ktpl\Productslider\Api\Data\ProductsliderExtensionInterface $extensionAttributes
    );

    /**
     * Get status
     * @return string|null
     */
    public function getStatus();

    /**
     * Set status
     * @param string $status
     * @return \Ktpl\Productslider\Api\Data\ProductsliderInterface
     */
    public function setStatus($status);

    /**
     * Get title
     * @return string|null
     */
    public function getTitle();

    /**
     * Set title
     * @param string $title
     * @return \Ktpl\Productslider\Api\Data\ProductsliderInterface
     */
    public function setTitle($title);

    /**
     * Get description
     * @return string|null
     */
    public function getDescription();

    /**
     * Set description
     * @param string $description
     * @return \Ktpl\Productslider\Api\Data\ProductsliderInterface
     */
    public function setDescription($description);

    /**
     * Get store_ids
     * @return string|null
     */
    public function getStoreIds();

    /**
     * Set store_ids
     * @param string $storeIds
     * @return \Ktpl\Productslider\Api\Data\ProductsliderInterface
     */
    public function setStoreIds($storeIds);

    /**
     * Get customer_group_ids
     * @return string|null
     */
    public function getCustomerGroupIds();

    /**
     * Set customer_group_ids
     * @param string $customerGroupIds
     * @return \Ktpl\Productslider\Api\Data\ProductsliderInterface
     */
    public function setCustomerGroupIds($customerGroupIds);

    /**
     * Get limit_number
     * @return string|null
     */
    public function getLimitNumber();

    /**
     * Set limit_number
     * @param string $limitNumber
     * @return \Ktpl\Productslider\Api\Data\ProductsliderInterface
     */
    public function setLimitNumber($limitNumber);

    /**
     * Get location
     * @return string|null
     */
    public function getLocation();

    /**
     * Set location
     * @param string $location
     * @return \Ktpl\Productslider\Api\Data\ProductsliderInterface
     */
    public function setLocation($location);

    /**
     * Get from_date
     * @return string|null
     */
    public function getFromDate();

    /**
     * Set from_date
     * @param string $fromDate
     * @return \Ktpl\Productslider\Api\Data\ProductsliderInterface
     */
    public function setFromDate($fromDate);

    /**
     * Get to_date
     * @return string|null
     */
    public function getToDate();

    /**
     * Set to_date
     * @param string $toDate
     * @return \Ktpl\Productslider\Api\Data\ProductsliderInterface
     */
    public function setToDate($toDate);

    /**
     * Get product_type
     * @return string|null
     */
    public function getProductType();

    /**
     * Set product_type
     * @param string $productType
     * @return \Ktpl\Productslider\Api\Data\ProductsliderInterface
     */
    public function setProductType($productType);

    /**
     * Get categories_ids
     * @return string|null
     */
    public function getCategoriesIds();

    /**
     * Set categories_ids
     * @param string $categoriesIds
     * @return \Ktpl\Productslider\Api\Data\ProductsliderInterface
     */
    public function setCategoriesIds($categoriesIds);

    /**
     * Get product_ids
     * @return string|null
     */
    public function getProductIds();

    /**
     * Set product_ids
     * @param string $productIds
     * @return \Ktpl\Productslider\Api\Data\ProductsliderInterface
     */
    public function setProductIds($productIds);

    /**
     * Get autoplay
     * @return string|null
     */
    public function getAutoplay();

    /**
     * Set autoplay
     * @param string $autoplay
     * @return \Ktpl\Productslider\Api\Data\ProductsliderInterface
     */
    public function setAutoplay($autoplay);

    /**
     * Get loop
     * @return string|null
     */
    public function getLoop();

    /**
     * Set loop
     * @param string $loop
     * @return \Ktpl\Productslider\Api\Data\ProductsliderInterface
     */
    public function setLoop($loop);

    /**
     * Get dots
     * @return string|null
     */
    public function getDots();

    /**
     * Set dots
     * @param string $dots
     * @return \Ktpl\Productslider\Api\Data\ProductsliderInterface
     */
    public function setDots($dots);

    /**
     * Get nav
     * @return string|null
     */
    public function getNav();

    /**
     * Set nav
     * @param string $nav
     * @return \Ktpl\Productslider\Api\Data\ProductsliderInterface
     */
    public function setNav($nav);

    /**
     * Get Slider Products
     * @return \Magento\Catalog\Api\Data\ProductSearchResultsInterface|null
     */
    public function getSliderProducts();

    /**
     * Get Slider Products
     * @return \Ktpl\Productslider\Api\Data\ProductSearchResultsInterface|null
     */
    public function getSliderProductsNew();

    /**
     * Set Slider Products
     * @param \Magento\Catalog\Api\Data\ProductSearchResultsInterface $products
     * @return \Ktpl\Productslider\Api\Data\ProductsliderInterface
     */
    public function setSliderProductsNew($products);

    /**
     * Set Slider Products
     * @param \Magento\Catalog\Api\Data\ProductSearchResultsInterface $products
     * @return \Ktpl\Productslider\Api\Data\ProductsliderInterface
     */
    public function setSliderProducts($products);

    /**
     * Get link
     * @return string|null
     */
    public function getSliderViewAllLink();

    /**
     * Set link
     * @param string $link
     * @return \Ktpl\Productslider\Api\Data\ProductsliderInterface
     */
    public function setSliderViewAllLink($link);
}
