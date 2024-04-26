<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Raf\Model\Api\SearchCriteria\CollectionProcessor\FilterProcessor\AdvocateSummary;

use Aheadworks\Raf\Model\ResourceModel\AdvocateSummary\Collection;
use Magento\Framework\Api\Filter;
use Magento\Framework\Api\SearchCriteria\CollectionProcessor\FilterProcessor\CustomFilterInterface;
use Magento\Framework\Data\Collection\AbstractDb;

/**
 * Class ExpireFilter
 *
 * @package Aheadworks\Raf\Model\Api\SearchCriteria\CollectionProcessor\FilterProcessor\AdvocateSummary
 */
class ExpireFilter implements CustomFilterInterface
{
    /**
     * Apply custom expire filter to collection
     *
     * @param Filter $filter
     * @param AbstractDb $collection
     * @return bool
     */
    public function apply(Filter $filter, AbstractDb $collection)
    {
        /** @var Collection $collection */
        $collection->addExpireFilter($filter->getValue());

        return true;
    }
}
