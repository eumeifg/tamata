<?php
namespace Ktpl\DealsZone\Model;

use Ktpl\DealsZone\Api\Data\DealZoneInterface;

class DealZone extends \Magento\Framework\DataObject implements DealZoneInterface
{
    /**
     * @return int
     */
    public function getEntityId()
    {
        return $this->getData(DealZoneInterface::ENTITY_ID);
    }

    /**
     * @param  int $entityId
     * @return $this
     */
    public function setEntityId($entityId)
    {
        return $this->setData(DealZoneInterface::ENTITY_ID,$entityId);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->getData(DealZoneInterface::NAME);
    }

    /**
     * @param  string $name
     * @return $this
     */
    public function setName($name)
    {
        return $this->setData(DealZoneInterface::NAME,$name);
    }

    /**
     * @return string
     */
    public function getImage()
    {
        return $this->getData(DealZoneInterface::IMAGE);
    }

    /**
     * @param  string $image
     * @return $this
     */
    public function setImage($image)
    {
        return $this->setData(DealZoneInterface::IMAGE,$image);
    }

    /**
     * @return string
     */
    public function getUrlKey()
    {
        return $this->getData(DealZoneInterface::URL_KEY);
    }

    /**
     * @param  string $urlKey
     * @return $this
     */
    public function setUrlKey($urlKey)
    {
        return $this->setData(DealZoneInterface::URL_KEY,$urlKey);
    }

    /**
     * @return string
     */
    public function getUrlPath()
    {
        return $this->getData(DealZoneInterface::URL_PATH);
    }

    /**
     * @param  string $path
     * @return $this
     */
    public function setUrlPath($path)
    {
        return $this->setData(DealZoneInterface::URL_PATH,$path);
    }

    /**
     * @return string
     */
    public function getThumbnail()
    {
        return $this->getData(DealZoneInterface::THUMBNAIL);
    }

    /**
     * @param  string $thumbnail
     * @return $this
     */
    public function setThumbnail($thumbnail)
    {
        return $this->setData(DealZoneInterface::THUMBNAIL,$thumbnail);
    }

    /**
     * @return string|null
     */
    public function getDiscountLabel()
    {
        return $this->getData(DealZoneInterface::DISCOUNT_LABEL);
    }

    /**
     * @param  string|null $label
     * @return $this
     */
    public function setDiscountLabel($label)
    {
        return $this->setData(DealZoneInterface::DISCOUNT_LABEL,$label);
    }

    /**
     * @return string|null
     */
    public function getDealzoneImage()
    {
        return $this->getData(DealZoneInterface::DEALZONE_IMG);
    }

    /**
     * @param  string|null $path
     * @return $this
     */
    public function setDealzoneImage($path)
    {
        return $this->setData(DealZoneInterface::DEALZONE_IMG,$path);
    }

}
