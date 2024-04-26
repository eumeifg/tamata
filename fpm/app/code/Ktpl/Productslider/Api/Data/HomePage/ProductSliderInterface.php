<?php
namespace Ktpl\Productslider\Api\Data\HomePage;

/**
 * @api
 */
interface ProductSliderInterface
{

    const TITLE = 'title';
    const CATEGORIES_IDS = 'categories_ids';
    const PRODUCTSLIDER_ID = 'slider_id';
    const PRODUCTS = 'products';
    const CATEGORIES = 'categories';
    const MEDIA_PATH = 'media_path';
    const SLIDER_VIEW_ALL_LINK = 'slider_view_all_link';
    const BRANDS_AND_VENDORS = 'brands_abd_vendors';
    const NEW_ITEMS_BANNER = 'new_items_banner';

    /**
     * Get slider_id
     * @return int
     */
    public function getSliderId();

    /**
     * Set slider_id
     * @param int $productsliderId
     * @return $this
     */
    public function setSliderId($productsliderId);

    /**
     * Get categories_ids
     * @return string|null
     */
    public function getCategoriesIds();

    /**
     * Set categories_ids
     * @param string $categoriesIds
     * @return $this
     */
    public function setCategoriesIds($categoriesIds);

    /**
     * Get Image Path
     * @return string
     */
    public function getMediaPath();

    /**
     * Set Image Path
     * @param string $mediaPath
     * @return $this
     */
    public function setMediaPath($mediaPath);

    /**
     * Get title
     * @return string|null
     */
    public function getTitle();

    /**
     * Set title
     * @param string $title
     * @return $this
     */
    public function setTitle($title);

    /**
     * Get Slider Products
     * @return \Ktpl\Productslider\Api\Data\ProductInterface[]|null
     */
    public function getProducts();

    /**
     * Set Slider Products
     * @param \Ktpl\Productslider\Api\Data\ProductInterface[] $products
     * @return $this
     */
    public function setProducts($products);

    /**
     * Get Slider Categories
     * @return \Ktpl\Productslider\Api\Data\HomePage\SliderCategoryInterface[]|null
     */
    public function getCategories();

    /**
     * Set Slider Categories
     * @param \Ktpl\Productslider\Api\Data\HomePage\SliderCategoryInterface[] $categories
     * @return $this
     */
    public function setCategories($categories);

    /**
     * Get view all link flag
     * @return boolean
     */
    public function getShowViewAllLink();

    /**
     * Set view all link flag
     * @param boolean $link
     * @return $this
     */
    public function setShowViewAllLink($link);

    /**
     * Get Brands
     * @return \Ktpl\Productslider\Api\Data\HomePage\BrandsInterface[]|null
     */
    public function getBrandsVendors();

    /**
     * Set Brands
     * @param \Ktpl\Productslider\Api\Data\HomePage\BrandsInterface[] $brands
     * @return $this
     */
    public function setBrandsVendors($brands);

    /**
     * Get new items banner
     * @return \Ktpl\Productslider\Api\Data\HomePage\NewItemsBannerInterface[]|null
     */
    public function getNewItemsBanner();

    /**
     * Set new items banner
     * @param \Ktpl\Productslider\Api\Data\HomePage\NewItemsBannerInterface[] $banner
     * @return $this
     */
    public function setNewItemsBanner($banner);
}
