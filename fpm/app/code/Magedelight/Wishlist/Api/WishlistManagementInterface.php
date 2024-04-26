<?php
namespace Magedelight\Wishlist\Api;

/**
 * Interface WishlistManagementInterface
 * @api
 */
interface WishlistManagementInterface
{

    /**
     * Return Wishlist items.
     *
     * @param int $customerId
     * @return mixed
     */
    public function getWishlistForCustomer($customerId);

    /**
     * Return Added wishlist item.
     *
     * @param int $customerId
     * @param int $productId
     * @param int|null $vendorId Default: null
     * @return mixed
     *
     */
    public function addWishlistForCustomer($customerId, $productId, $vendorId = null);

    /**
     * Remove wishlist item.
     *
     * @param int $customerId
     * @param int $itemId
     * @return mixed
     *
     */
    public function removeWishlistItem($customerId, $itemId);

    /**
     * Remove wishlist item.
     *
     * @param int $customerId
     * @param int $productId
     * @return mixed
     *
     */
    public function removeWishlistItemProductWise($customerId, $productId);

    /**
     * Remove All wishlist items.
     *
     * @param int $customerId
     * @return mixed
     *
     */
    public function removeAllWishlistForCustomer($customerId);

    /**
     * Add wishlist item to cart.
     *
     * @api
     * @param int $customerId
     * @param int $itemId
     * @param int $qty
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function moveToCart($customerId, $itemId, $qty);

    /**
     * Share wishlist.
     *
     * @api
     * @param int $customerId
     * @param int $itemId
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function moveToWishlist($customerId, $itemId);
}
