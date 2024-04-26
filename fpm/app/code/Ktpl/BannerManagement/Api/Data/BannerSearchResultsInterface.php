<?php
/**
 * php version 7.2.17
 */
namespace Ktpl\BannerManagement\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

/**
 * Interface for banner search results.
 *
 * @api
 * @since 100.0.2
 */
interface BannerSearchResultsInterface extends SearchResultsInterface
{
    /**
     * Get banner list.
     *
     * @return Ktpl\BannerManagement\Api\Data\BannerInterface[]
     */
    public function getItems();

    /**
     * Set banner list.
     *
     * @param  Ktpl\BannerManagement\Api\Data\BannerInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
