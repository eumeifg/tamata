<?php

namespace MDC\Vendor\Api\Data\Microsite;

interface ProductFilterInterface
{
    const ATTRIBUTE_CODE = 'attribute_code';
    const LABEL = 'label';
    const OPTIONS = 'options';

    /**
     * Attribute Code
     *
     * @return string|null
     */
    public function getAttributeCode();

    /**
     * Set Attribute Code
     *
     * @param string $attributeCode
     * @return $this
     */
    public function setAttributeCode($attributeCode);

    /**
     * label
     *
     * @return string|null
     */
    public function getLabel();

    /**
     * Set label
     *
     * @param string $label
     * @return $this
     */
    public function setLabel($label);

    /**
     * Options
     *
     * @return \MDC\Vendor\Api\Data\Microsite\ProductFilter\OptionsInterface[]|null
     */
    public function getOptions();

    /**
     * Get options
     *
     * @param \MDC\Vendor\Api\Data\Microsite\ProductFilter\OptionsInterface[]|null $options
     */
    public function setOptions($options);
}
