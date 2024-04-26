<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Catalog
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Catalog\Model\VendorProduct;

class DataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /**
     * Loaded data cache
     *
     * @var array
     */
    protected $loadedData;

    /**
     * Data persistor
     *
     * @var \Magento\Framework\App\Request\DataPersistorInterface
     */
    protected $dataPersistor;

    /**
     * constructor
     *
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param \Magedelight\Catalog\Model\ResourceModel\VendorProduct\CollectionFactory $collectionFactory
     * @param \Magento\Framework\App\Request\DataPersistorInterface $dataPersistor
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        \Magedelight\Catalog\Model\ResourceModel\VendorProduct\CollectionFactory $collectionFactory,
        \Magento\Framework\App\Request\DataPersistorInterface $dataPersistor,
        array $meta = [],
        array $data = []
    ) {
        $this->dataPersistor = $dataPersistor;
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collection = $collectionFactory->create();
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
        /** @var \Magedelight\Catalog\Model\VendorProduct $VendorProduct */
        foreach ($items as $VendorProduct) {
            $this->loadedData[$VendorProduct->getId()] = $VendorProduct->getData();
        }
        $data = $this->dataPersistor->get('md_vendor_product');
        if (!empty($data)) {
            $VendorProduct = $this->collection->getNewEmptyItem();
            $VendorProduct->setData($data);
            $this->loadedData[$VendorProduct->getId()] = $VendorProduct->getData();
            $this->dataPersistor->clear('md_vendor_product');
        }
        return $this->loadedData;
    }
}
