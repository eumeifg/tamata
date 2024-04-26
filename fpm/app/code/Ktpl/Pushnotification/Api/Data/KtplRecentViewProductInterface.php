<?php declare(strict_types=1);

namespace Ktpl\Pushnotification\Api\Data;

interface KtplRecentViewProductInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{

    const ID = 'id';
    const PRODUCT_ID = 'product_id';
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
     * @return \Ktpl\Pushnotification\Api\Data\KtplRecentViewProductInterface
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
     * @return \Ktpl\Pushnotification\Api\Data\KtplRecentViewProductInterface
     */
    public function setDeviceTokenId($deviceTokenId);

    /**
     * Get productId
     * @return int|null
     */
    public function getProductId();

    /**
     * Set productId
     * @param int $productId
     * @return \Ktpl\Pushnotification\Api\Data\KtplRecentViewProductInterface
     */
    public function setProductId($productId);

    /**
     * Get storeId
     * @return int
     */
    public function getStoreId();

    /**
     * Set storeId
     * @param int $storeId
     * @return \Ktpl\Pushnotification\Api\Data\KtplRecentViewProductInterface
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
     * @return \Ktpl\Pushnotification\Api\Data\KtplRecentViewProductInterface
     */
    public function setAddedAt($addedAt);  

    
}

