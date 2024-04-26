<?php declare(strict_types=1);

namespace Ktpl\Pushnotification\Model;

use Ktpl\Pushnotification\Api\Data\KtplAbandonCartDataInterface;

use Magento\Framework\Model\AbstractModel;
use Ktpl\Pushnotification\Model\ResourceModel\KtplAbandonCart as KtplAbandonCartresource;

class KtplAbandonCart extends AbstractModel implements KtplAbandonCartDataInterface
{

    protected function _construct()
    {
        $this->_init(KtplAbandonCartresource::class);
    }

    /**
     * Get id
     * @return int|null
     */
    public function getId()
    {
        return $this->getData(\Ktpl\Pushnotification\Api\Data\KtplAbandonCartDataInterface::ID);
    }

    /**
     * Set id
     * @param int $id
     * @return \Ktpl\Pushnotification\Api\Data\KtplAbandonCartDataInterface
     */
    public function setId($id)
    {
        return $this->setData(self::ID, $id);
    }

    /**
     * Get quoteId
     * @return int|null
     */
    public function getQuoteId()
    {
        return $this->getData(\Ktpl\Pushnotification\Api\Data\KtplAbandonCartDataInterface::QUOTE_ID);
    }
    /**
     * Set quoteId
     * @param int $quoteId
     * @return \Ktpl\Pushnotification\Api\Data\KtplAbandonCartDataInterface
     */
    public function setQuoteId($quoteId)
    {
        return $this->setData(self::QUOTE_ID, $quoteId);
    }

    /**
     * Get quoteItem
     * @return int|null
     */
    public function getQuoteItemId()
    {
        return $this->getData(\Ktpl\Pushnotification\Api\Data\KtplAbandonCartDataInterface::QUOTE_ITEM_ID);

    }

    /**
     * Set quoteItem
     * @param int $quoteItem
     * @return \Ktpl\Pushnotification\Api\Data\KtplAbandonCartDataInterface
     */
    public function setQuoteItemId($quoteItemId)
    {
        return $this->setData(self::QUOTE_ITEM_ID, $quoteItemId);
    }

    /**
     * Get deviceTokenId
     * @return string|null
     */
    public function getDeviceTokenId()
    {
        return $this->getData(\Ktpl\Pushnotification\Api\Data\KtplAbandonCartDataInterface::DEVICE_TOKEN_ID);
    }

    /**
     * Set an deviceTokenId.
     * @param string $deviceTokenId
     * @return \Ktpl\Pushnotification\Api\Data\KtplAbandonCartDataInterface
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
        return $this->getData(\Ktpl\Pushnotification\Api\Data\KtplAbandonCartDataInterface::ADDED_AT);
    }

    /**
     * Set added_at
     * @param string $addedAt
     * @return \Ktpl\Pushnotification\Api\Data\KtplAbandonCartDataInterface
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
        return $this->getData(\Ktpl\Pushnotification\Api\Data\KtplAbandonCartDataInterface::STORE_ID);
    }

    /**
     * Set storeId
     * @param int $storeId
     * @return \Ktpl\Pushnotification\Api\Data\KtplAbandonCartDataInterface
     */
    public function setStoreId($storeId)
    {
        return $this->setData(self::STORE_ID, $storeId);
    }   
    
}
