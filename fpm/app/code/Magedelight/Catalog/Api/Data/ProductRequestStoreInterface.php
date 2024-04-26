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
interface ProductRequestStoreInterface
{
    const PRODUCT_REQUEST_ID = 'product_request_id';

    /**
     * Product Name attribute constant
     *
     * @var string
     */
    const NAME = 'name';

    /**
     * Attributes attribute constant
     *
     * @var string
     */
    const ATTRIBUTES = 'attributes';

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
    const WARRANTY_DESCRIPTION = 'warranty_description';

    /**
     * Store attribute constant
     *
     * @var string
     */
    const STORE_ID = 'store_id';

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
     * Get Product Name
     *
     * @return mixed
     */
    public function getName();

    /**
     * Set Product Name
     *
     * @param mixed $name
     * @return ProductRequestInterface
     */
    public function setName($name);

    /**
     * Get Attributes
     *
     * @return mixed
     */
    public function getAttributes();

    /**
     * Set Attributes
     *
     * @param mixed $attributes
     * @return ProductRequestInterface
     */
    public function setAttributes($attributes);

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
     * @return ProductRequestInterface
     */
    public function setConditionNote($conditionNote);

    /**
     * Get Warranty Description
     *
     * @return mixed
     */
    public function getWarrantyDescription();

    /**
     * Set Warranty Description
     *
     * @param mixed $warrantyDescription
     * @return ProductRequestInterface
     */
    public function setWarrantyDescription($warrantyDescription);

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
}
