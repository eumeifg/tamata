<?php

namespace MDC\PickupPoints\Model;

use MDC\PickupPoints\Model\ResourceModel\Pickups\CollectionFactory;

class DataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{

    /**
     * @var array
     */
    protected $loadedData;

    // @codingStandardsIgnoreStart
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $pickupsCollectionFactory,
        array $meta = [],
        array $data = []
    ) {
        $this->collection = $pickupsCollectionFactory->create();
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    // @codingStandardsIgnoreEnd

    public function getData()
    {

        if (isset($this->loadedData)) {
            return $this->loadedData;
        }

        $items = $this->collection->getItems();

        foreach ($items as $pickups) {
            $this->loadedData[$pickups->getPickupPointId()] = $pickups->getData();
        }
        return $this->loadedData;
    }
}