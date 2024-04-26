<?php
namespace Magedelight\Catalog\Model;

use Magedelight\Catalog\Api\Data\ProductRenderSearchResultsInterface;
use Magento\Framework\Api\SearchResults;

class ProductRenderSearchResults extends SearchResults implements ProductRenderSearchResultsInterface
{
    const KEY_WISHLIST_PRODUCT_IDS = 'wishlist_product_ids';
    const KEY_SORT_ORDERS = 'sort_orders';
    const KEY_DIRECTION = 'direction';
    const KEY_FILTERS = 'filters';

    /**
     * Returns array of items
     *
     * @return \Magento\Catalog\Api\Data\ProductInterface[]
     */
    public function getItems()
    {
        return $this->_get('items');
    }

    /**
     * Returns array of items
     *
     * @param \Magento\Catalog\Api\Data\ProductInterface[] $items
     * @return $this
     */
    public function setItems(array $items)
    {
        return $this->setData('items', $items);
    }

    /**
     * Get Wishlist Ids
     *
     * @return array
     */
    public function getWishlistIds()
    {
        return $this->_get(self::KEY_WISHLIST_PRODUCT_IDS);
    }

    /**
     * Set Wishlist Ids
     *
     * @param array $productIds
     * @return $this
     */
    public function setWishlistIds(array $productIds)
    {
        return $this->setData(self::KEY_WISHLIST_PRODUCT_IDS, $productIds);
    }

    /**
     * Get Sort Orders
     *
     * @return string
     */
    public function getSortOrders()
    {
        return $this->_get(self::KEY_SORT_ORDERS);
    }

    /**
     * Set Sort Orders
     *
     * @param array $sortOrders
     * @return $this
     */
    public function setSortOrders(array $sortOrders)
    {
        return $this->setData(self::KEY_SORT_ORDERS, $sortOrders);
    }

    /**
     * Get Direction
     *
     * @return array
     */
    public function getDirection()
    {
        return $this->_get(self::KEY_DIRECTION);
    }

    /**
     * Set Direction
     *
     * @param \Magedelight\Catalog\Api\Data\SortOrderInterface[] $direction
     * @return $this
     */
    public function setDirection(array $direction)
    {
        return $this->setData(self::KEY_DIRECTION, $direction);
    }

    /**
     * Get Filters
     *
     * @return \Magedelight\Catalog\Api\Data\FilterInterface[] $filters
     */
    public function getFilters()
    {
        return $this->_get(self::KEY_FILTERS);
    }

    /**
     * Set Filters
     *
     * @param \Magedelight\Catalog\Api\Data\FilterInterface[] $filters
     * @return $this
     */
    public function setFilters(array $filters)
    {
        return $this->setData(self::KEY_FILTERS, $filters);
    }
}
