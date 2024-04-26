<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace MDC\MobileBanner\Model\Banner;

use MDC\MobileBanner\Model\ResourceModel\Banner\CollectionFactory;
use Magento\Framework\App\Request\DataPersistorInterface;

class DataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{

    protected $dataPersistor;

    protected $collection;

    protected $loadedData;

    /**
     * Constructor
     *
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $collectionFactory
     * @param DataPersistorInterface $dataPersistor
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        DataPersistorInterface $dataPersistor,
        \Magento\Framework\Serialize\Serializer\Json $serialize,
        array $meta = [],
        array $data = []
    ) {
        $this->collection = $collectionFactory->create();
        $this->dataPersistor = $dataPersistor;
        $this->serialize = $serialize;
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
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

        foreach ($items as $model) {
            $this->loadedData[$model->getId()] = $model->getData();
            $serializeData = $model->getSectionDetails();

            if ($serializeData){
                $value = $this->serialize->unserialize($serializeData);
                if (!empty($value)) {
                    foreach ($value as $item) {
                        $this->loadedData[$model->getId()]['dynamic_rows_container'][] = $item;

                    }
                }

            }

        }
        $data = $this->dataPersistor->get('mdc_mobilebanner_banner');
        
        if (!empty($data)) {
            $model = $this->collection->getNewEmptyItem();
            $model->setData($data);

            $this->loadedData[$model->getId()] = $model->getData();

            $this->dataPersistor->clear('mdc_mobilebanner_banner');
        }
        return $this->loadedData;
    }
}
