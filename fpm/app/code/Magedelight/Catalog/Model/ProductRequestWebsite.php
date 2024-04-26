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

use Magedelight\Catalog\Api\Data\ProductRequestWebsiteInterface;

/**
 * @method \Magedelight\Catalog\Model\ResourceModel\ProductWebsite _getResource()
 * @method \Magedelight\Catalog\Model\ResourceModel\ProductWebsite getResource()
 */
class ProductRequestWebsite extends \Magento\Framework\Model\AbstractModel implements ProductRequestWebsiteInterface
{
    const CACHE_TAG = 'md_vendor_product_request_website';

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
    protected $_eventPrefix = 'md_vendor_product_request_website';

    /**
     * Event object
     *
     * @var string
     */
    protected $_eventObject = 'product_request_website';

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Magedelight\Catalog\Model\ResourceModel\ProductRequestWebsite::class);
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
     * Get Product Request id
     *
     * @return array
     */
    public function getProductRequestId()
    {
        return $this->getData(ProductRequestWebsiteInterface::PRODUCT_REQUEST_ID);
    }

    /**
     * set Product Request id
     *
     * @param int $productRequestId
     * @return ProductRequestWebsiteInterface
     */
    public function setProductRequestId($productRequestId)
    {
        return $this->setData(
            ProductRequestWebsiteInterface::PRODUCT_REQUEST_ID,
            $productRequestId
        );
    }

    /**
     * set Price
     *
     * @param mixed $price
     * @return ProductRequestWebsiteInterface
     */
    public function setPrice($price)
    {
        return $this->setData(ProductRequestWebsiteInterface::PRICE, $price);
    }

    /**
     * get Price
     *
     * @return string
     */
    public function getPrice()
    {
        return $this->getData(ProductRequestWebsiteInterface::PRICE);
    }

    /**
     * set Special Price
     *
     * @param mixed $specialPrice
     * @return ProductRequestWebsiteInterface
     */
    public function setSpecialPrice($specialPrice)
    {
        return $this->setData(
            ProductRequestWebsiteInterface::SPECIAL_PRICE,
            $specialPrice
        );
    }

    /**
     * get Special Price
     *
     * @return string
     */
    public function getSpecialPrice()
    {
        return $this->getData(ProductRequestWebsiteInterface::SPECIAL_PRICE);
    }

    /**
     * set Special From
     *
     * @param mixed $specialFromDate
     * @return ProductRequestWebsiteInterface
     */
    public function setSpecialFromDate($specialFromDate)
    {
        return $this->setData(
            ProductRequestWebsiteInterface::SPECIAL_FROM_DATE,
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
        return $this->getData(ProductRequestWebsiteInterface::SPECIAL_FROM_DATE);
    }

    /**
     * set Special To
     *
     * @param mixed $specialToDate
     * @return ProductRequestWebsiteInterface
     */
    public function setSpecialToDate($specialToDate)
    {
        return $this->setData(
            ProductRequestWebsiteInterface::SPECIAL_TO_DATE,
            $specialToDate
        );
    }

    /**
     * get Special To
     *
     * @return string
     */
    public function getSpecialToDate()
    {
        return $this->getData(ProductRequestWebsiteInterface::SPECIAL_TO_DATE);
    }

    /**
     * set Condition
     *
     * @param mixed $condition
     * @return ProductRequestWebsiteInterface
     */
    public function setCondition($condition)
    {
        return $this->setData(ProductRequestWebsiteInterface::CONDITION, $condition);
    }

    /**
     * get Condition
     *
     * @return string
     */
    public function getCondition()
    {
        return $this->getData(ProductRequestWebsiteInterface::CONDITION);
    }

    /**
     * set Warranty Type
     *
     * @param mixed $warrantyType
     * @return ProductRequestWebsiteInterface
     */
    public function setWarrantyType($warrantyType)
    {
        return $this->setData(
            ProductRequestWebsiteInterface::WARRANTY_TYPE,
            $warrantyType
        );
    }

    /**
     * get Warranty Type
     *
     * @return string
     */
    public function getWarrantyType()
    {
        return $this->getData(ProductRequestWebsiteInterface::WARRANTY_TYPE);
    }

    /**
     * set Reorder Level
     *
     * @param mixed $reorderLevel
     * @return ProductRequestWebsiteInterface
     */
    public function setReorderLevel($reorderLevel)
    {
        return $this->setData(
            ProductRequestWebsiteInterface::REORDER_LEVEL,
            $reorderLevel
        );
    }

    /**
     * get Reorder Level
     *
     * @return string
     */
    public function getReorderLevel()
    {
        return $this->getData(ProductRequestWebsiteInterface::REORDER_LEVEL);
    }

    /**
     * set Website ID
     *
     * @param mixed $websiteId
     * @return ProductRequestWebsiteInterface
     */
    public function setWebsiteId($websiteId)
    {
        return $this->setData(ProductRequestWebsiteInterface::WEBSITE_ID, $websiteId);
    }

    /**
     * get Website ID
     *
     * @return string
     */
    public function getWebsiteId()
    {
        return $this->getData(ProductRequestWebsiteInterface::WEBSITE_ID);
    }
}
