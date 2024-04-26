<?php
/**
 * php version 7.2.17
 */
namespace Ktpl\TopCategory\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

/**
 * Interface for Top Category search results.
 *
 * @api
 * @since 100.0.2
 */
interface TopCategorySearchResultsInterface extends SearchResultsInterface
{
    /**
     * Get banner list.
     *
     * @return Ktpl\TopCategory\Api\Data\TopCategoryInterface[]
     */
    public function getItems();

    /**
     * Set banner list.
     *
     * @param  Ktpl\TopCategory\Api\Data\TopCategoryInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
