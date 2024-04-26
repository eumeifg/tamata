<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Ktpl\BannerManagement\Model;

use Ktpl\BannerManagement\Model\ResourceModel\Slider\CollectionFactory;
use Ktpl\BannerManagement\Model\ResourceModel\SliderFactory;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Ui\DataProvider\Modifier\PoolInterface;

/**
 * Class DataProvider
 */
class SliderDataProvider extends \Magento\Ui\DataProvider\ModifierPoolDataProvider
{
    /**
     * @var \Ktpl\BannerManagement\Model\ResourceModel\Slider\Collection
     */
    protected $collection;

    /**
     * @var DataPersistorInterface
     */
    protected $dataPersistor;

    /**
     * @var array
     */
    protected $loadedData;

    /**
     * @param string                 $name
     * @param string                 $primaryFieldName
     * @param string                 $requestFieldName
     * @param CollectionFactory      $pageCollectionFactory
     * @param DataPersistorInterface $dataPersistor
     * @param array                  $meta
     * @param array                  $data
     * @param PoolInterface|null     $pool
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $sliderCollectionFactory,
        SliderFactory $sliderFactory,
        DataPersistorInterface $dataPersistor,
        array $meta = [],
        array $data = [],
        PoolInterface $pool = null
    ) {
        $this->collection = $sliderCollectionFactory->create();
        $this->dataPersistor = $dataPersistor;
        $this->sliderFactory = $sliderFactory->create();
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data, $pool);
        $this->meta = $this->prepareMeta($this->meta);
    }

    /**
     * Prepares Meta
     *
     * @param  array $meta
     * @return array
     */
    public function prepareMeta(array $meta)
    {
        return $meta;
    }

    /**
     * Get data
     *
     * @return array
     */
    public function getData()
    {
        if (isset($this->loadedData)) {
            return $this->loadedData;
        }
        $items = $this->collection->getItems();
        /**
         * @var $page \Ktpl\BannerManagement\Model\Slider
        */
        foreach ($items as $slider) {
            $sliderBannerData=$this->sliderFactory->getBannersSliderID($slider);
            $this->loadedData[$slider->getSliderId()]= $slider->getData();
            foreach ($sliderBannerData as $bannerKey => $bannerValue) {
                $this->loadedData[$slider->getSliderId()]['bannermanagement_banner_slider_listing'][$bannerKey]['banner_id']=$bannerValue;
            }
        }
        

        $data = $this->dataPersistor->get('slider');
        if (!empty($data)) {
            $sliderObj = $this->collection->getNewEmptyItem();
            $sliderObj->setData($data);
            $this->loadedData[$slider->getSliderId()] = $slider->getData();
            $this->dataPersistor->clear('slider');
        }
        return $this->loadedData;
    }
}
