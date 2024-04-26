<?php

namespace CAT\Address\Model;

use Magento\Ui\DataProvider\Modifier\PoolInterface;
use Magento\Ui\DataProvider\ModifierPoolDataProvider;
use Magento\Framework\App\Request\DataPersistorInterface;
use CAT\Address\Model\ResourceModel\Collection\CollectionFactory;
use CAT\Address\Model\ResourceModel\RomCityFactory;

class CityDataProvider extends ModifierPoolDataProvider
{
    /**
     * @var ResourceModel\Collection\Collection
     */
    protected $collection;

    /**
     * @var DataPersistorInterface
     */
    protected $dataPersistor;

    /**
     * @var RomCityFactory
     */
        protected $romCityFactory;

    /**
     * @var array
     */
    protected $loadedData;

    /**
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param DataPersistorInterface $dataPersistor
     * @param RomCityFactory $romCityFactory
     * @param CollectionFactory $cityCollectionFactory
     * @param array $meta
     * @param array $data
     * @param PoolInterface|null $pool
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        DataPersistorInterface $dataPersistor,
        RomCityFactory $romCityFactory,
        CollectionFactory $cityCollectionFactory,
        array $meta = [],
        array $data = [],
        PoolInterface $pool = null
    ) {
        $this->dataPersistor = $dataPersistor;
        $this->collection = $cityCollectionFactory->create();
        $this->romCityFactory = $romCityFactory->create();
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data, $pool);
    }

    /**
     * @param array $meta
     * @return array
     */
    public function prepareMeta(array $meta) {
        return $meta;
    }

    public function getData()
    {
        if (isset($this->loadedData)) {
            return $this->loadedData;
        }
        $items = $this->collection->getItems();
        foreach ($items as $city) {
            $this->loadedData[$city->getEntityId()] = $city->getData();
        }
        $data = $this->dataPersistor->get('current_address_city');
        if (!empty($data)) {
            $sliderObj = $this->collection->getNewEmptyItem();
            $sliderObj->setData($data);
            $this->loadedData[$city->getEntityId()] = $city->getData();
            $this->dataPersistor->clear('current_address_city');
        }
        return $this->loadedData;
    }
}
