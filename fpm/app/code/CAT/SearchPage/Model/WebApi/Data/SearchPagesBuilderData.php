<?php

namespace CAT\SearchPage\Model\WebApi\Data;

use CAT\SearchPage\Api\Data\SearchPagesBuilderDataInterface;
use Magento\Framework\DataObject;

class SearchPagesBuilderData extends DataObject implements SearchPagesBuilderDataInterface
{

    /**
     * @inheritDoc
     */
    public function getBannerSlider(): array
    {
        return $this->getData('banner_slider');
    }

    /**
     * @inheritDoc
     */
    public function setBannerSlider(array $bannerSlider): SearchPagesBuilderDataInterface
    {
        return $this->setData('banner_slider', $bannerSlider);
    }
}
