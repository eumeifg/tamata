<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */


namespace Aheadworks\Raf\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

/**
 * Interface for transaction search results
 * @api
 */
interface TransactionSearchResultsInterface extends SearchResultsInterface
{
    /**
     * Get transaction list
     *
     * @return \Aheadworks\Raf\Api\Data\TransactionInterface[]
     */
    public function getItems();

    /**
     * Set transaction list
     *
     * @param \Aheadworks\Raf\Api\Data\TransactionInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
