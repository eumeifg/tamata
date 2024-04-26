<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */


namespace Aheadworks\Raf\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

/**
 * Interface for advocate summary search results
 * @api
 */
interface AdvocateSummarySearchResultsInterface extends SearchResultsInterface
{
    /**
     * Get advocate summary list
     *
     * @return \Aheadworks\Raf\Api\Data\AdvocateSummaryInterface[]
     */
    public function getItems();

    /**
     * Set advocate summary list
     *
     * @param \Aheadworks\Raf\Api\Data\AdvocateSummaryInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
