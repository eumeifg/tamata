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

interface ProductWebsiteInterface
{
    /**
     * ID
     *
     * @var string
     */
    const PRODUCTWEBSITE_ID = 'row_id';

    /**
     * Vendor ID attribute constant
     *
     * @var string
     */
    const VENDOR_ID = 'vendor_id';

    /**
     * Price attribute constant
     *
     * @var string
     */
    const PRICE = 'price';

    /**
     * Special Price attribute constant
     *
     * @var string
     */
    const SPECIAL_PRICE = 'special_price';

    /**
     * Special From attribute constant
     *
     * @var string
     */
    const SPECIAL_FROM_DATE = 'special_from_date';

    /**
     * Special To attribute constant
     *
     * @var string
     */
    const SPECIAL_TO_DATE = 'special_to_date';

    /**
     * Condition attribute constant
     *
     * @var string
     */
    const CONDITION = 'condition';

    /**
     * Warranty Type attribute constant
     *
     * @var string
     */
    const WARRANTY_TYPE = 'warranty_type';

    /**
     * Reorder Level attribute constant
     *
     * @var string
     */
    const REORDER_LEVEL = 'reorder_level';

    /**
     * Status attribute constant
     *
     * @var string
     */
    const STATUS = 'status';

    /**
     * Website ID attribute constant
     *
     * @var string
     */
    const WEBSITE_ID = 'website_id';

    /**
     * Product Website Data attribute constant
     *
     * @var string
     */
    const PRODUCT_WEBSITE_VENDORPRODUCT_ID = 'vendor_product_id';

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
    public function getProductWebsiteId();

    /**
     * Set ID
     *
     * @param int $ProductWebsiteId
     * @return ProductWebsiteInterface
     */
    public function setProductWebsiteId($ProductWebsiteId);

    /**
     * Get Vendor ID
     *
     * @return mixed
     */
    public function getVendorId();

    /**
     * Set Vendor ID
     *
     * @param mixed $vendorId
     * @return ProductWebsiteInterface
     */
    public function setVendorId($vendorId);

    /**
     * Get Price
     *
     * @return mixed
     */
    public function getPrice();

    /**
     * Set Price
     *
     * @param mixed $price
     * @return ProductWebsiteInterface
     */
    public function setPrice($price);

    /**
     * Get Special Price
     *
     * @return mixed
     */
    public function getSpecialPrice();

    /**
     * Set Special Price
     *
     * @param mixed $specialPrice
     * @return ProductWebsiteInterface
     */
    public function setSpecialPrice($specialPrice);

    /**
     * Get Special From
     *
     * @return mixed
     */
    public function getSpecialFromDate();

    /**
     * Set Special From
     *
     * @param mixed $specialFromDate
     * @return ProductWebsiteInterface
     */
    public function setSpecialFromDate($specialFromDate);

    /**
     * Get Special To
     *
     * @return mixed
     */
    public function getSpecialToDate();

    /**
     * Set Special To
     *
     * @param mixed $specialToDate
     * @return ProductWebsiteInterface
     */
    public function setSpecialToDate($specialToDate);

    /**
     * Get Condition
     *
     * @return mixed
     */
    public function getCondition();

    /**
     * Set Condition
     *
     * @param mixed $condition
     * @return ProductWebsiteInterface
     */
    public function setCondition($condition);

    /**
     * Get Warranty Type
     *
     * @return mixed
     */
    public function getWarrantyType();

    /**
     * Set Warranty Type
     *
     * @param mixed $warrantyType
     * @return ProductWebsiteInterface
     */
    public function setWarrantyType($warrantyType);

    /**
     * Get Reorder Level
     *
     * @return mixed
     */
    public function getReorderLevel();

    /**
     * Set Reorder Level
     *
     * @param mixed $reorderLevel
     * @return ProductWebsiteInterface
     */
    public function setReorderLevel($reorderLevel);

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
     * @return ProductWebsiteInterface
     */
    public function setStatus($status);

    /**
     * Get Website ID
     *
     * @return mixed
     */
    public function getWebsiteId();

    /**
     * Set Website ID
     *
     * @param mixed $websiteId
     * @return ProductWebsiteInterface
     */
    public function setWebsiteId($websiteId);

    /**
     * Get Product Website Data
     *
     * @return int
     */
    public function getProductWebsiteVendorProductId();

    /**
     * Set Product Website Data
     *
     * @param mixed $productWebsiteVendorProductId
     * @return ProductWebsiteInterface
     */
    public function setProductWebsiteVendorProductId($productWebsiteVendorProductId);
}
