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
namespace Ktpl\Cms\Model\Pages\Data;

use Ktpl\Cms\Api\Data\HomePageInterface;

/**
 * Home-Page Build getter-setter.
 */
class HomePage extends \Magento\Framework\DataObject implements HomePageInterface
{

    /**
     * {@inheritdoc}
     */
    public function getBanners()
    {
        return $this->getData('banners');
    }

    /**
     * {@inheritdoc}
     */
    public function setBanners($banners)
    {
        return $this->setData('banners', $banners);
    }

    /**
     * {@inheritdoc}
     */
    public function getTopCategories()
    {
        return $this->getData('top_categories');
    }

    /**
     * {@inheritdoc}
     */
    public function setTopCategories($categories)
    {
        return $this->setData('top_categories', $categories);
    }

    public function getBannerAfterTopCategories()
    {
        return $this->getData('banner_after_top_categories');
    }

    public function setBannerAfterTopCategories($banner)
    {
        return $this->setData('banner_after_top_categories', $banner);
    }

    /**
     * {@inheritdoc}
     */
    public function getProductSliders()
    {
        return $this->getData('product_sliders');
    }

    /**
     * {@inheritdoc}
     */
    public function setProductSliders($sliders)
    {
        return $this->setData('product_sliders', $sliders);
    }
}
