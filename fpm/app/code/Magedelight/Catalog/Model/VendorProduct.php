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

use Magedelight\Catalog\Api\Data\VendorProductInterface;
use Magento\Framework\Model\AbstractExtensibleModel;

/**
 * @method \Magedelight\Catalog\Model\ResourceModel\VendorProduct _getResource()
 * @method \Magedelight\Catalog\Model\ResourceModel\VendorProduct getResource()
 */
class VendorProduct extends AbstractExtensibleModel implements VendorProductInterface
{
    const STATUS_PARAM_NAME = 'status';

    const STATUS_UNLISTED = 0;

    const STATUS_LISTED = 1;

    const CONDITION_USED = 0;

    const CONDITION_NEW = 1;

    const CONDITION_RENTAL = 2;

    const WARRANTY_MANUFACTURER = 1;

    const WARRANTY_SELLER = 2;

    const CURRENT_PRODUCT_ID = 'current_product_id';

    const CACHE_TAG = 'md_vendor_product';

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
    protected $_eventPrefix = 'md_vendor_product';

    /**
     * Event object
     *
     * @var string
     */
    protected $_eventObject = 'VendorProduct';

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Magedelight\Catalog\Model\ResourceModel\VendorProduct::class);
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
     * Get Product id
     *
     * @return array
     */
    public function getVendorProductId()
    {
        return $this->getData(VendorProductInterface::VENDORPRODUCT_ID);
    }

    /**
     * set Product id
     *
     * @param int $VendorProductId
     * @return VendorProductInterface
     */
    public function setVendorProductId($VendorProductId)
    {
        return $this->setData(VendorProductInterface::VENDORPRODUCT_ID, $VendorProductId);
    }

    /**
     * set Product Website Data
     *
     * @param mixed $productWebsiteProductWebsiteId
     * @return VendorProductInterface
     */
    public function setProductWebsiteProductWebsiteId($productWebsiteProductWebsiteId)
    {
        return $this->setData(
            VendorProductInterface::PRODUCT_WEBSITE_PRODUCTWEBSITE_ID,
            $productWebsiteProductWebsiteId
        );
    }

    /**
     * get Product Website Data
     *
     * @return string
     */
    public function getProductWebsiteProductWebsiteId()
    {
        return $this->getData(VendorProductInterface::PRODUCT_WEBSITE_PRODUCTWEBSITE_ID);
    }

    /**
     * set Product Store Data
     *
     * @param mixed $productStoreProductStoreId
     * @return VendorProductInterface
     */
    public function setProductStoreProductStoreId($productStoreProductStoreId)
    {
        return $this->setData(VendorProductInterface::PRODUCT_STORE_PRODUCTSTORE_ID, $productStoreProductStoreId);
    }

    /**
     * get Product Store Data
     *
     * @return string
     */
    public function getProductStoreProductStoreId()
    {
        return $this->getData(VendorProductInterface::PRODUCT_STORE_PRODUCTSTORE_ID);
    }

    /**
     * set Marketplace Product Id
     *
     * @param mixed $marketplaceProductId
     * @return VendorProductInterface
     */
    public function setMarketplaceProductId($marketplaceProductId)
    {
        return $this->setData(VendorProductInterface::MARKETPLACE_PRODUCT_ID, $marketplaceProductId);
    }

    /**
     * get Marketplace Product Id
     *
     * @return string
     */
    public function getMarketplaceProductId()
    {
        return $this->getData(VendorProductInterface::MARKETPLACE_PRODUCT_ID);
    }

    /**
     * set Parent ID
     *
     * @param mixed $parentId
     * @return VendorProductInterface
     */
    public function setParentId($parentId)
    {
        return $this->setData(VendorProductInterface::PARENT_ID, $parentId);
    }

    /**
     * get Parent ID
     *
     * @return string
     */
    public function getParentId()
    {
        return $this->getData(VendorProductInterface::PARENT_ID);
    }

    /**
     * set Type
     *
     * @param mixed $typeId
     * @return VendorProductInterface
     */
    public function setTypeId($typeId)
    {
        return $this->setData(VendorProductInterface::TYPE_ID, $typeId);
    }

    /**
     * get Type
     *
     * @return string
     */
    public function getTypeId()
    {
        return $this->getData(VendorProductInterface::TYPE_ID);
    }

    /**
     * set Vendor Id
     *
     * @param mixed $vendorId
     * @return VendorProductInterface
     */
    public function setVendorId($vendorId)
    {
        return $this->setData(VendorProductInterface::VENDOR_ID, $vendorId);
    }

    /**
     * get Vendor Id
     *
     * @return string
     */
    public function getVendorId()
    {
        return $this->getData(VendorProductInterface::VENDOR_ID);
    }

    /**
     * set Is Deleted
     *
     * @param mixed $isDeleted
     * @return VendorProductInterface
     */
    public function setIsDeleted($isDeleted)
    {
        return $this->setData(VendorProductInterface::IS_DELETED, $isDeleted);
    }

    /**
     * get Is Deleted
     *
     * @return string
     */
    public function getIsDeleted()
    {
        return $this->getData(VendorProductInterface::IS_DELETED);
    }

    /**
     * set Vendor SKU
     *
     * @param mixed $vendorSku
     * @return VendorProductInterface
     */
    public function setVendorSku($vendorSku)
    {
        return $this->setData(VendorProductInterface::VENDOR_SKU, $vendorSku);
    }

    /**
     * get Vendor SKU
     *
     * @return string
     */
    public function getVendorSku()
    {
        return $this->getData(VendorProductInterface::VENDOR_SKU);
    }

    /**
     * set Quantity
     *
     * @param mixed $qty
     * @return VendorProductInterface
     */
    public function setQty($qty)
    {
        return $this->setData(VendorProductInterface::QTY, $qty);
    }

    /**
     * get Quantity
     *
     * @return string
     */
    public function getQty()
    {
        return $this->getData(VendorProductInterface::QTY);
    }

    /**
     * set Approve Date
     *
     * @param mixed $approvedAt
     * @return VendorProductInterface
     */
    public function setApprovedAt($approvedAt)
    {
        return $this->setData(VendorProductInterface::APPROVED_AT, $approvedAt);
    }

    /**
     * get Approve Date
     *
     * @return string
     */
    public function getApprovedAt()
    {
        return $this->getData(VendorProductInterface::APPROVED_AT);
    }

    /**
     * Get Product External Id
     *
     * @return int|null
     */
    public function getExternalId()
    {
        return $this->getData(VendorProductInterface::EXTERNAL_ID);
    }

    /**
     * Set Product External Id
     *
     * @param int|null $externalId
     * @return $this
     */
    public function setExternalId($externalId)
    {
        return $this->setData(VendorProductInterface::EXTERNAL_ID, $externalId);
    }

    /**
     * Get Product Is Offered
     *
     * @return bool|null
     */
    public function getIsOffered()
    {
        return $this->getData(VendorProductInterface::IS_OFFERED);
    }

    /**
     * Set Product Is Offereds
     *
     * @param bool|null $isOffered
     * @return $this
     */
    public function setIsOffered($isOffered)
    {
        return $this->setData(VendorProductInterface::IS_OFFERED, $isOffered);
    }

    /**
     * Get Vendor Email
     *
     * @return string|null
     */
    public function getVendorEmail()
    {
        return $this->getData(VendorProductInterface::VENDOR_EMAIL);
    }

    /**
     * Set Vendor Email
     *
     * @param string|null $vendorEmail
     * @return $this
     */
    public function setVendorEmail($vendorEmail)
    {
        return $this->setData(VendorProductInterface::VENDOR_EMAIL, $vendorEmail);
    }

    /**
     * Get Vendor Name
     *
     * @return string|null
     */
    public function getVendorName()
    {
        return $this->getData(VendorProductInterface::VENDOR_NAME);
    }

    /**
     * Set Vendor Name
     *
     * @param string|null $vendorName
     * @return $this
     */
    public function setVendorName($vendorName)
    {
        return $this->setData(VendorProductInterface::VENDOR_NAME, $vendorName);
    }

    /**
     * Get Vendor business Name
     *
     * @return string|null
     */
    public function getBusinessName()
    {
        return $this->getData(VendorProductInterface::BUSINESS_NAME);
    }

    /**
     * Set Vendor business Name
     *
     * @param string|null $businessName
     * @return $this
     */
    public function setBusinessName($businessName)
    {
        return $this->setData(VendorProductInterface::BUSINESS_NAME, $businessName);
    }

    /**
     * Get Vendor Logo
     *
     * @return string|null
     */
    public function getVendorLogo()
    {
        return $this->getData(VendorProductInterface::LOGO);
    }

    /**
     * Set Vendor Logo
     *
     * @param string|null $vendorLogo
     * @return $this
     */
    public function setVendorLogo($vendorLogo)
    {
        return $this->setData(VendorProductInterface::LOGO, $vendorLogo);
    }

    /**
     * Get Vendor Product Website Id
     *
     * @return int|null
     */
    public function getWebsiteId()
    {
        return $this->getData(VendorProductInterface::WEBSITE_ID);
    }

    /**
     * Set Vendor Product Website Id
     *
     * @param int|null $websiteId
     * @return $this
     */
    public function setWebsiteId($websiteId)
    {
        return $this->setData(VendorProductInterface::WEBSITE_ID, $websiteId);
    }

    /**
     * Get Vendor Product Status
     *
     * @return bool|null
     */
    public function getStatus()
    {
        return $this->getData(VendorProductInterface::STATUS);
    }

    /**
     * Set Vendor Product Status
     *
     * @param bool|null $status
     * @return $this
     */
    public function setStatus($status)
    {
        return $this->setData(VendorProductInterface::STATUS, $status);
    }

    /**
     * Get Vendor Product Reorder Level
     *
     * @return float
     */
    public function getReorderLevel()
    {
        return $this->getData(VendorProductInterface::REORDER_LEVEL);
    }

    /**
     * Set Vendor Product Reorder Level
     *
     * @param float|null $reorderLevel
     * @return $this
     */
    public function setReorderLevel($reorderLevel)
    {
        return $this->setData(VendorProductInterface::REORDER_LEVEL, $reorderLevel);
    }

    /**
     * Get Vendor Product Warranty Type
     *
     * @return string
     */
    public function getWarrantyType()
    {
        return $this->getData(VendorProductInterface::WARRANTY_TYPE);
    }

    /**
     * Set Vendor Product Warranty Type
     *
     * @param string $warrantyType
     * @return $this
     */
    public function setWarrantyType($warrantyType)
    {
        return $this->setData(VendorProductInterface::WARRANTY_TYPE, $warrantyType);
    }

    /**
     * Get Vendor Product Condition
     *
     * @return string
     */
    public function getCondition()
    {
        return $this->getData(VendorProductInterface::CONDITION);
    }

    /**
     * Set Vendor Product Condition
     *
     * @param string $condition
     * @return $this
     */
    public function setCondition($condition)
    {
        return $this->setData(VendorProductInterface::CONDITION, $condition);
    }

    /**
     * Get Vendor Product Special To Date
     *
     * @return string
     */
    public function getSpecialToDate()
    {
        return $this->getData(VendorProductInterface::SPECIAL_TO_DATE);
    }

    /**
     * Set Vendor Product Special To Date
     *
     * @param string $specialToDate
     * @return $this
     */
    public function setSpecialToDate($specialToDate)
    {
        return $this->setData(VendorProductInterface::SPECIAL_TO_DATE, $specialToDate);
    }

    /**
     * Get Vendor Product Special From Date
     *
     * @return string
     */
    public function getSpecialFromDate()
    {
        return $this->getData(VendorProductInterface::SPECIAL_FROM_DATE);
    }

    /**
     * Set Vendor Product Special From Date
     *
     * @param string $specialToDate
     * @return $this
     */
    public function setSpecialFromDate($specialFromDate)
    {
        return $this->setData(VendorProductInterface::SPECIAL_FROM_DATE, $specialFromDate);
    }

    /**
     * Get Vendor Product Special Price
     *
     * @return float|null
     */
    public function getSpecialPrice()
    {
        return $this->getData(VendorProductInterface::SPECIAL_PRICE);
    }

    /**
     * Set Product Url
     *
     * @param float|null $specialprice
     * @return $this
     */
    public function setSpecialPrice($specialprice)
    {
        return $this->setData(VendorProductInterface::SPECIAL_PRICE, $specialprice);
    }

    /**
     * Get Vendor Product Price
     *
     * @return float|null
     */
    public function getPrice()
    {
        return $this->getData(VendorProductInterface::PRICE);
    }

    /**
     * Set Vendor Product Price
     *
     * @param float|null $price
     * @return $this
     */
    public function setPrice($price)
    {
        return $this->setData(VendorProductInterface::PRICE, $price);
    }

    /**
     * Get Vendor Product Store ID
     *
     * @return int|null
     */
    public function getStoreId()
    {
        return $this->getData(VendorProductInterface::STORE_ID);
    }

    /**
     * Set Vendor Product Store ID
     *
     * @param int|null $storeId
     * @return $this
     */
    public function setStoreId($storeId)
    {
        return $this->setData(VendorProductInterface::STORE_ID, $storeId);
    }

    /**
     * Get Vendor Product Warranty Description
     *
     * @return string|null
     */
    public function getWarrantyDescription()
    {
        return $this->getData(VendorProductInterface::WARRANTY_DESCRIPTION);
    }

    /**
     * Set Product Url
     *
     * @param string|null $warrantyDesc
     * @return $this
     */
    public function setWarrantyDescription($warrantyDesc)
    {
        return $this->setData(VendorProductInterface::WARRANTY_DESCRIPTION, $warrantyDesc);
    }

    /**
     * Get Vendor Product Condition Note
     *
     * @return string|null
     */
    public function getConditionNote()
    {
        return $this->getData(VendorProductInterface::CONDITION_NOTE);
    }

    /**
     * Set Vendor Product Condition Note
     *
     * @param string|null $conditionNote
     * @return $this
     */
    public function setConditionNote($conditionNote)
    {
        return $this->setData(VendorProductInterface::CONDITION_NOTE, $conditionNote);
    }

    /**
     * Get Vendor Product Store Ids
     *
     * @return string|int|null
     */
    public function getStores()
    {
        return $this->getData(VendorProductInterface::STORES);
    }

    /**
     * Set Vendor Product Store Ids
     *
     * @param string|int|null $storeIds
     * @return $this
     */
    public function setStores($storeIds)
    {
        return $this->setData(VendorProductInterface::STORES, $storeIds);
    }

    /**
     * Get Vendor Product Website Ids
     *
     * @return string|int|null
     */
    public function getWebsites()
    {
        return $this->getData(VendorProductInterface::WEBSITES);
    }

    /**
     * Set Vendor Product Website Ids
     *
     * @param string|int|null $websiteIds
     * @return $this
     */
    public function setWebsites($websiteIds)
    {
        return $this->setData(VendorProductInterface::WEBSITES, $websiteIds);
    }

    /**
     * Get Product Real Sku
     *
     * @return string|null
     */
    public function getSku()
    {
        return $this->getData(VendorProductInterface::SKU);
    }

    /**
     * Set Product Real Sku
     *
     * @param string|null $sku
     * @return $this
     */
    public function setSku($sku)
    {
        return $this->setData(VendorProductInterface::SKU, $sku);
    }

    /**
     * Get Product Url
     *
     * @return string|null
     */
    public function getProductUrl()
    {
        return $this->getData(VendorProductInterface::PRODUCT_URL);
    }

    /**
     * Set Product Url
     *
     * @param string|null $url
     * @return $this
     */
    public function setProductUrl($url)
    {
        return $this->setData(VendorProductInterface::PRODUCT_URL, $url);
    }

    /**
     * Get Product Url
     *
     * @return string|null
     */
    public function getProductName()
    {
        return $this->getData(VendorProductInterface::PRODUCT_NAME);
    }

    /**
     * Set Product Url
     *
     * @param string|null $name
     * @return $this
     */
    public function setProductName($name)
    {
        return $this->setData(VendorProductInterface::PRODUCT_NAME, $name);
    }

    /**
     * {@inheritDoc}
     */
    public function getImage()
    {
        return $this->getData(VendorProductInterface::IMAGE);
    }

    /**
     * {@inheritDoc}
     */
    public function setImage($url = null)
    {
        return $this->setData(VendorProductInterface::IMAGE, $url);
    }

    /**
     * {@inheritDoc}
     *
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * {@inheritDoc}
     *
     */
    public function setExtensionAttributes(
        \Magedelight\Catalog\Api\Data\VendorProductExtensionInterface $extensionAttributes
    ) {
        return $this->_setExtensionAttributes($extensionAttributes);
    }
}
