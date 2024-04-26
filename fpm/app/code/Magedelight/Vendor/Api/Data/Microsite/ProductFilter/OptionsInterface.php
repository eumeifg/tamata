<?php

namespace Magedelight\Vendor\Api\Data\Microsite\ProductFilter;

interface OptionsInterface
{
    const LABEL = 'label';
    const VALUE = 'value';

    /**
     * Label
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
     * Value
     *
     * @return string|null
     */
    public function getValue();

    /**
     * Set Value
     *
     * @param string $value
     * @return $this
     */
    public function setValue($value);
}
