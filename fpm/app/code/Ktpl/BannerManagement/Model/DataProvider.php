<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Ktpl\BannerManagement\Model;

use Ktpl\BannerManagement\Helper\Image as HelperImage;
use Ktpl\BannerManagement\Model\ResourceModel\Banner\CollectionFactory;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Ui\DataProvider\Modifier\PoolInterface;

/**
 * Class DataProvider
 */
class DataProvider extends \Magento\Ui\DataProvider\ModifierPoolDataProvider
{
    /**
     * @var \Ktpl\BannerManagement\Model\BannerRepository
    protected $bannerRepository;

    /**
     * @var DataPersistorInterface
     */
    protected $dataPersistor;

    /**
     * @var array
     */
    protected $loadedData;


    /**
     * Constructor
     *
     * @param string                 $name
     * @param string                 $primaryFieldName
     * @param string                 $requestFieldName
     * @param CollectionFactory      $bannerCollectionFactory
     * @param DataPersistorInterface $dataPersistor
     * @param array                  $meta
     * @param array                  $data
     * @param PoolInterface|null     $pool
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $bannerCollectionFactory,
        DataPersistorInterface $dataPersistor,
        HelperImage $imageHelper,
        \Magento\Store\Model\StoreManagerInterface $url,
        array $meta = [],
        array $data = [],
        PoolInterface $pool = null
    ) {
        $this->collection = $bannerCollectionFactory->create();
        $this->dataPersistor = $dataPersistor;
        $this->url = $url;
        $this->imageHelper = $imageHelper;
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data, $pool);
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
         $mediaUrl=$this->url->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
        /** @var \Ktpl\BannerManagement\Model\Banner $banner */
        foreach ($items as $banner) {
            $bannerData = $banner->getData();
            $sliderIds=$banner->getSliderIds();
            $bannerData['sliders_ids']=$sliderIds;

            if (isset($bannerData['image'])) {
                unset($bannerData['image']);
                $bannerData['image'][0]['name'] = $banner->getData('image');
                $bannerData['image'][0]['url'] = $mediaUrl.$this->imageHelper->getBaseMediaPath(HelperImage::TEMPLATE_MEDIA_PATH) ."/" .$banner->getData('image');
            }

            $this->loadedData[$banner->getBannerId()]['general'] = $bannerData;
        }
        $data = $this->dataPersistor->get('banner');
        if (!empty($data)) {
            $bannerObj = $this->collection->getNewEmptyItem();
            $bannerObj->setData($data);
            $this->loadedData[$banner->getBannerId()] = $banner->getData();
            $this->dataPersistor->clear('banner');
        }
        return $this->loadedData;
    }
}
