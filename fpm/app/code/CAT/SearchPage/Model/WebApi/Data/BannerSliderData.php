<?php

namespace CAT\SearchPage\Model\WebApi\Data;

use CAT\SearchPage\Api\Data\BannerSliderDataInterface;
use Magento\Framework\DataObject;

class BannerSliderData extends DataObject implements BannerSliderDataInterface
{

    /**
     * @inheritDoc
     */
    public function getBannerUrl()
    {
        return $this->getData(self::BANNER_URL);
    }

    /**
     * @inheritDoc
     */
    public function setBannerUrl(string $bannerUrl)
    {
        return $this->setData(self::BANNER_URL, $bannerUrl);
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
    public function setPageType(string $pageType)
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
    public function setDataId(int $dataId)
    {
        return $this->setData(self::DATA_ID, $dataId);
    }

    /**
     * @inheritDoc
     */
    public function getCategoryTitle()
    {
        return $this->getData(self::CATEGORY_TITLE);
    }

    /**
     * @inheritDoc
     */
    public function setCategoryTitle(string $categoryTitle)
    {
        return $this->setData(self::CATEGORY_TITLE, $categoryTitle);
    }

    /**
     * @inheritDoc
     */
    public function getShowViewAllLink()
    {
        return $this->getData(self::SHOW_VIEW_ALL_LINK);
    }

    /**
     * @inheritDoc
     */
    public function setShowViewAllLink(bool $showViewAllLink)
    {
        return $this->setData(self::SHOW_VIEW_ALL_LINK, $showViewAllLink);
    }

    /**
     * @inheritDoc
     */
    public function getViewAllLinkId()
    {
        return $this->getData(self::VIEW_ALL_LINK_CATEGORY_ID);
    }

    /**
     * @inheritDoc
     */
    public function setViewAllLinkId(int $viewAllLinkId)
    {
        return $this->setData(self::VIEW_ALL_LINK_CATEGORY_ID, $viewAllLinkId);
    }

    /**
     * @inheritDoc
     */
    public function getProducts()
    {
        return $this->getData(self::PRODUCTS);
    }

    /**
     * @inheritDoc
     */
    public function setProducts(array $products)
    {
        return $this->setData(self::PRODUCTS, $products);
    }
}
