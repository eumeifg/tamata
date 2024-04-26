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

interface ProductStoreInterface
{
    /**
     * ID
     *
     * @var string
     */
    const PRODUCTSTORE_ID = 'row_id';

    /**
     * Vendor Product attribute constant
     *
     * @var string
     */
    const VENDOR_PRODUCT_ID = 'vendor_product_id';

    /**
     * Condition Note attribute constant
     *
     * @var string
     */
    const CONDITION_NOTE = 'condition_note';

    /**
     * Warranty Description attribute constant
     *
     * @var string
     */
    const WARRANTY_DESCIPTION = 'warranty_desciption';

    /**
     * Store ID attribute constant
     *
     * @var string
     */
    const STORE_ID = 'store_id';

    /**
     * Website ID attribute constant
     *
     * @var string
     */
    const WEBSITE_ID = 'website_id';

    /**
     * Product Store Data attribute constant
     *
     * @var string
     */
    const PRODUCT_STORE_VENDORPRODUCT_ID = 'vendor_product_id';

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
    public function getProductStoreId();

    /**
     * Set ID
     *
     * @param int $ProductStoreId
     * @return ProductStoreInterface
     */
    public function setProductStoreId($ProductStoreId);

    /**
     * Get Vendor Product
     *
     * @return mixed
     */
    public function getVendorProductId();

    /**
     * Set Vendor Product
     *
     * @param mixed $vendorProductId
     * @return ProductStoreInterface
     */
    public function setVendorProductId($vendorProductId);

    /**
     * Get Condition Note
     *
     * @return mixed
     */
    public function getConditionNote();

    /**
     * Set Condition Note
     *
     * @param mixed $conditionNote
     * @return ProductStoreInterface
     */
    public function setConditionNote($conditionNote);

    /**
     * Get Warranty Description
     *
     * @return mixed
     */
    public function getWarrantyDesciption();

    /**
     * Set Warranty Description
     *
     * @param mixed $warrantyDesciption
     * @return ProductStoreInterface
     */
    public function setWarrantyDesciption($warrantyDesciption);

    /**
     * Get Store ID
     *
     * @return mixed
     */
    public function getStoreId();

    /**
     * Set Store ID
     *
     * @param mixed $storeId
     * @return ProductStoreInterface
     */
    public function setStoreId($storeId);

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
     * @return ProductStoreInterface
     */
    public function setWebsiteId($websiteId);

    /**
     * Get Product Store Data
     *
     * @return int
     */
    public function getProductStoreVendorProductId();

    /**
     * Set Product Store Data
     *
     * @param mixed $productStoreVendorProductId
     * @return ProductStoreInterface
     */
    public function setProductStoreVendorProductId($productStoreVendorProductId);
}
