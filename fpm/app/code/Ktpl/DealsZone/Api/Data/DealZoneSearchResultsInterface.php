<?php
/**
 * php version 7.2.17
 */
namespace Ktpl\DealsZone\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

/**
 * Interface for Deals Zone search results.
 *
 * @api
 */
interface DealZoneSearchResultsInterface extends SearchResultsInterface
{
    /**
     * Get Deal Zone Items
     *
     * @return Ktpl\DealsZone\Api\Data\DealZoneInterface[]
     */
    public function getItems();

    /**
     * Set Deal Zone Items.
     *
     * @param  Ktpl\DealsZone\Api\Data\DealZoneInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
