<?php

namespace MDC\Vendor\Api\Data\Microsite;

interface SortFieldsInterface
{
    const DEFAULT = 'default';
    const OPTIONS = 'options';

    /**
     * Default
     *
     * @return string|null
     */
    public function getDefault();

    /**
     * Set default
     *
     * @param string $default
     * @return $this
     */
    public function setDefault($default);

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
