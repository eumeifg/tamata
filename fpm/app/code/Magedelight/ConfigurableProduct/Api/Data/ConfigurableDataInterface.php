<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_ConfigurableProduct
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\ConfigurableProduct\Api\Data;

interface ConfigurableDataInterface
{

    const ATTRIBUTES = 'attributes';
    const DEFAULT_VALUES = 'default_values';
    const IN_STOCK_IDS = 'in_stock_ids';
    const INDEX = 'index';
    const MOBILEATTRIBUTES = 'mobile_attributes';

    /**
     * Get Configurable Attributes
     *
     * @return \Magedelight\ConfigurableProduct\Api\Data\ConfigurableAttributeDataInterface[]
     */
    public function getAttributes();

    /**
     * Set Configurable Attributes
     * @param \Magedelight\ConfigurableProduct\Api\Data\ConfigurableAttributeDataInterface[] array $attributes
     * @return $this
     */
    public function setAttributes($attributes);

    /**
     * Get Configurable Default Values
     *
     * @return mixed
     */
    public function getDefaultValues();

    /**
     * Set Configurable Default Values
     * @param mixed $defaultValues
     * @return $this
     */
    public function setDefaultValues(array $defaultValues);

    /**
     * Get In stock Product Ids
     *
     * @return mixed
     */
    public function getInStockIds();

    /**
     * Set In stock Product Ids
     * @param mixed $productIds
     * @return $this
     */
    public function setInStockIds(array $productIds);

    /**
     * Get Product Attribute Mapping
     *
     * @return \Magedelight\ConfigurableProduct\Api\Data\AssociativeArrayItemInterface[] array
     */
    public function getIndex();

    /**
     * Set Product Attribute Mapping
     * @param \Magedelight\ConfigurableProduct\Api\Data\AssociativeArrayItemInterface[] array $index
     * @return $this
     */
    public function setIndex($index);

     /**
     * Get Configurable Attributes For Mobile Api
     *
     * @return \Magedelight\ConfigurableProduct\Api\Data\ConfigurableAttributeDataInterface[]
     */
    public function getMobileAttributes();

    /**
     * Set Configurable Attributes For Mobile Api
     * @param \Magedelight\ConfigurableProduct\Api\Data\AssociativeArrayItemInterface[] array $mobileAttributes
     * @return $this
     */
    public function setMobileAttributes($mobileAttributes);
}
