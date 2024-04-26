<?php

namespace Magedelight\Vendor\Api\Data\Microsite;

interface FilterAndSortingDataInterface
{
    const PRODUCT_FILTER = 'product_filter';

    const SORT_FIELDS = 'sort_fields';

    /**
     * Get Product Filter
     *
     * @return \Magedelight\Vendor\Api\Data\Microsite\ProductFilterInterface[]|null
     */
    public function getProductFilter();

    /**
     * Set Product Filter
     *
     * @param \Magedelight\Vendor\Api\Data\Microsite\ProductFilterInterface[]|null $productFilter
     * @return $this
     */
    public function setProductFilter($productFilter);

    /**
     * Get Sort Fields
     *
     * @return \Magedelight\Vendor\Api\Data\Microsite\SortFieldsInterface[]|null
     */
    public function getSortFields();

    /**
     * Set SortFields
     *
     * @param \Magedelight\Vendor\Api\Data\Microsite\SortFieldsInterface[]|null $sortFields
     * @return $this
     */
    public function setSortFields($sortFields);
}
