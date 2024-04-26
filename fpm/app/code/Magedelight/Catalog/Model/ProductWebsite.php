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
namespace Magedelight\Catalog\Model;

use Magento\Framework\Model\AbstractModel;

/**
 * @method \Magedelight\Catalog\Model\ResourceModel\ProductWebsite _getResource()
 * @method \Magedelight\Catalog\Model\ResourceModel\ProductWebsite getResource()
 */
class ProductWebsite extends AbstractModel implements \Magedelight\Catalog\Api\Data\ProductWebsiteInterface
{
    const CACHE_TAG = 'md_vendor_product_website';

    /**
     * Cache tag
     *
     * @var string
     */
    protected $_cacheTag = self::CACHE_TAG;

    /**
     * Event prefix
     *
     * @var string
     */
    protected $_eventPrefix = 'md_vendor_product_website';

    /**
     * Event object
     *
     * @var string
     */
    protected $_eventObject = 'ProductWebsite';

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Magedelight\Catalog\Model\ResourceModel\ProductWebsite::class);
    }

    /**
     * Get identities
     *
     * @return array
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    /**
     * Get Product Website id
     *
     * @return array
     */
    public function getProductWebsiteId()
    {
        return $this->getData(\Magedelight\Catalog\Api\Data\ProductWebsiteInterface::PRODUCTWEBSITE_ID);
    }

    /**
     * set Product Website id
     *
     * @param int $ProductWebsiteId
     * @return \Magedelight\Catalog\Api\Data\ProductWebsiteInterface
     */
    public function setProductWebsiteId($ProductWebsiteId)
    {
        return $this->setData(
            \Magedelight\Catalog\Api\Data\ProductWebsiteInterface::PRODUCTWEBSITE_ID,
            $ProductWebsiteId
        );
    }

    /**
     * set Product Website Data
     *
     * @param mixed $productWebsiteVendorProductId
     * @return \Magedelight\Catalog\Api\Data\ProductWebsiteInterface
     */
    public function setProductWebsiteVendorProductId($productWebsiteVendorProductId)
    {
        return $this->setData(
            \Magedelight\Catalog\Api\Data\ProductWebsiteInterface::PRODUCT_WEBSITE_VENDORPRODUCT_ID,
            $productWebsiteVendorProductId
        );
    }

    /**
     * get Product Website Data
     *
     * @return string
     */
    public function getProductWebsiteVendorProductId()
    {
        return $this->getData(\Magedelight\Catalog\Api\Data\ProductWebsiteInterface::PRODUCT_WEBSITE_VENDORPRODUCT_ID);
    }

    /**
     * set Vendor ID
     *
     * @param mixed $vendorId
     * @return \Magedelight\Catalog\Api\Data\ProductWebsiteInterface
     */
    public function setVendorId($vendorId)
    {
        return $this->setData(\Magedelight\Catalog\Api\Data\ProductWebsiteInterface::VENDOR_ID, $vendorId);
    }

    /**
     * get Vendor ID
     *
     * @return string
     */
    public function getVendorId()
    {
        return $this->getData(\Magedelight\Catalog\Api\Data\ProductWebsiteInterface::VENDOR_ID);
    }

    /**
     * set Price
     *
     * @param mixed $price
     * @return \Magedelight\Catalog\Api\Data\ProductWebsiteInterface
     */
    public function setPrice($price)
    {
        return $this->setData(\Magedelight\Catalog\Api\Data\ProductWebsiteInterface::PRICE, $price);
    }

    /**
     * get Price
     *
     * @return string
     */
    public function getPrice()
    {
        return $this->getData(\Magedelight\Catalog\Api\Data\ProductWebsiteInterface::PRICE);
    }

    /**
     * set Special Price
     *
     * @param mixed $specialPrice
     * @return \Magedelight\Catalog\Api\Data\ProductWebsiteInterface
     */
    public function setSpecialPrice($specialPrice)
    {
        return $this->setData(\Magedelight\Catalog\Api\Data\ProductWebsiteInterface::SPECIAL_PRICE, $specialPrice);
    }

    /**
     * get Special Price
     *
     * @return string
     */
    public function getSpecialPrice()
    {
        return $this->getData(\Magedelight\Catalog\Api\Data\ProductWebsiteInterface::SPECIAL_PRICE);
    }

    /**
     * set Special From
     *
     * @param mixed $specialFromDate
     * @return \Magedelight\Catalog\Api\Data\ProductWebsiteInterface
     */
    public function setSpecialFromDate($specialFromDate)
    {
        return $this->setData(
            \Magedelight\Catalog\Api\Data\ProductWebsiteInterface::SPECIAL_FROM_DATE,
            $specialFromDate
        );
    }

    /**
     * get Special From
     *
     * @return string
     */
    public function getSpecialFromDate()
    {
        return $this->getData(\Magedelight\Catalog\Api\Data\ProductWebsiteInterface::SPECIAL_FROM_DATE);
    }

    /**
     * set Special To
     *
     * @param mixed $specialToDate
     * @return \Magedelight\Catalog\Api\Data\ProductWebsiteInterface
     */
    public function setSpecialToDate($specialToDate)
    {
        return $this->setData(\Magedelight\Catalog\Api\Data\ProductWebsiteInterface::SPECIAL_TO_DATE, $specialToDate);
    }

    /**
     * get Special To
     *
     * @return string
     */
    public function getSpecialToDate()
    {
        return $this->getData(\Magedelight\Catalog\Api\Data\ProductWebsiteInterface::SPECIAL_TO_DATE);
    }

    /**
     * set Condition
     *
     * @param mixed $condition
     * @return \Magedelight\Catalog\Api\Data\ProductWebsiteInterface
     */
    public function setCondition($condition)
    {
        return $this->setData(\Magedelight\Catalog\Api\Data\ProductWebsiteInterface::CONDITION, $condition);
    }

    /**
     * get Condition
     *
     * @return string
     */
    public function getCondition()
    {
        return $this->getData(\Magedelight\Catalog\Api\Data\ProductWebsiteInterface::CONDITION);
    }

    /**
     * set Warranty Type
     *
     * @param mixed $warrantyType
     * @return \Magedelight\Catalog\Api\Data\ProductWebsiteInterface
     */
    public function setWarrantyType($warrantyType)
    {
        return $this->setData(\Magedelight\Catalog\Api\Data\ProductWebsiteInterface::WARRANTY_TYPE, $warrantyType);
    }

    /**
     * get Warranty Type
     *
     * @return string
     */
    public function getWarrantyType()
    {
        return $this->getData(\Magedelight\Catalog\Api\Data\ProductWebsiteInterface::WARRANTY_TYPE);
    }

    /**
     * set Reorder Level
     *
     * @param mixed $reorderLevel
     * @return \Magedelight\Catalog\Api\Data\ProductWebsiteInterface
     */
    public function setReorderLevel($reorderLevel)
    {
        return $this->setData(\Magedelight\Catalog\Api\Data\ProductWebsiteInterface::REORDER_LEVEL, $reorderLevel);
    }

    /**
     * get Reorder Level
     *
     * @return string
     */
    public function getReorderLevel()
    {
        return $this->getData(\Magedelight\Catalog\Api\Data\ProductWebsiteInterface::REORDER_LEVEL);
    }

    /**
     * set Status
     *
     * @param mixed $status
     * @return \Magedelight\Catalog\Api\Data\ProductWebsiteInterface
     */
    public function setStatus($status)
    {
        return $this->setData(\Magedelight\Catalog\Api\Data\ProductWebsiteInterface::STATUS, $status);
    }

    /**
     * get Status
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->getData(\Magedelight\Catalog\Api\Data\ProductWebsiteInterface::STATUS);
    }

    /**
     * set Website ID
     *
     * @param mixed $websiteId
     * @return \Magedelight\Catalog\Api\Data\ProductWebsiteInterface
     */
    public function setWebsiteId($websiteId)
    {
        return $this->setData(\Magedelight\Catalog\Api\Data\ProductWebsiteInterface::WEBSITE_ID, $websiteId);
    }

    /**
     * get Website ID
     *
     * @return string
     */
    public function getWebsiteId()
    {
        return $this->getData(\Magedelight\Catalog\Api\Data\ProductWebsiteInterface::WEBSITE_ID);
    }
}
