<?php
/**
 * KrishTechnolabs
 *
 * PHP version 7
 *
 * @category  KrishTechnolabs
 * @package   Ktpl_Cms
 * @copyright 2019 (c) KrishTechnolabs (https://www.krishtechnolabs.com/)
 * @license   https://www.krishtechnolabs.com/LICENSE.txt Krish License
 * @link      https://www.krishtechnolabs.com/
 */
namespace Ktpl\Cms\Api\Data;

interface HomePageInterface
{

    /**
     * Get banners data
     *
     * @api
     * @return \Ktpl\BannerManagement\Api\Data\SliderInterface[]
     */
    public function getBanners();

    /**
     * Set banners data
     *
     * @api
     * @param \Ktpl\BannerManagement\Api\Data\BannerInterface[] $banners
     * @return $this
     */
    public function setBanners($banners);

    /**
     * Get top categories data
     *
     * @api
     * @return \Ktpl\TopCategory\Api\Data\TopCategoryInterface[]
     */
    public function getTopCategories();

    /**
     * Set top categories data
     *
     * @api
     * @param \Ktpl\TopCategory\Api\Data\TopCategoryInterface[] $categories
     * @return $this
     */
    public function setTopCategories($categories);

    /**
     * Get the banner for home page after the top category
     *
     * @api
     * @return \MDC\MobileBanner\Api\Data\Homepage\AfterTopCategory\BannerInterface[]
     */
    public function getBannerAfterTopCategories();

    /**
     * @param \MDC\MobileBanner\Api\Data\Homepage\AfterTopCategory\BannerInterface[] $banner
     * @return $this
     */
    public function setBannerAfterTopCategories($banner);

    /**
     * Get product sliders data
     *
     * @api
     * @return \Ktpl\Productslider\Api\Data\HomePage\ProductSliderInterface[]
     */
    public function getProductSliders();

    /**
     * Set product sliders data
     *
     * @api
     * @param \Ktpl\Productslider\Api\Data\HomePage\ProductSliderInterface[] $sliders
     * @return $this
     */
    public function setProductSliders($sliders);
}
