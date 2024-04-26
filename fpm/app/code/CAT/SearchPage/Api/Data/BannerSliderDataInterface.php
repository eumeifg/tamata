<?php

namespace CAT\SearchPage\Api\Data;

interface BannerSliderDataInterface
{
    const BANNER_URL = 'banner_url';
    const PAGE_TYPE = 'page_type';
    const DATA_ID = 'data_id';
    const CATEGORY_TITLE = 'category_title';
    const SHOW_VIEW_ALL_LINK = 'show_view_all_link';
    const VIEW_ALL_LINK_CATEGORY_ID = 'view_all_link_category_id';
    const PRODUCTS = 'products';

    /**
     * @return string
     */
    public function getBannerUrl();
    /**
     * @param string $bannerUrl
     * @return $this
     */
    public function setBannerUrl(string $bannerUrl);

    /**
     * @return string
     */
    public function getPageType();
    /**
     * @param string $pageType
     * @return $this
     */
    public function setPageType(string $pageType);

    /**
     * @return int
     */
    public function getDataId();
    /**
     * @param int $dataId
     * @return $this
     */
    public function setDataId(int $dataId);

    /**
     * @return string
     */
    public function getCategoryTitle();
    /**
     * @param string $categoryTitle
     * @return $this
     */
    public function setCategoryTitle(string $categoryTitle);

    /**
     * @return bool
     */
    public function getShowViewAllLink();

    /**
     * @param bool $showViewAllLink
     * @return $this
     */
    public function setShowViewAllLink(bool $showViewAllLink);

    /**
     * @return int
     */
    public function getViewAllLinkId();

    /**
     * @param int $viewAllLinkId
     * @return $this
     */
    public function setViewAllLinkId(int $viewAllLinkId);

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
    public function setProducts(array $products);
}
