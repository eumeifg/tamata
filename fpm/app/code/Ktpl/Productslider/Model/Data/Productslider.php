<?php


namespace Ktpl\Productslider\Model\Data;

use Ktpl\Productslider\Api\Data\ProductsliderInterface;

class Productslider extends \Magento\Framework\Api\AbstractExtensibleObject implements ProductsliderInterface
{

    /**
     * Get productslider_id
     * @return string|null
     */
    public function getSliderId()
    {
        return $this->_get(self::PRODUCTSLIDER_ID);
    }

    /**
     * Set productslider_id
     * @param string $productsliderId
     * @return \Ktpl\Productslider\Api\Data\ProductsliderInterface
     */
    public function setSliderId($productsliderId)
    {
        return $this->setData(self::PRODUCTSLIDER_ID, $productsliderId);
    }


    /**
     * Get Image Path
     * @return string
     */
    public function getMediaPath()
    {
        return $this->_get(self::MEDIA_PATH);
    }

    /**
     * Set Image Path
     * @param string $mediaPath
     * @return \Ktpl\Productslider\Api\Data\ProductsliderInterface
     */
    public function setMediaPath($mediaPath)
    {
        return $this->setData(self::MEDIA_PATH,$mediaPath);
    }

    /**
     * Get name
     * @return string|null
     */
    public function getName()
    {
        return $this->_get(self::NAME);
    }

    /**
     * Set name
     * @param string $name
     * @return \Ktpl\Productslider\Api\Data\ProductsliderInterface
     */
    public function setName($name)
    {
        return $this->setData(self::NAME, $name);
    }

    /**
     * Retrieve existing extension attributes object or create a new one.
     * @return \Ktpl\Productslider\Api\Data\ProductsliderExtensionInterface|null
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * Set an extension attributes object.
     * @param \Ktpl\Productslider\Api\Data\ProductsliderExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Ktpl\Productslider\Api\Data\ProductsliderExtensionInterface $extensionAttributes
    ) {
        return $this->_setExtensionAttributes($extensionAttributes);
    }

    /**
     * Get status
     * @return string|null
     */
    public function getStatus()
    {
        return $this->_get(self::STATUS);
    }

    /**
     * Set status
     * @param string $status
     * @return \Ktpl\Productslider\Api\Data\ProductsliderInterface
     */
    public function setStatus($status)
    {
        return $this->setData(self::STATUS, $status);
    }

    /**
     * Get title
     * @return string|null
     */
    public function getTitle()
    {
        return $this->_get(self::TITLE);
    }

    /**
     * Set title
     * @param string $title
     * @return \Ktpl\Productslider\Api\Data\ProductsliderInterface
     */
    public function setTitle($title)
    {
        return $this->setData(self::TITLE, $title);
    }

    /**
     * Get description
     * @return string|null
     */
    public function getDescription()
    {
        return $this->_get(self::DESCRIPTION);
    }

    /**
     * Set description
     * @param string $description
     * @return \Ktpl\Productslider\Api\Data\ProductsliderInterface
     */
    public function setDescription($description)
    {
        return $this->setData(self::DESCRIPTION, $description);
    }

    /**
     * Get store_ids
     * @return string|null
     */
    public function getStoreIds()
    {
        return $this->_get(self::STORE_IDS);
    }

    /**
     * Set store_ids
     * @param string $storeIds
     * @return \Ktpl\Productslider\Api\Data\ProductsliderInterface
     */
    public function setStoreIds($storeIds)
    {
        return $this->setData(self::STORE_IDS, $storeIds);
    }

    /**
     * Get customer_group_ids
     * @return string|null
     */
    public function getCustomerGroupIds()
    {
        return $this->_get(self::CUSTOMER_GROUP_IDS);
    }

    /**
     * Set customer_group_ids
     * @param string $customerGroupIds
     * @return \Ktpl\Productslider\Api\Data\ProductsliderInterface
     */
    public function setCustomerGroupIds($customerGroupIds)
    {
        return $this->setData(self::CUSTOMER_GROUP_IDS, $customerGroupIds);
    }

    /**
     * Get limit_number
     * @return string|null
     */
    public function getLimitNumber()
    {
        return $this->_get(self::LIMIT_NUMBER);
    }

    /**
     * Set limit_number
     * @param string $limitNumber
     * @return \Ktpl\Productslider\Api\Data\ProductsliderInterface
     */
    public function setLimitNumber($limitNumber)
    {
        return $this->setData(self::LIMIT_NUMBER, $limitNumber);
    }

    /**
     * Get location
     * @return string|null
     */
    public function getLocation()
    {
        return $this->_get(self::LOCATION);
    }

    /**
     * Set location
     * @param string $location
     * @return \Ktpl\Productslider\Api\Data\ProductsliderInterface
     */
    public function setLocation($location)
    {
        return $this->setData(self::LOCATION, $location);
    }

    /**
     * Get from_date
     * @return string|null
     */
    public function getFromDate()
    {
        return $this->_get(self::FROM_DATE);
    }

    /**
     * Set from_date
     * @param string $fromDate
     * @return \Ktpl\Productslider\Api\Data\ProductsliderInterface
     */
    public function setFromDate($fromDate)
    {
        return $this->setData(self::FROM_DATE, $fromDate);
    }

    /**
     * Get to_date
     * @return string|null
     */
    public function getToDate()
    {
        return $this->_get(self::TO_DATE);
    }

    /**
     * Set to_date
     * @param string $toDate
     * @return \Ktpl\Productslider\Api\Data\ProductsliderInterface
     */
    public function setToDate($toDate)
    {
        return $this->setData(self::TO_DATE, $toDate);
    }

    /**
     * Get product_type
     * @return string|null
     */
    public function getProductType()
    {
        return $this->_get(self::PRODUCT_TYPE);
    }

    /**
     * Set product_type
     * @param string $productType
     * @return \Ktpl\Productslider\Api\Data\ProductsliderInterface
     */
    public function setProductType($productType)
    {
        return $this->setData(self::PRODUCT_TYPE, $productType);
    }

    /**
     * Get categories_ids
     * @return string|null
     */
    public function getCategoriesIds()
    {
        return $this->_get(self::CATEGORIES_IDS);
    }

    /**
     * Set categories_ids
     * @param string $categoriesIds
     * @return \Ktpl\Productslider\Api\Data\ProductsliderInterface
     */
    public function setCategoriesIds($categoriesIds)
    {
        return $this->setData(self::CATEGORIES_IDS, $categoriesIds);
    }

    /**
     * Get product_ids
     * @return string|null
     */
    public function getProductIds()
    {
        return $this->_get(self::PRODUCT_IDS);
    }

    /**
     * Set product_ids
     * @param string $productIds
     * @return \Ktpl\Productslider\Api\Data\ProductsliderInterface
     */
    public function setProductIds($productIds)
    {
        return $this->setData(self::PRODUCT_IDS, $productIds);
    }

    /**
     * Get autoplay
     * @return string|null
     */
    public function getAutoplay()
    {
        return $this->_get(self::AUTOPLAY);
    }

    /**
     * Set autoplay
     * @param string $autoplay
     * @return \Ktpl\Productslider\Api\Data\ProductsliderInterface
     */
    public function setAutoplay($autoplay)
    {
        return $this->setData(self::AUTOPLAY, $autoplay);
    }

    /**
     * Get loop
     * @return string|null
     */
    public function getLoop()
    {
        return $this->_get(self::LOOP);
    }

    /**
     * Set loop
     * @param string $loop
     * @return \Ktpl\Productslider\Api\Data\ProductsliderInterface
     */
    public function setLoop($loop)
    {
        return $this->setData(self::LOOP, $loop);
    }

    /**
     * Get dots
     * @return string|null
     */
    public function getDots()
    {
        return $this->_get(self::DOTS);
    }

    /**
     * Set dots
     * @param string $dots
     * @return \Ktpl\Productslider\Api\Data\ProductsliderInterface
     */
    public function setDots($dots)
    {
        return $this->setData(self::DOTS, $dots);
    }

    /**
     * Get nav
     * @return string|null
     */
    public function getNav()
    {
        return $this->_get(self::NAV);
    }

    /**
     * Set nav
     * @param string $nav
     * @return \Ktpl\Productslider\Api\Data\ProductsliderInterface
     */
    public function setNav($nav)
    {
        return $this->setData(self::NAV, $nav);
    }

    /**
     * Get nav
     * @return string|null
     */
    public function getSliderProducts()
    {
        return $this->_get(self::SLIDER_PRODUCTS);
    }

    /**
     * Set nav
     * @param string $nav
     * @return \Ktpl\Productslider\Api\Data\ProductsliderInterface
     */
    public function setSliderProducts($sliderProducts)
    {
        return $this->setData(self::SLIDER_PRODUCTS, $sliderProducts);
    }

    /**
     * { @inheritDoc }
     */
    public function getSliderProductsNew()
    {
        return $this->_get(self::SLIDER_PRODUCTS);
    }

    /**
     * { @inheritDoc }
     */
    public function setSliderProductsNew($sliderProducts)
    {
        return $this->setData(self::SLIDER_PRODUCTS, $sliderProducts);
    }

    /**
     * Get link
     * @return string|null
     */
    public function getSliderViewAllLink()
    {
        return $this->_get(self::SLIDER_VIEW_ALL_LINK);
    }

    /**
     * Set link
     * @param string $link
     * @return \Ktpl\Productslider\Api\Data\ProductsliderInterface
     */
    public function setSliderViewAllLink($link)
    {
        return $this->setData(self::SLIDER_VIEW_ALL_LINK, $link);
    }
}
