<?php

namespace CAT\Custom\Api\Data;

/**
 * ProductSearchResultsInterface interface
 */
interface ProductSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{
    /**
     * Get attributes list.
     *
     * @return \CAT\Custom\Api\Data\ProductInterface[]
     */
    public function getItems();

    /**
     * Set attributes list.
     *
     * @param \CAT\Custom\Api\Data\ProductInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}