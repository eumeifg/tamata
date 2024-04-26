<?php declare(strict_types=1);

namespace Ktpl\Pushnotification\Model\Data;

use Ktpl\Pushnotification\Api\Data\KtplRecentViewProductInterface;


class KtplRecentViewProduct extends \Magento\Framework\Api\AbstractExtensibleObject implements KtplRecentViewProductInterface
{
    /**
     * Get id
     * @return int|null
     */
    public function getId()
    {
        return $this->_get(self::ID);
    }

    /**
     * Set id
     * @param int $id
     * @return \Ktpl\Pushnotification\Api\Data\KtplRecentViewProductInterface
     */
    public function setId($id)
    {
        return $this->setData(self::ID, $id);
    }

    /**
     * Get deviceTokenId
     * @return string|null
     */
    public function getDeviceTokenId()
    {
        return $this->_get(self::DEVICE_TOKEN_ID);
    }

    /**
     * Set an deviceTokenId.
     * @param string $deviceTokenId
     * @return \Ktpl\Pushnotification\Api\Data\KtplRecentViewProductInterface
     */
    public function setDeviceTokenId($deviceTokenId) {
        return $this->setData(self::DEVICE_TOKEN_ID, $deviceTokenId);
    }    

    /**
     * Get added_at
     * @return string|null
     */
    public function getAddedAt()
    {
        return $this->_get(self::ADDED_AT);
    }

    /**
     * Set added_at
     * @param string $addedAt
     * @return \Ktpl\Pushnotification\Api\Data\KtplRecentViewProductInterface
     */
    public function setAddedAt($addedAt)
    {
        return $this->setData(self::ADDED_AT, $addedAt);
    }
   
    /**
     * Get storeId
     * @return int|null
     */
    public function getStoreId()
    {
        return $this->_get(self::STORE_ID);
    }

    /**
     * Set storeId
     * @param int $storeId
     * @return \Ktpl\Pushnotification\Api\Data\KtplRecentViewProductInterface
     */
    public function setStoreId($storeId)
    {
        return $this->setData(self::STORE_ID, $storeId);
    }

    /**
     * Get productId
     * @return int|null
     */
    public function getProductId()
    {
        return $this->_get(self::PRODUCT_ID);
    }

    /**
     * Set productId
     * @param int $productId
     * @return \Ktpl\Pushnotification\Api\Data\KtplRecentViewProductInterface
     */
    public function setProductId($productId)
    {
        return $this->setData(self::PRODUCT_ID, $productId);
    }
    
}
