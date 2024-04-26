<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magedelight\Sales\Api\Data;

interface OrderItemAttributeInfoInterface
{

    /**
     * Get Attribute Label
     *
     * @return string|NULL
     */
    public function getLabel();

    /**
     * Set Attribute Label
     *
     * @param string $label
     * @return $this
     */
    public function setLabel($label);

    /**
     * Get Attribute Value
     *
     * @return string|NULL
     */
    public function getValue();

    /**
     * Set Attribute Value
     *
     * @param string $value
     * @return $this
     */
    public function setValue($value);

    /**
     * Get Option Id
     *
     * @return int|NULL
     */
    public function getOptionId();

    /**
     * Set Option Id
     *
     * @param int|NULL $optionId
     * @return $this
     */
    public function setOptionId($optionId);

    /**
     * Get Option Id
     *
     * @return int|NULL
     */
    public function getOptionValue();

    /**
     * Set Option Id
     *
     * @param int|NULL $optionValue
     * @return $this
     */
    public function setOptionValue($optionValue);
}
