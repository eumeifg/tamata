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

interface ProductVendorDataInterface
{
    const VENDOR_PRODUCT_ID = 'vendor_product_id';
    const MARKETPLACE_PRODUCT_ID = 'marketplace_product_id';
    const PARENT_ID = 'parent_id';
    const TYPE_ID ='type_id';
    const VENDOR_ID = 'vendor_id';
    const EXTERNAL_ID = 'external_id';
    const IS_DELETED = 'is_deleted';
    const VENDOR_SKU = 'vendor_sku';
    const QTY = 'qty';
    const IS_OFFERED = 'is_offered';
    const APPROVED_AT = 'approved_at';
    /**
     * Get Vendor Product Id
     * @return int $vendorProductId
     */
    public function getVendorProductId();

    /**
     * Set Vendor Product Id
     * @param int $vendorProductId
     * @return $this
     */
    public function setVendorProductId(int $vendorProductId);

    /**
     * get Marketplace Product Id
     * @return int $marketPlaceProductId
     */
    public function getMarketplaceProductId();

    /**
     * set Marketplace Product Id
     * @param int $marketPlaceProductId
     * @return $this
     */
    public function setMarketplaceProductId($marketPlaceProductId);

    /**
     * get Product Parent Id
     * @return int $parentId
     */
    public function getParentId();

    /**
     * set Product Parent Id
     * @param int $parentId
     * @return $this
     */
    public function setParentId($parentId);

    /**
     * get Product Type Id
     * @return string $typeId
     */
    public function getTypeId();

    /**
     * set Product Type Id
     * @param string $typeId
     * @return $this
     */
    public function setTypeId($typeId);

    /**
     * Get Vendor Id
     *
     * @return int
     */
    public function getVendorId();

    /**
     * Set Vendor Id
     * @param int $vendorId
     * @return $this
     */
    public function setVendorId(int $vendorId);

    /**
     * get External Id
     * @return int $externalId
     */
    public function getExternalId();

    /**
     * set External Id
     * @param int $externalId
     * @return $this
     */
    public function setExternalId($externalId);

    /**
     * Get Is Deleted
     * @return int $isDeleted
     */
    public function getIsDeleted();

    /**
     * set External Id
     * @param int $isDeleted
     * @return $this
     */
    public function setIsDeleted($isDeleted);

    /**
     * Get Vendor SKU
     * @return string $vendorSku
     */
    public function getVendorSku();

    /**
     * set Vendor SKU
     * @param string $vendorSku
     * @return $this
     */
    public function setVendorSku($vendorSku);

    /**
     * Get Quantity
     * @return float $qty
     */
    public function getQty();

    /**
     * set Quantity
     * @param float $qty
     * @return $this
     */
    public function setQty($qty);

    /**
     * Get Is Offered
     * @return int $isOffered
     */
    public function getIsOffered();

    /**
     * set Quantity
     * @param int $isOffered
     * @return $this
     */
    public function setIsOffered($isOffered);

    /**
     * Get Approved At
     * @return string $approvedAt
     */
    public function getApprovedAt();

    /**
     * set Approved At
     * @param string $approvedAt
     * @return $this
     */
    public function setApprovedAt($approvedAt);

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Magedelight\Catalog\Api\Data\ProductVendorDataExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Magedelight\Catalog\Api\Data\ProductVendorDataExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Magedelight\Catalog\Api\Data\ProductVendorDataExtensionInterface $extensionAttributes
    );
}
