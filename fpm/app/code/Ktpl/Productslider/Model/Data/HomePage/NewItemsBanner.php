<?php

namespace Ktpl\Productslider\Model\Data\HomePage;

use Ktpl\Productslider\Api\Data\HomePage\NewItemsBannerInterface;

class NewItemsBanner extends \Magento\Framework\DataObject implements NewItemsBannerInterface
{

    /**
     * @inheritDoc
     */
    public function getImagePath()
    {
        return $this->getData(self::IMAGE_PATH);
    }

    /**
     * @inheritDoc
     */
    public function setImagePath($imagePath)
    {
        return $this->setData(self::IMAGE_PATH, $imagePath);
    }

    /**
     * @inheritDoc
     */
    public function getPageType()
    {
        return $this->getData(self::PAGE_TYPE);
    }

    /**
     * @inheritDoc
     */
    public function setPageType($pageType)
    {
        return $this->setData(self::PAGE_TYPE, $pageType);
    }

    /**
     * @inheritDoc
     */
    public function getDataId()
    {
        return $this->getData(self::DATA_ID);
    }

    /**
     * @inheritDoc
     */
    public function setDataId($dataId)
    {
        return $this->setData(self::DATA_ID, $dataId);
    }
}
