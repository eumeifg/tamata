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
namespace Magedelight\Catalog\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

interface VendorProductInterface extends ExtensibleDataInterface
{
    /**
     * ID
     *
     * @var string
     */
    const VENDORPRODUCT_ID = 'vendor_product_id';

    /**
     * Marketplace Product Id attribute constant
     *
     * @var string
     */
    const MARKETPLACE_PRODUCT_ID = 'marketplace_product_id';

    /**
     * Parent ID attribute constant
     *
     * @var string
     */
    const PARENT_ID = 'parent_id';

    /**
     * Type attribute constant
     *
     * @var string
     */
    const TYPE_ID = 'type_id';

    /**
     * Vendor Id attribute constant
     *
     * @var string
     */
    const VENDOR_ID = 'vendor_id';

    /**
     * Is Deleted attribute constant
     *
     * @var string
     */
    const IS_DELETED = 'is_deleted';

    /**
     * Vendor SKU attribute constant
     *
     * @var string
     */
    const VENDOR_SKU = 'vendor_sku';

    /**
     * Quantity attribute constant
     *
     * @var string
     */
    const QTY = 'qty';

    /**
     * Approve Date attribute constant
     *
     * @var string
     */
    const APPROVED_AT = 'approved_at';

    /**
     * Product Website Data attribute constant
     *
     * @var string
     */
    const PRODUCT_WEBSITE_PRODUCTWEBSITE_ID = 'Product_Website_ProductWebsite_id';

    /**
     * Product Store Data attribute constant
     *
     * @var string
     */
    const PRODUCT_STORE_PRODUCTSTORE_ID = 'Product_Store_ProductStore_id';

    /*-----*/
    const EXTERNAL_ID = 'external_id';

    const IS_OFFERED = 'is_offered';

    const VENDOR_EMAIL = 'email';

    const VENDOR_NAME = 'vendor_name';

    const BUSINESS_NAME = 'business_name';

    const LOGO = 'logo';

    const WEBSITE_ID = 'website_id';

    const STATUS = 'status';

    const REORDER_LEVEL = 'reorder_level';

    const WARRANTY_TYPE = 'warranty_type';

    const CONDITION = 'condition';

    const SPECIAL_TO_DATE ='special_to_date';

    const SPECIAL_FROM_DATE = 'special_from_date';

    const SPECIAL_PRICE ='special_price';

    const PRICE = 'price';

    const STORE_ID = 'store_id';

    const WARRANTY_DESCRIPTION ='warranty_description';

    const CONDITION_NOTE = 'condition_note';

    const STORES = 'stores';

    const WEBSITES = 'websites';

    const SKU = 'sku';

    const PRODUCT_URL ='product_url';

    const PRODUCT_NAME = 'product_name';

    const IMAGE = 'image';

    /**
     * Get ID
     *
     * @return int|null
     */
    public function getId();

    /**
     * Get ID
     *
     * @return int|null
     */
    public function getVendorProductId();

    /**
     * Set ID
     *
     * @param int $VendorProductId
     * @return VendorProductInterface
     */
    public function setVendorProductId($VendorProductId);

    /**
     * Get Marketplace Product Id
     *
     * @return mixed
     */
    public function getMarketplaceProductId();

    /**
     * Set Marketplace Product Id
     *
     * @param mixed $marketplaceProductId
     * @return VendorProductInterface
     */
    public function setMarketplaceProductId($marketplaceProductId);

    /**
     * Get Parent ID
     *
     * @return mixed
     */
    public function getParentId();

    /**
     * Set Parent ID
     *
     * @param mixed $parentId
     * @return VendorProductInterface
     */
    public function setParentId($parentId);

    /**
     * Get Type
     *
     * @return mixed
     */
    public function getTypeId();

    /**
     * Set Type
     *
     * @param mixed $typeId
     * @return VendorProductInterface
     */
    public function setTypeId($typeId);

    /**
     * Get Vendor Id
     *
     * @return mixed
     */
    public function getVendorId();

    /**
     * Set Vendor Id
     *
     * @param mixed $vendorId
     * @return VendorProductInterface
     */
    public function setVendorId($vendorId);

    /**
     * Get Is Deleted
     *
     * @return mixed
     */
    public function getIsDeleted();

    /**
     * Set Is Deleted
     *
     * @param mixed $isDeleted
     * @return VendorProductInterface
     */
    public function setIsDeleted($isDeleted);

    /**
     * Get Vendor SKU
     *
     * @return mixed
     */
    public function getVendorSku();

    /**
     * Set Vendor SKU
     *
     * @param mixed $vendorSku
     * @return VendorProductInterface
     */
    public function setVendorSku($vendorSku);

    /**
     * Get Quantity
     *
     * @return mixed
     */
    public function getQty();

    /**
     * Set Quantity
     *
     * @param mixed $qty
     * @return VendorProductInterface
     */
    public function setQty($qty);

    /**
     * Get Approve Date
     *
     * @return mixed
     */
    public function getApprovedAt();

    /**
     * Set Approve Date
     *
     * @param mixed $approvedAt
     * @return VendorProductInterface
     */
    public function setApprovedAt($approvedAt);

    /**
     * Get Product Website Data
     *
     * @return int
     */
    public function getProductWebsiteProductWebsiteId();

    /**
     * Set Product Website Data
     *
     * @param mixed $productWebsiteProductWebsiteId
     * @return VendorProductInterface
     */
    public function setProductWebsiteProductWebsiteId($productWebsiteProductWebsiteId);

    /**
     * Get Product Store Data
     *
     * @return int
     */
    public function getProductStoreProductStoreId();

    /**
     * Set Product Store Data
     *
     * @param mixed $productStoreProductStoreId
     * @return VendorProductInterface
     */
    public function setProductStoreProductStoreId($productStoreProductStoreId);

    /**
     * Get Product External Id
     *
     * @return int|null
     */
    public function getExternalId();

    /**
     * Set Product External Id
     *
     * @param int|null $externalId
     * @return $this
     */
    public function setExternalId($externalId);

    /**
     * Get Product Is Offered
     *
     * @return bool|null
     */
    public function getIsOffered();

    /**
     * Set Product Is Offereds
     *
     * @param bool|null $isOffered
     * @return $this
     */
    public function setIsOffered($isOffered);

    /**
     * Get Vendor Email
     *
     * @return string|null
     */
    public function getVendorEmail();

    /**
     * Set Vendor Email
     *
     * @param string|null $vendorEmail
     * @return $this
     */
    public function setVendorEmail($vendorEmail);

    /**
     * Get Vendor Name
     *
     * @return string|null
     */
    public function getVendorName();

    /**
     * Set Vendor Name
     *
     * @param string|null $vendorName
     * @return $this
     */
    public function setVendorName($vendorName);

    /**
     * Get Vendor business Name
     *
     * @return string|null
     */
    public function getBusinessName();

    /**
     * Set Vendor business Name
     *
     * @param string|null $businessName
     * @return $this
     */
    public function setBusinessName($businessName);

    /**
     * Get Vendor Logo
     *
     * @return string|null
     */
    public function getVendorLogo();

    /**
     * Set Vendor Logo
     *
     * @param string|null $vendorLogo
     * @return $this
     */
    public function setVendorLogo($vendorLogo);

    /**
     * Get Vendor Product Website Id
     *
     * @return int|null
     */
    public function getWebsiteId();

    /**
     * Set Vendor Product Website Id
     *
     * @param int|null $websiteId
     * @return $this
     */
    public function setWebsiteId($websiteId);

    /**
     * Get Vendor Product Status
     *
     * @return bool|null
     */
    public function getStatus();

    /**
     * Set Vendor Product Status
     *
     * @param bool|null $status
     * @return $this
     */
    public function setStatus($status);

    /**
     * Get Vendor Product Reorder Level
     *
     * @return float
     */
    public function getReorderLevel();

    /**
     * Set Vendor Product Reorder Level
     *
     * @param float|null $reorderLevel
     * @return $this
     */
    public function setReorderLevel($reorderLevel);

    /**
     * Get Vendor Product Warranty Type
     *
     * @return string
     */
    public function getWarrantyType();

    /**
     * Set Vendor Product Warranty Type
     *
     * @param string $warrantyType
     * @return $this
     */
    public function setWarrantyType($warrantyType);

    /**
     * Get Vendor Product Condition
     *
     * @return string
     */
    public function getCondition();

    /**
     * Set Vendor Product Condition
     *
     * @param string $condition
     * @return $this
     */
    public function setCondition($condition);

    /**
     * Get Vendor Product Special To Date
     *
     * @return string
     */
    public function getSpecialToDate();

    /**
     * Set Vendor Product Special To Date
     *
     * @param string $specialToDate
     * @return $this
     */
    public function setSpecialToDate($specialToDate);

    /**
     * Get Vendor Product Special From Date
     *
     * @return string
     */
    public function getSpecialFromDate();

    /**
     * Set Vendor Product Special From Date
     *
     * @param string $specialToDate
     * @return $this
     */
    public function setSpecialFromDate($specialFromDate);

    /**
     * Get Vendor Product Price
     *
     * @return float|null
     */
    public function getPrice();

    /**
     * Set Vendor Product Price
     *
     * @param float|null $price
     * @return $this
     */
    public function setPrice($price);

    /**
     * Get Vendor Product Store ID
     *
     * @return int|null
     */
    public function getStoreId();

    /**
     * Set Vendor Product Store ID
     *
     * @param int|null $storeId
     * @return $this
     */
    public function setStoreId($storeId);

    /**
     * Get Vendor Product Condition Note
     *
     * @return string|null
     */
    public function getConditionNote();

    /**
     * Set Vendor Product Condition Note
     *
     * @param string|null $conditionNote
     * @return $this
     */
    public function setConditionNote($conditionNote);

    /**
     * Get Vendor Product Store Ids
     *
     * @return string|int|null
     */
    public function getStores();

    /**
     * Set Vendor Product Store Ids
     *
     * @param string|int|null $storeIds
     * @return $this
     */
    public function setStores($storeIds);

    /**
     * Get Vendor Product Website Ids
     *
     * @return string|int|null
     */
    public function getWebsites();

    /**
     * Set Vendor Product Website Ids
     *
     * @param string|int|null $websiteIds
     * @return $this
     */
    public function setWebsites($websiteIds);

    /**
     * Get Product Real Sku
     *
     * @return string|null
     */
    public function getSku();

    /**
     * Set Product Real Sku
     *
     * @param string|null $sku
     * @return $this
     */
    public function setSku($sku);

    /**
     * Get Product Url
     *
     * @return string|null
     */
    public function getProductUrl();

    /**
     * Set Product Url
     *
     * @param string|null $url
     * @return $this
     */
    public function setProductUrl($url);

    /**
     * Get Product Url
     *
     * @return string|null
     */
    public function getProductName();

    /**
     * Set Product Url
     *
     * @param string|null $name
     * @return $this
     */
    public function setProductName($name);

    /**
     * Get Vendor Product Special Price
     *
     * @return float|null
     */
    public function getSpecialPrice();

    /**
     * Set Product Url
     *
     * @param float|null $specialprice
     * @return $this
     */
    public function setSpecialPrice($specialprice);

    /**
     * Get Vendor Product Warranty Description
     *
     * @return string|null
     */
    public function getWarrantyDescription();

    /**
     * Set Product Url
     *
     * @param string|null $warrantyDesc
     * @return $this
     */
    public function setWarrantyDescription($warrantyDesc);

    /**
     * Set product image url
     *
     * @param string|null $url
     * @return $this
     */
    public function setImage($url = null);

    /**
     * Get product image url
     *
     * @return string|null
     */
    public function getImage();

    /**
     * Retrieve existing extension attributes object or create a new one.
     * @return \Magedelight\Catalog\Api\Data\VendorProductExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     * @param \Magedelight\Catalog\Api\Data\VendorProductExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Magedelight\Catalog\Api\Data\VendorProductExtensionInterface $extensionAttributes
    );
}
