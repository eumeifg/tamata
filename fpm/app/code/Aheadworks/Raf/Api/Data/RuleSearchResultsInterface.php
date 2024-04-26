<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */


namespace Aheadworks\Raf\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

/**
 * Interface for rule search results
 * @api
 */
interface RuleSearchResultsInterface extends SearchResultsInterface
{
    /**
     * Get rule list
     *
     * @return \Aheadworks\Raf\Api\Data\RuleInterface[]
     */
    public function getItems();

    /**
     * Set rule list
     *
     * @param \Aheadworks\Raf\Api\Data\RuleInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
