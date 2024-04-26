<?php

namespace Magedelight\Vendor\Model\Microsite\Build\Data;

use Magedelight\Vendor\Api\Data\Microsite\FilterAndSortingDataInterface;

class FilterAndSortingData extends \Magento\Framework\DataObject implements FilterAndSortingDataInterface
{

    /**
     * @inheritDoc
     */
    public function getProductFilter()
    {
        return $this->getData(FilterAndSortingDataInterface::PRODUCT_FILTER);
    }

    /**
     * @inheritDoc
     */
    public function setProductFilter($productFilter)
    {
        return $this->setData(FilterAndSortingDataInterface::PRODUCT_FILTER, $productFilter);
    }

    /**
     * @inheritDoc
     */
    public function getSortFields()
    {
        return $this->getData(FilterAndSortingDataInterface::SORT_FIELDS);
    }

    /**
     * @inheritDoc
     */
    public function setSortFields($sortFields)
    {
        return $this->setData(FilterAndSortingDataInterface::SORT_FIELDS, $sortFields);
    }
}
