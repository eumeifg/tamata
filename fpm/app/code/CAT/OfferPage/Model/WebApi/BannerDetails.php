<?php

namespace CAT\OfferPage\Model\WebApi;

use CAT\OfferPage\Api\Data\BannerDetailsInterface;
use Magento\Framework\DataObject;

class BannerDetails extends DataObject implements BannerDetailsInterface
{
    /**
     * {@inheritdoc}
     */
    public function getPageType()
    {
        return $this->getData(BannerDetailsInterface::PAGE_TYPE);
    }

    /**
     * {@inheritdoc}
     */
    public function setPageType($pageType)
    {
        return $this->setData(BannerDetailsInterface::PAGE_TYPE, $pageType);
    }

    /**
     * {@inheritdoc}
     */
    public function getDataId()
    {
        return $this->getData(BannerDetailsInterface::DATA_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setDataId($dataId)
    {
        return $this->setData(BannerDetailsInterface::DATA_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function getImageUrl()
    {
        return $this->getData(BannerDetailsInterface::IMAGE_URL);
    }

    /**
     * {@inheritdoc}
     */
    public function setImageUrl($imageUrl)
    {
        return $this->setData(BannerDetailsInterface::IMAGE_URL, $imageUrl);
    }

    /**
     * {@inheritdoc}
     */
    public function getLayout()
    {
        return $this->getData(BannerDetailsInterface::LAYOUT);
    }

    /**
     * {@inheritdoc}
     */
    public function setLayout($layout)
    {
        return $this->setData(BannerDetailsInterface::LAYOUT, $layout);
    }

    /**
     * {@inheritdoc}
     */
    public function getImages()
    {
        return $this->getData(BannerDetailsInterface::IMAGES);
    }

    /**
     * {@inheritdoc}
     */
    public function setImages($images)
    {
        return $this->setData(BannerDetailsInterface::IMAGES, $images);
    }
}
