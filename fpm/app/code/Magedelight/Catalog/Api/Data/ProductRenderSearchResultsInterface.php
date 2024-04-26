<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magedelight\Catalog\Api\Data;

/**
 * Dto that holds render information about products
 */
interface ProductRenderSearchResultsInterface
{
    /**
     * Get list of products rendered information
     *
     * @return \Magento\Catalog\Api\Data\ProductInterface[]
     */
    public function getItems();

    /**
     * Set list of products rendered information
     *
     * @api
     * @param \Magento\Catalog\Api\Data\ProductInterface[] $items
     * @return $this
     */
    public function setItems(array $items);

    /**
     * Get search criteria
     *
     * @return \Magento\Framework\Api\SearchCriteria
     */
    public function getSearchCriteria();

    /**
     * Set search criteria
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return $this
     */
    public function setSearchCriteria(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);

    /**
     * Get total count
     *
     * @return int
     */
    public function getTotalCount();

    /**
     * Set total count
     *
     * @param int $count
     * @return $this
     */
    public function setTotalCount($count);

    /**
     * Get Wishlist Ids
     *
     * @return array
     */
    public function getWishlistIds();

    /**
     * Set Wishlist Ids
     *
     * @param array $productIds
     * @return $this
     */
    public function setWishlistIds(array $productIds);

    /**
     * Get list of products rendered information
     *
     * @return \Magedelight\Catalog\Api\Data\SortOrderInterface[]
     */
    public function getSortOrders();

    /**
     * Set list of products rendered information
     *
     * @api
     * @param array $sortOrders
     * @return $this
     */
    public function setSortOrders(array $sortOrders);

    /**
     * Get list of products rendered information
     *
     * @return \Magedelight\Catalog\Api\Data\SortOrderInterface[]
     */
    public function getDirection();

    /**
     * Set list of products rendered information
     *
     * @api
     * @param \Magedelight\Catalog\Api\Data\SortOrderInterface[] $direction
     * @return $this
     */
    public function setDirection(array $direction);

    /**
     * Get Filters
     *
     * @return \Magedelight\Catalog\Api\Data\FilterInterface[] $filters
     */
    public function getFilters();

    /**
     * Set set Filters
     *
     * @api
     * @param \Magedelight\Catalog\Api\Data\FilterInterface[] $filters
     * @return $this
     */
    public function setFilters(array $filters);
}
