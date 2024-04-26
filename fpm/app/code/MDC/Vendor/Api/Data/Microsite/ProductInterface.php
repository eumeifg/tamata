<?php

namespace MDC\Vendor\Api\Data\Microsite;

interface ProductInterface extends \Magedelight\Vendor\Api\Data\Microsite\ProductInterface
{
    const PRODUCT_FILTER = 'product_filter';
    const SORT_FIELDS = 'sort_fields';

    /**
     * Get Product Filter
     *
     * @return \MDC\Vendor\Api\Data\Microsite\ProductFilterInterface[]|null
     */
    public function getProductFilter();

    /**
     * Set Product Filter
     *
     * @param \MDC\Vendor\Api\Data\Microsite\ProductFilterInterface[]|null $productFilter
     */
    public function setProductFilter($productFilter);

    /**
     * Get Sort Fields
     *
     * @return \MDC\Vendor\Api\Data\Microsite\SortFieldsInterface[]|null
     */
    public function getSortFields();

    /**
     * Set SortFields
     *
     * @param \MDC\Vendor\Api\Data\Microsite\SortFieldsInterface[]|null $sortFields
     */
    public function setSortFields($sortFields);
}
