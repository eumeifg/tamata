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

/**
 * Product request interface.
 */
interface ProductRequestWebsiteInterface
{
    const PRODUCT_REQUEST_ID = 'product_request_id';

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
     * Special From Date attribute constant
     *
     * @var string
     */
    const SPECIAL_FROM_DATE = 'special_from_date';

    /**
     * Special To Date attribute constant
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
     * Reorder Level attribute constant
     *
     * @var string
     */
    const REORDER_LEVEL = 'reorder_level';

    /**
     * Warranty Type attribute constant
     *
     * @var string
     */
    const WARRANTY_TYPE = 'warranty_type';

    /**
     * Website attribute constant
     *
     * @var string
     */
    const WEBSITE_ID = 'website_id';

    /**
     * Get Product Request Id
     *
     * @return int|null
     */
    public function getProductRequestId();

    /**
     * Set Product Request Id
     *
     * @param int $productRequestId
     * @return ProductRequestInterface
     */
    public function setProductRequestId($productRequestId);

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
     * @return ProductRequestInterface
     */
    public function setCondition($condition);

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
     * @return ProductRequestInterface
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
     * @return ProductRequestInterface
     */
    public function setSpecialPrice($specialPrice);

    /**
     * Get Special From Date
     *
     * @return mixed
     */
    public function getSpecialFromDate();

    /**
     * Set Special From Date
     *
     * @param mixed $specialFromDate
     * @return ProductRequestInterface
     */
    public function setSpecialFromDate($specialFromDate);

    /**
     * Get Special To Date
     *
     * @return mixed
     */
    public function getSpecialToDate();

    /**
     * Set Special To Date
     *
     * @param mixed $specialToDate
     * @return ProductRequestInterface
     */
    public function setSpecialToDate($specialToDate);

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
     * @return ProductRequestInterface
     */
    public function setReorderLevel($reorderLevel);

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
     * @return ProductRequestInterface
     */
    public function setWarrantyType($warrantyType);

    /**
     * Get Website
     *
     * @return mixed
     */
    public function getWebsiteId();

    /**
     * Set Website
     *
     * @param mixed $websiteId
     * @return ProductRequestInterface
     */
    public function setWebsiteId($websiteId);
}
