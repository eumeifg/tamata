<?php
namespace Ktpl\Productslider\Model\Data\HomePage;

use Ktpl\Productslider\Api\Data\HomePage\ProductSliderInterface;

class ProductSlider extends \Magento\Framework\DataObject implements ProductSliderInterface
{

    /**
     * {@inheritDoc}
     */
    public function getSliderId()
    {
        return $this->getData(self::PRODUCTSLIDER_ID);
    }

    /**
     * {@inheritDoc}
     */
    public function setSliderId($productsliderId)
    {
        return $this->setData(self::PRODUCTSLIDER_ID, $productsliderId);
    }

    /**
     * {@inheritDoc}
     */
    public function getCategoriesIds()
    {
        return $this->getData(self::CATEGORIES_IDS);
    }

    /**
     * {@inheritDoc}
     */
    public function setCategoriesIds($categoriesIds)
    {
        return $this->setData(self::CATEGORIES_IDS, $categoriesIds);
    }


    /**
     * {@inheritDoc}
     */
    public function getMediaPath()
    {
        return $this->getData(self::MEDIA_PATH);
    }

    /**
     * {@inheritDoc}
     */
    public function setMediaPath($mediaPath)
    {
        return $this->setData(self::MEDIA_PATH,$mediaPath);
    }

    /**
     * {@inheritDoc}
     */
    public function getTitle()
    {
        return $this->getData(self::TITLE);
    }

    /**
     * {@inheritDoc}
     */
    public function setTitle($title)
    {
        return $this->setData(self::TITLE, $title);
    }

    /**
     * {@inheritDoc}
     */
    public function getProducts()
    {
        return $this->getData(self::PRODUCTS);
    }

    /**
     * {@inheritDoc}
     */
    public function setProducts($sliderProducts)
    {
        return $this->setData(self::PRODUCTS, $sliderProducts);
    }

    /**
     * {@inheritDoc}
     */
    public function getCategories()
    {
        return $this->getData(self::CATEGORIES);
    }

    /**
     * {@inheritDoc}
     */
    public function setCategories($categories)
    {
        return $this->setData(self::CATEGORIES, $categories);
    }

    /**
     * {@inheritDoc}
     */
    public function getShowViewAllLink()
    {
        return $this->getData(self::SLIDER_VIEW_ALL_LINK);
    }

    /**
     * {@inheritDoc}
     */
    public function setShowViewAllLink($link)
    {
        return $this->setData(self::SLIDER_VIEW_ALL_LINK, $link);
    }

    /**
     * {@inheritDoc}
     */
    public function getBrandsVendors()
    {
        return $this->getData(self::BRANDS_AND_VENDORS);
    }

    /**
     * {@inheritDoc}
     */
    public function setBrandsVendors($categories)
    {
        return $this->setData(self::BRANDS_AND_VENDORS, $categories);
    }

    /**
     * {@inheritDoc}
     */
    public function getNewItemsBanner()
    {
        return $this->getData(self::NEW_ITEMS_BANNER);
    }

    /**
     * {@inheritDoc}
     */
    public function setNewItemsBanner($title)
    {
        return $this->setData(self::NEW_ITEMS_BANNER, $title);
    }
}
