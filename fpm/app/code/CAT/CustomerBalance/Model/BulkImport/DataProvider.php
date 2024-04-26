<?php

namespace CAT\CustomerBalance\Model\BulkImport;

use CAT\CustomerBalance\Model\ResourceModel\BulkImport\CollectionFactory;

class DataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        array $meta = [], array $data = []
    ) {
        $this->collection = $collectionFactory->create();
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    public function getData()
    {
        if (isset($this->loadedData)) {
            return $this->loadedData;
        }
        $items = $this->collection->getItems();

        $this->loadedData = [];

        foreach ($items as $contact) {
            $this->loadedData[$contact->getId()]['import'] = $contact->getData();
        }

        return $this->loadedData;
    }
}