<?php

namespace MDC\MobileBanner\Model\Data\Homepage\AfterTopCategory;

use MDC\MobileBanner\Api\Data\Homepage\AfterTopCategory\BannerInterface;

class Banner extends \Magento\Framework\DataObject implements BannerInterface
{
    public function getImagePath()
    {
        return $this->getData(self::IMAGE_PATH);
    }

    public function setImagePath($imagePath)
    {
        return $this->setData(self::IMAGE_PATH, $imagePath);
    }

    public function getPageType()
    {
        return $this->getData(self::PAGE_TYPE);
    }

    public function setPageType($pageType)
    {
        return $this->setData(self::PAGE_TYPE, $pageType);
    }

    public function getDataId()
    {
        return $this->getData(self::DATA_ID);
    }

    public function setDataId($dataId)
    {
        return $this->setData(self::DATA_ID, $dataId);
    }
}
