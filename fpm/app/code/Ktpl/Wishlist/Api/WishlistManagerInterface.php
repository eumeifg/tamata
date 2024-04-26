<?php
/**
 * Copyright © Krish TechnoLabs, All rights reserved.
 */
declare(strict_types=1);

namespace Ktpl\Wishlist\Api;

interface WishlistManagerInterface
{
	/**
     * Returns string
     *
     * @api
     * @param string $val
     * @return string
     */
    public function add($val);

    /**
     * Returns string
     *
     * @api
     * @param string $val
     * @return string
     */
    public function remove($val);

    /**
     * Return Wishlist items.
     *
     * @param int $customerId
     * @return array
     */
    public function getWishlistForCustomer($customerId);

    /**
     * Return Added wishlist info.
     *
     * @param int $customerId
     * @return array
     *
     */
    public function getWishlistInfo($customerId);

    /**
     * Return Added wishlist item.
     *
     * @param int $customerId
     * @param int $productId
     * @return array
     *
     */
    public function addWishlistForCustomer($customerId,$productId);

    /**
     * Return Added wishlist item.
     *
     * @param int $customerId
     * @param int $wishlistId
     * @return status
     *
     */
    public function deleteWishlistForCustomer($customerId,$wishlistItemId);
}