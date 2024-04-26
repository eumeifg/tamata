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

interface ProductAdditionalAttributeDataInterface
{
    /**
     * Get Attribute Label
     *
     * @return string
     */
    public function getLabel();

    /**
     * Set Attribute Label
     * @param array $additionalInformation
     * @return $this
     */
    public function setLabel(string $attributeLabel);

    /**
     * Get Attribute Value
     *
     * @return string
     */
    public function getValue();

    /**
     * Set Attribute Value
     * @param string $additionalInformation
     * @return $this
     */
    public function setValue(string $attributeValue);

    /**
     * Get Attribute Code
     *
     * @return string
     */
    public function getCode();

    /**
     * Set Attribute Code
     * @param string $attributeCode
     * @return $this
     */
    public function setCode(string $attributeCode);
}
