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

interface ProductRequestInterface extends ExtensibleDataInterface
{
    /**#@+
     * Constants defined for keys of  data array
     */
    const REQUEST_ID = 'product_request_id';

    const MARKETPLACE_PRODUCT_ID = 'marketplace_product_id';

    const VENDOR_ID = 'vendor_id';

    const MAIN_CATEGORY_ID = 'main_category_id';

    const CATEGORY_ID = 'category_id';

    const ATTRIBUTE_SET_ID = 'attribute_set_id';

    const STATUS = 'status';

    const DISAPPROVE_MESSAGE = 'disapprove_message';

    const HAS_VARIANTS = 'has_variants';

    const USED_PRODUCT_ATTRIBUTE_IDS = 'used_product_attribute_ids';

    const CONFIGURABLE_ATTRIBUTES = 'configurable_attributes';

    const CONFIGURABLE_ATTRIBUTE_CODES = 'configurable_attribute_codes';

    const CONFIGURABLE_ATTRIBUTES_DATA = 'configurable_attributes_data';

    const VENDOR_SKU = 'vendor_sku';

    const QTY = 'qty';

    const IMAGES = 'images';

    const BASE_IMAGE = 'base_image';

    const IS_REQUESTED_FOR_EDIT = 'is_requested_for_edit';

    const VENDOR_PRODUCT_ID = 'vendor_product_id';

    const STORE_ID = 'store_id';

    const TAX_CLASS_ID = 'tax_class_id';

    const IS_OFFERED = 'is_offered';

    const WEBSITE_IDS = 'website_ids';

    const PRICE = 'price';

    const SPECIAL_PRICE = 'special_price';

    const SPECIAL_FROM_DATE = 'special_from_date';

    const SPECIAL_TO_DATE = 'special_to_date';

    const REORDER_LEVEL = 'reorder_level';

    const NAME = 'name';

    const BASE_IMAGE_URL = 'base_image_url';

    /**
     * Get ID
     *
     * @return int|null
     */
    public function getProductRequestId();

    /**
     * Set ID
     *
     * @param int $requestId
     * @return $this
     */
    public function setProductRequestId($requestId);

    /**
     * Get Product Id
     *
     * @return mixed
     */
    public function getMarketplaceProductId();

    /**
     * Set Product Id
     *
     * @param mixed $marketplaceProductId
     * @return ProductRequestInterface
     */
    public function setMarketplaceProductId($marketplaceProductId);

    /**
     * Get Vendor
     *
     * @return mixed
     */
    public function getVendorId();

    /**
     * Set Vendor
     *
     * @param mixed $vendorId
     * @return ProductRequestInterface
     */
    public function setVendorId($vendorId);

    /**
     * Get Main Category ID
     *
     * @return mixed
     */
    public function getMainCategoryId();

    /**
     * Set Main Category ID
     *
     * @param mixed $mainCategoryId
     * @return ProductRequestInterface
     */
    public function setMainCategoryId($mainCategoryId);

    /**
     * Get Category Id
     *
     * @return mixed
     */
    public function getCategoryId();

    /**
     * Set Category Id
     *
     * @param mixed $categoryId
     * @return ProductRequestInterface
     */
    public function setCategoryId($categoryId);

    /**
     * Get Attribute Set Id
     *
     * @return mixed
     */
    public function getAttributeSetId();

    /**
     * Set Attribute Set Id
     *
     * @param mixed $attributeSetId
     * @return ProductRequestInterface
     */
    public function setAttributeSetId($attributeSetId);

    /**
     * Get Status
     *
     * @return mixed
     */
    public function getStatus();

    /**
     * Set Status
     *
     * @param mixed $status
     * @return ProductRequestInterface
     */
    public function setStatus($status);

    /**
     * Get Disapprove Message
     *
     * @return mixed
     */
    public function getDisapproveMessage();

    /**
     * Set Disapprove Message
     *
     * @param mixed $disapproveMessage
     * @return ProductRequestInterface
     */
    public function setDisapproveMessage($disapproveMessage);

    /**
     * Get Has Variants
     *
     * @return mixed
     */
    public function getHasVariants();

    /**
     * Set Has Variants
     *
     * @param mixed $hasVariants
     * @return ProductRequestInterface
     */
    public function setHasVariants($hasVariants);

    /**
     * Get Used_product_attribute_ids
     *
     * @return mixed
     */
    public function getUsedProductAttributeIds();

    /**
     * Set Used_product_attribute_ids
     *
     * @param mixed $usedProductAttributeIds
     * @return ProductRequestInterface
     */
    public function setUsedProductAttributeIds($usedProductAttributeIds);

    /**
     * Get Configurable Attributes
     *
     * @return mixed
     */
    public function getConfigurableAttributes();

    /**
     * Set Configurable Attributes
     *
     * @param mixed $configurableAttributes
     * @return ProductRequestInterface
     */
    public function setConfigurableAttributes($configurableAttributes);

    /**
     * Get Configurable Attributes Codes
     *
     * @return mixed
     */
    public function getConfigurableAttributeCodes();

    /**
     * Set Configurable Attributes Codes
     *
     * @param mixed $configurableAttributeCodes
     * @return ProductRequestInterface
     */
    public function setConfigurableAttributeCodes($configurableAttributeCodes);

    /**
     * Get Configurable Attributes Data
     *
     * @return mixed
     */
    public function getConfigurableAttributesData();

    /**
     * Set Configurable Attributes Data
     *
     * @param mixed $configurableAttributesData
     * @return ProductRequestInterface
     */
    public function setConfigurableAttributesData($configurableAttributesData);

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
     * @return ProductRequestInterface
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
     * @return ProductRequestInterface
     */
    public function setQty($qty);

    /**
     * Get Images
     *
     * @return mixed
     */
    public function getImages();

    /**
     * Set Images
     *
     * @param mixed $images
     * @return ProductRequestInterface
     */
    public function setImages($images);

    /**
     * Get Base Image
     *
     * @return mixed
     */
    public function getBaseImage();

    /**
     * Set Base Image
     *
     * @param mixed $baseImage
     * @return ProductRequestInterface
     */
    public function setBaseImage($baseImage);

    /**
     * Get Is Edit Request
     *
     * @return mixed
     */
    public function getIsRequestedForEdit();

    /**
     * Set Is Edit Request
     *
     * @param mixed $isRequestedForEdit
     * @return ProductRequestInterface
     */
    public function setIsRequestedForEdit($isRequestedForEdit);

    /**
     * Get Vendor Product Id
     *
     * @return mixed
     */
    public function getVendorProductId();

    /**
     * Set Vendor Product Id
     *
     * @param mixed $vendorProductId
     * @return ProductRequestInterface
     */
    public function setVendorProductId($vendorProductId);

    /**
     * Get Store
     *
     * @return mixed
     */
    public function getStoreId();

    /**
     * Set Store
     *
     * @param mixed $storeId
     * @return ProductRequestInterface
     */
    public function setStoreId($storeId);

    /**
     * Get Tax Class
     *
     * @return mixed
     */
    public function getTaxClassId();

    /**
     * Set Tax Class
     *
     * @param mixed $taxClassId
     * @return ProductRequestInterface
     */
    public function setTaxClassId($taxClassId);

    /**
     * Get Is Offered
     *
     * @return mixed
     */
    public function getIsOffered();

    /**
     * Set Is Offered
     *
     * @param mixed $isOffered
     * @return ProductRequestInterface
     */
    public function setIsOffered($isOffered);

    /**
     * Get Website Ids
     *
     * @return mixed
     */
    public function getWebsiteIds();

    /**
     * Set Website Ids
     *
     * @param mixed $websiteIds
     * @return RequestInterface
     */
    public function setWebsiteIds($websiteIds);

    /**
     * Product price
     *
     * @return float|null
     */
    public function getPrice();

    /**
     * Set product price
     *
     * @param float $price
     * @return $this
     */
    public function setPrice($price);

    /**
     * Product special price
     *
     * @return float|NULL
     */
    public function getSpecialPrice();

    /**
     * Set product special price
     *
     * @param float|NULL $specialPrice
     * @return $this
     */
    public function setSpecialPrice($specialPrice);

    /**
     * Product reorder level
     *
     * @return int|NULL
     */
    public function getReorderLevel();

    /**
     * Set product reorder level
     *
     * @param int|NULL $reorderLevel
     * @return $this
     */
    public function setReorderLevel($reorderLevel);

    /**
     * Get Product Name
     *
     * @return string
     */
    public function getName();

    /**
     * Set Product Name
     *
     * @param string $name
     * @return $this
     */
    public function setName($name);

    /**
     * Get Product Base Image Url
     *
     * @return string|NULL
     */
    public function getBaseImageUrl();

    /**
     * Set Product Base Image Url
     *
     * @param string|NULL $baseImage
     * @return $this
     */
    public function setBaseImageUrl($baseImage);

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Magedelight\Catalog\Api\Data\ProductRequestExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Magedelight\Catalog\Api\Data\ProductRequestExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Magedelight\Catalog\Api\Data\ProductRequestExtensionInterface $extensionAttributes
    );
}
