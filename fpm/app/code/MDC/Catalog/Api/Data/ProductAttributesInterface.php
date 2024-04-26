<?php
namespace MDC\Catalog\Api\Data;

interface ProductAttributesInterface
{
    const ATTRIBUTE_LABEL = 'attribute_label';

    const ATTRIBUTE_VALUE = 'attribute_value';

    /**
     * Get Label
     *
     * @return string|NULL
     */
    public function getAttributeLabel();

    /**
     * Set Label
     *
     * @param string|NULL $label
     * @return $this
     */
    public function setAttributeLabel($label);

    /**
     * Get Value
     *
     * @return string|NULL
     */
    public function getAttributeValue();

    /**
     * Set Value
     *
     * @param string|NULL $value
     * @return $this
     */
    public function setAttributeValue($value);
}
