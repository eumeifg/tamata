<?php
namespace Ktpl\Productslider\Api\Data;

/**
 * @api
 */

interface ProductsliderSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{

    /**
     * Get Productslider list.
     * @return \Ktpl\Productslider\Api\Data\ProductsliderInterface[]
     */
    public function getItems();

    /**
     * Set name list.
     * @param \Ktpl\Productslider\Api\Data\ProductsliderInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
