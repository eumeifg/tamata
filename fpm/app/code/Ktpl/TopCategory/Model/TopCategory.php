<?php
namespace Ktpl\TopCategory\Model;

use Ktpl\TopCategory\Api\Data\TopCategoryInterface;

class TopCategory extends \Magento\Framework\DataObject implements TopCategoryInterface
{
    /**
     * @return int
     */
    public function getEntityId()
    {
        return $this->getData(TopCategoryInterface::ENTITY_ID);
    }

    /**
     * @param  int $entityId
     * @return $this
     */
    public function setEntityId($entityId)
    {
        return $this->setData(TopCategoryInterface::ENTITY_ID,$entityId);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->getData(TopCategoryInterface::NAME);
    }

    /**
     * @param  string $name
     * @return $this
     */
    public function setName($name)
    {
        return $this->setData(TopCategoryInterface::NAME,$name);
    }

    /**
     * @return string
     */
    public function getImage()
    {
        return $this->getData(TopCategoryInterface::IMAGE);
    }

    /**
     * @param  string $image
     * @return $this
     */
    public function setImage($image)
    {
        return $this->setData(TopCategoryInterface::IMAGE,$image);
    }

    /**
     * @return string
     */
    public function getUrlKey()
    {
        return $this->getData(TopCategoryInterface::URL_KEY);
    }

    /**
     * @param  string $urlKey
     * @return $this
     */
    public function setUrlKey($urlKey)
    {
        return $this->setData(TopCategoryInterface::URL_KEY,$urlKey);
    }

    /**
     * @return string
     */
    public function getUrlPath()
    {
        return $this->getData(TopCategoryInterface::URL_PATH);
    }

    /**
     * @param  string $path
     * @return $this
     */
    public function setUrlPath($path)
    {
        return $this->setData(TopCategoryInterface::URL_PATH,$path);
    }

    /**
     * @return string
     */
    public function getThumbnail()
    {
        return $this->getData(TopCategoryInterface::THUMBNAIL);
    }

    /**
     * @param  string $thumbnail
     * @return $this
     */
    public function setThumbnail($thumbnail)
    {
        return $this->setData(TopCategoryInterface::THUMBNAIL,$thumbnail);
    }
}
