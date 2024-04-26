<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Vendor
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Vendor\Model\VendorWebsite;

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
     * @param \Magedelight\Vendor\Model\ResourceModel\VendorWebsite\CollectionFactory $collectionFactory
     * @param \Magento\Framework\App\Request\DataPersistorInterface $dataPersistor
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        \Magedelight\Vendor\Model\ResourceModel\VendorWebsite\CollectionFactory $collectionFactory,
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
        /*if (isset($this->loadedData)) {
            return $this->loadedData;
        }
        $items = $this->collection->getItems();
        /** @var \Magedelight\Catalog\Model\ProductWebsite $ProductWebsite *\/
        foreach ($items as $ProductWebsite) {
            $this->loadedData[$ProductWebsite->getId()] = $ProductWebsite->getData();

        }
        $data = $this->dataPersistor->get('md_vendor_website');
        if (!empty($data)) {
            $ProductWebsite = $this->collection->getNewEmptyItem();
            $ProductWebsite->setData($data);
            $this->loadedData[$ProductWebsite->getId()] = $ProductWebsite->getData();
            $this->dataPersistor->clear('md_vendor_product_website');
        }
        return $this->loadedData;*/
    }
}
