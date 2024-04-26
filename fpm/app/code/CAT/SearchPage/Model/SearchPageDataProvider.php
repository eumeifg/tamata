<?php

namespace CAT\SearchPage\Model;

use Magento\Ui\DataProvider\Modifier\PoolInterface;
use Magento\Ui\DataProvider\ModifierPoolDataProvider;
use CAT\SearchPage\Model\ResourceModel\SearchPage\CollectionFactory;
use Magento\Framework\App\Request\DataPersistorInterface;
use CAT\SearchPage\Model\ResourceModel\SearchPageFactory;

class SearchPageDataProvider extends ModifierPoolDataProvider
{
    protected $collection;

    /**
     * @var DataPersistorInterface
     */
    protected $dataPersistor;

    /**
     * @var
     */
    protected $offerPageFactory;

    /**
     * @var array
     */
    protected $loadedData;

    /**
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $offerPageCollectionFactory
     * @param DataPersistorInterface $dataPersistor
     * @param SearchPageFactory $searchPageFactory
     * @param array $meta
     * @param array $data
     * @param PoolInterface|null $pool
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $offerPageCollectionFactory,
        DataPersistorInterface $dataPersistor,
        SearchPageFactory $searchPageFactory,
        array $meta = [],
        array $data = [],
        PoolInterface $pool = null
    ) {
        $this->collection = $offerPageCollectionFactory->create();
        $this->dataPersistor = $dataPersistor;
        $this->offerPageFactory = $searchPageFactory->create();
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data, $pool);
        $this->meta = $this->prepareMeta($this->meta);
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
        /** @var \CAT\SearchPage\Model\SearchPage $searchPage */
        foreach ($items as $searchPage) {
            !empty($searchPage->getAdditionalInfo()) ? $searchPage->setData('dynamic_rows', json_decode($searchPage->getAdditionalInfo())) : $searchPage->getAdditionalInfo();
            $this->loadedData[$searchPage->getSearchPageId()] = $searchPage->getData();
        }

        $data = $this->dataPersistor->get('search_page');
        if (!empty($data)) {
            $sliderObj = $this->collection->getNewEmptyItem();
            $sliderObj->setData($data);
            $this->loadedData[$searchPage->getSearchPageId()] = $searchPage->getData();
            $this->dataPersistor->clear('search_page');
        }
        return $this->loadedData;
    }
}
