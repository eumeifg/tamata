<?php declare(strict_types=1);

namespace Ktpl\Pushnotification\Api\Data;

interface KtplAbandonCartDataInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{

    const ID = 'id';
    const QUOTE_ID = 'quote_id';
    const QUOTE_ITEM_ID = 'quote_item_id';
    const DEVICE_TOKEN_ID = 'device_token_id';
    const STORE_ID = 'store_id';
    const ADDED_AT = 'added_at';

    /**
     * Get id
     * @return int|null
     */
    public function getId();

    /**
     * Set id
     * @param int $id
     * @return \Ktpl\Pushnotification\Api\Data\KtplAbandonCartDataInterface
     */
    public function setId($id);

    /**
     * @param string deviceTokenId
     * @return string|null
     */
    public function getDeviceTokenId();

    /**
     * Set deviceTokenId.
     * @param string $deviceTokenId
     * @return \Ktpl\Pushnotification\Api\Data\KtplAbandonCartDataInterface
     */
    public function setDeviceTokenId($deviceTokenId);

    /**
     * Get quoteId
     * @return int|null
     */
    public function getQuoteId();

    /**
     * Set quoteId
     * @param int $quoteId
     * @return \Ktpl\Pushnotification\Api\Data\KtplAbandonCartDataInterface
     */
    public function setQuoteId($quoteId);

    /**
     * Get quoteItem
     * @return int|null
     */
    public function getQuoteItemId();

    /**
     * Set quoteItem
     * @param int $quoteItem
     * @return \Ktpl\Pushnotification\Api\Data\KtplAbandonCartDataInterface
     */
    public function setQuoteItemId($quoteItemId);

    /**
     * Get storeId
     * @return int
     */
    public function getStoreId();

    /**
     * Set storeId
     * @param int $storeId
     * @return \Ktpl\Pushnotification\Api\Data\KtplAbandonCartDataInterface
     */
    public function setStoreId($storeId);

    /**
     * Get addedAt
     * @return string|null
     */
    public function getAddedAt();

    /**
     * Set addedAt
     * @param string $addedAt
     * @return \Ktpl\Pushnotification\Api\Data\KtplAbandonCartDataInterface
     */
    public function setAddedAt($addedAt);  

    
}

