<?php

namespace CAT\OfferPage\Model;

use Magento\Ui\DataProvider\Modifier\PoolInterface;
use Magento\Ui\DataProvider\ModifierPoolDataProvider;
use CAT\OfferPage\Model\ResourceModel\OfferPage\CollectionFactory;
use Magento\Framework\App\Request\DataPersistorInterface;
use CAT\OfferPage\Model\ResourceModel\OfferPageFactory;

class OfferPageDataProvider extends ModifierPoolDataProvider
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
     * @param OfferPageFactory $offerPageFactory
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
        OfferPageFactory $offerPageFactory,
        array $meta = [],
        array $data = [],
        PoolInterface $pool = null
    ) {
        $this->collection = $offerPageCollectionFactory->create();
        $this->dataPersistor = $dataPersistor;
        $this->offerPageFactory = $offerPageFactory->create();
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
        /** @var \CAT\OfferPage\Model\OfferPage $offerPage */
        foreach ($items as $offerPage) {
            !empty($offerPage->getAdditionalInfo()) ? $offerPage->setData('dynamic_rows', json_decode($offerPage->getAdditionalInfo())) : $offerPage->getAdditionalInfo();
            $this->loadedData[$offerPage->getOfferpageId()] = $offerPage->getData();
        }

        $data = $this->dataPersistor->get('offerpage');
        if (!empty($data)) {
            $sliderObj = $this->collection->getNewEmptyItem();
            $sliderObj->setData($data);
            $this->loadedData[$offerPage->getOfferpageId()] = $offerPage->getData();
            $this->dataPersistor->clear('offerpage');
        }
        return $this->loadedData;
    }
}
