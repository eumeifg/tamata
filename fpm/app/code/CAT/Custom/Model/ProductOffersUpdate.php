<?php

namespace CAT\Custom\Model;

use CAT\Custom\Model\ResourceModel\Automation\CollectionFactory;
use Magedelight\Catalog\Model\Product;
use Magedelight\Catalog\Model\ProductRequestManagement;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use CAT\Custom\Model\Source\Option;
use Magedelight\OffersImportExport\Helper\Data;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magedelight\Catalog\Model\ProductFactory;
use Magedelight\Catalog\Model\ProductStoreFactory;
use Magedelight\Catalog\Model\ProductWebsiteFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\File\Csv;
use Magento\Framework\Filesystem;
use Magento\Store\Model\StoreManagerInterface;
use Magedelight\Catalog\Api\ProductStoreRepositoryInterface;
use Magedelight\Catalog\Api\ProductWebsiteRepositoryInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Psr\Log\LoggerInterface;

class ProductOffersUpdate
{
    const ERROR_CODE_DUPLICATE_ENTRY = 23000;
    const CORE_PRODUCT_TYPE_DEFAULT = 'simple';
    const CORE_PRODUCT_TYPE_ASSOCIATED = 'config-simple';

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var Filesystem
     */
    protected $_filesystem;

    /**
     * @var Csv
     */
    protected $csv;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var ProductFactory
     */
    protected $vendorProductFactory;

    /**
     * @var ProductStoreFactory
     */
    protected $vendorProductStoreFactory;

    /**
     * @var ProductWebsiteFactory
     */
    protected $vendorProductWebsiteFactory;

    /**
     * @var Configurable|\Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable
     */
    protected $catalogProductTypeConfigurable;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var ProductWebsiteRepositoryInterface
     */
    protected $productWebsiteRepository;

    /**
     * @var ProductStoreRepositoryInterface
     */
    protected $productStoreRepository;

    /**
     * @var ManagerInterface
     */
    protected $_eventManager;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var DateTime
     */
    protected $_date;

    /**
     * @var LoggerInterface
     */
    protected $_logger;

    /**
     * @param CollectionFactory $collectionFactory
     * @param Filesystem $filesystem
     * @param Csv $csv
     * @param Data $helper
     * @param ProductRepositoryInterface $productRepository
     * @param ProductFactory $vendorProductFactory
     * @param ProductStoreFactory $vendorProductStoreFactory
     * @param ProductWebsiteFactory $vendorProductWebsiteFactory
     * @param \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable $catalogProductTypeConfigurable
     * @param StoreManagerInterface $storeManager
     * @param ProductWebsiteRepositoryInterface $productWebsiteRepository
     * @param ProductStoreRepositoryInterface $productStoreRepository
     * @param ManagerInterface $eventManager
     * @param ScopeConfigInterface $scopeConfig
     * @param DateTime $date
     * @param LoggerInterface $logger
     */
    public function __construct(
        CollectionFactory                                                          $collectionFactory,
        Filesystem                                                                 $filesystem,
        Csv                                                                        $csv,
        Data                                                                       $helper,
        ProductRepositoryInterface                                                 $productRepository,
        ProductFactory                                                             $vendorProductFactory,
        ProductStoreFactory                                                        $vendorProductStoreFactory,
        ProductWebsiteFactory                                                      $vendorProductWebsiteFactory,
        \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable $catalogProductTypeConfigurable,
        StoreManagerInterface                                                      $storeManager,
        ProductWebsiteRepositoryInterface                                          $productWebsiteRepository,
        ProductStoreRepositoryInterface                                            $productStoreRepository,
        ManagerInterface                                                           $eventManager,
        ScopeConfigInterface                                                       $scopeConfig,
        DateTime                                                                   $date,
        LoggerInterface                                                            $logger
    )
    {
        $this->collectionFactory = $collectionFactory;
        $this->_filesystem = $filesystem;
        $this->csv = $csv;
        $this->helper = $helper;
        $this->productRepository = $productRepository;
        $this->vendorProductFactory = $vendorProductFactory;
        $this->vendorProductStoreFactory = $vendorProductStoreFactory;
        $this->vendorProductWebsiteFactory = $vendorProductWebsiteFactory;
        $this->catalogProductTypeConfigurable = $catalogProductTypeConfigurable;
        $this->storeManager = $storeManager;
        $this->productWebsiteRepository = $productWebsiteRepository;
        $this->productStoreRepository = $productStoreRepository;
        $this->_eventManager = $eventManager;
        $this->scopeConfig = $scopeConfig;
        $this->_date = $date;
        $this->_logger = $logger;
    }

    /**
     * @return void
     * @throws LocalizedException
     */
    public function productOffersUpdate()
    {
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter('entity_type', ['eq' => Option::PRODUCT_OFFERS_KEYWORD]);
        $collection->addFieldToFilter('status', ['eq' => 0]);

        if ($collection->getSize()) {
            $productOffer = $collection->getFirstItem();
            $batchCounter = $productOffer->getBatchCounter();

            if (empty($batchCounter) || $batchCounter == 0) {
                $productOffer->setStartedAt($this->_date->gmtDate('Y-m-d H:i:s'))->save();
                $this->_logger->info('after the started time save');
            }
            
            $csvName = $productOffer->getFileName();
            $additionalInfo = json_decode($productOffer->getAdditionalInfo());
            $websiteId = $additionalInfo->website_id;
            $vendorId = $additionalInfo->vendor_id;

            $csvfile = $this->_filesystem->getDirectoryRead(DirectoryList::MEDIA)
                    ->getAbsolutePath('cat/' . Option::PRODUCT_OFFERS_KEYWORD . '/') . $csvName;

            $importProductRawData = $this->csv->getData($csvfile);

            $headers = $importProductRawData[0];
            unset($importProductRawData[0]);

            $predefinedHeaders = array_keys($this->helper->getCSVFields());

            $fields = $predefinedHeaders;
            /*$updatedDataCount = 0;
            $newDataCount = 0;*/
            $productIds = $parentIds = $products = [];
            $batchLimit = $this->scopeConfig->getValue(Option::PRODUCT_OFFER_UPDATE_BATCH_LIMIT); // Admin Configuration dynamic data

            $fileCounter = count($importProductRawData);
            $remainingCount = $fileCounter - (int)$batchCounter;
            $limit = ($remainingCount > $batchLimit) ? $batchLimit : $remainingCount;

            /*foreach ($importProductRawData as $rowIndex => $dataRow) {*/
            for ($i = ($batchCounter + 1); $i <= ($batchCounter + $limit); $i++) {
                $rowData = [];
                foreach ($fields as $key => $field) {
                    if (in_array($field, ['status'])) {
                        $rowData[$field] = (isset($importProductRawData[$i][$key])) ? $importProductRawData[$i][$key] : null;
                    } else {
                        $rowData[$field] = (!empty($importProductRawData[$i][$key])) ? trim($importProductRawData[$i][$key]) : null;
                    }
                }

                $row = $this->_getImportRow($rowData, $predefinedHeaders);
                //echo "<pre>"; print_r($row); echo "</pre>"; //die();
                if (!empty($row)) {
                    try {
                        /* Get Vendor Product Id by sku. */
                        $vendorSku = $row['vendor_sku'];
                        $vendorProductId = 0;
                        $vendorProductObject = $this->vendorProductFactory->create()->getCollection()
                            ->addFieldToFilter('main_table.vendor_id', ['eq' => $row['vendor_id']])
                            ->addFieldToFilter('vendor_sku', ['eq' => $row['vendor_sku']])->getFirstItem();
                        $vendorProductId = ($vendorProductObject) ? $vendorProductObject->getVendorProductId() : '';

                        if ($vendorProductId) {
                            if (!isset($row['marketplace_product_id'])) {
                                continue;
                            }

                            $marketpalceId = $row['marketplace_product_id'];

                            /*Verify marketpalce sku and vendor sku.*/
                            $vendorProduct = $this->vendorProductFactory->create()->getCollection();
                            /*$this->addFilterToMap('vendor_product_id', 'main_table.vendor_product_id');*/
                            $vendorProduct->addFieldToFilter('main_table.vendor_product_id', ['eq' => $vendorProductId])
                                ->addFieldToFilter('marketplace_product_id', ['eq' => $marketpalceId]);

                            $vendorProductData = [];

                            if ($vendorProduct) {
                                $vendorProductItem = $vendorProduct->getFirstItem();
                                $vendorProductData = $vendorProductItem->getData();
                                $vendorProductDataCount = count($vendorProductData);
                            }

                            if ($vendorProductDataCount != '0') {
                                $vendorProductItem->setData('condition', 1);

                                if (array_key_exists('qty', $row)) {
                                    $vendorProductItem->setData('qty', $row['qty']);
                                }
                                $productWebModel = $this->productWebsiteRepository
                                    ->getProductWebsiteData($vendorProductItem->getId());

                                $product = $this->productRepository
                                    ->getById($vendorProductItem->getMarketplaceProductId(), true);

                                $categoryIds = $product->getCategoryIds();
                                $row['category_id'] = 0;
                                if (!empty($categoryIds)) {
                                    $row['category_id'] = $categoryIds[0];
                                }

                                if ($productWebModel) {
                                    $this->setWebsiteDetails($productWebModel, $row);
                                    $productWebModel->setData('website_id', $websiteId);
                                    $this->productWebsiteRepository->save($productWebModel);

                                    $storeId = $vendorProductItem->getDefaultStoreId($websiteId);

                                    $productStoreModel = $this->productStoreRepository->getProductStoreData($vendorProductItem->getId(), $storeId);

                                    if ($productStoreModel) {
                                        $productStoreModel->setData('vendor_product_id', $vendorProductItem->getId());
                                        $productStoreModel->setData('store_id', $storeId);

                                        $this->productStoreRepository->save($productStoreModel);
                                    }

                                    $vendorProductItem->save();
                                    /*$updatedDataCount++;*/
                                    if ($row['status'] == Product::STATUS_LISTED) {
                                        $products[$vendorProductItem->getVendorProductId()]['qty'] = $vendorProductItem->getQty();
                                        $products[$vendorProductItem->getVendorProductId()]['marketplace_product_id'] = $vendorProductItem->getMarketplaceProductId();

                                        $productIds[] = $vendorProductItem->getMarketplaceProductId();
                                        if ($vendorProductItem->getParentId()) {
                                            $parentIds[] = $vendorProductItem->getParentId();
                                        }
                                    }
                                }
                                $eventParams = [
                                    'marketplace_product_id' => $vendorProductItem->getMarketplaceProductId(),
                                    'old_qty' => (int)$vendorProductItem->getQty(),
                                    'vendor_product' => $vendorProductItem
                                ];

                                $this->_eventManager->dispatch('vendor_product_list_after', $eventParams);
                            } else {
                                $model = $this->vendorProductFactory->create();
                                $stores = $this->storeManager->getStores();
                                $totalStoreRecordsCreated = 0;
                                foreach ($stores as $store) {
                                    $storeId = $store["store_id"];
                                    $row['store_id'] = $storeId;
                                    $row['condition'] = 1;
                                    $model->setData($row);
                                    $model->save();

                                    $eventParams = [
                                        'marketplace_product_id' => $model->getMarketplaceProductId(),
                                        'old_qty' => (int)$model->getQty(),
                                        'vendor_product' => $model
                                    ];

                                    $this->_eventManager->dispatch('vendor_product_list_after', $eventParams);

                                    $model->unsetData();
                                }
                                /*$newDataCount++;*/
                            }
                        }
                    } catch (\Exception $e) {
                        if ($e->getCode() === self::ERROR_CODE_DUPLICATE_ENTRY
                            && preg_match('#SQLSTATE\[23000\']: [^:]+: 1062[^\d]#', $e->getMessage())
                        ) {
                            throw new LocalizedException(__('Vendor offer already exists.'));
                        }
                        throw new LocalizedException(__($e->getMessage()));
                    }
                }
            }
            /* Updating the process data */
            if (($batchCounter + $limit) == $fileCounter) {
                $processedTime = $this->_date->gmtDate('Y-m-d H:i:s');
                $status = !empty($productOffer->getIndexerData()) ? 2 : 1;
                $productOffer->setStatus($status);
                $productOffer->setProcessedAt($processedTime);
            }
            $productOffer->setBatchCounter($batchCounter + $limit);
            /* Updating the process data */
            $this->_logger->info('=============== STARTS ==============>');
            $this->_logger->info('Product Ids =====>' . print_r(count($productIds), true));
            if (!empty($productIds)) {
                $eventParams = [
                    'marketplace_product_ids' => array_unique($productIds),
                    'vendor_products' => $products,
                    'parent_ids' => array_unique($parentIds)];
                if (!empty($productOffer->getIndexerData())) {
                    $this->_logger->info('=============== PRODUCT INDEXER NOT EMPTY ==============>');
                    $indexerData = json_decode($productOffer->getIndexerData(), true);

                    $this->_logger->info('Encoded Data OLD - ' . json_encode($indexerData));
                    $this->_logger->info('Encoded Data NEW - ' . json_encode($eventParams));
                    $indexerNewData = array_merge_recursive($eventParams, $indexerData);
                    $this->_logger->info('Encoded Data COMBINED - ' . json_encode($indexerNewData));
                    $productOffer->setIndexerData(json_encode($indexerNewData));
                } else {
                    $this->_logger->info('=============== PRODUCT INDEXER IS EMPTY ==============>');
                    $encodedData = json_encode($eventParams);
                    $this->_logger->info('Encoded Data When Empty - ' . $encodedData);
                    $productOffer->setIndexerData($encodedData);
                }
                //$this->_eventManager->dispatch('vendor_product_mass_list_after', $eventParams);
            }
            $this->_logger->info('=============== ENDS ==============>');
            $productOffer->save();
        }
    }

    public function _getImportRow($row, $predefinedHeaders)
    {
        $data = [];
        /* strip whitespace from the beginning and end of each row */
        foreach ($row as $k => $v) {
            $row[$k] = trim($v);
        }

        $coreProduct = $this->productRepository->get($row['marketplace_sku']);
        $data['marketplace_product_id'] = $coreProduct->getId();
        $data['name'] = $coreProduct->getName();
        $data['type_id'] = $coreProduct->getTypeId();
        $data['attribute_set_id'] = $coreProduct->getAttributeSetId();
        $coreProduct->unsetData();

        /* Format special_from_date */
        $special_from_date = $this->_formateDateValue($row['special_from_date']);
        $row['special_from_date'] = ($special_from_date) ? $special_from_date : null;

        /* Format special_to_date */
        $special_to_date = $this->_formateDateValue($row['special_to_date']);
        $row['special_to_date'] = ($special_to_date) ? $special_to_date : null;
        $fields = $predefinedHeaders;
        unset($row['marketplace_sku']);
        foreach ($fields as $field) {
            if (array_key_exists($field, $row)) {
                $data[$field] = $row[$field];
            }
        }
        return $data;
    }

    protected function _formateDateValue($date = '')
    {
        if ($date) {
            $newDate = strtr($date, '/', '-');
            $convertdate = (new \DateTime())->setTimestamp(strtotime($newDate));
            $d = $convertdate->format(\Magento\Framework\Stdlib\DateTime::DATE_PHP_FORMAT);
            return $d;
        }
        return false;
    }

    protected function saveStoreData($data, $vendorProductId, $productType, $vendorSku = null)
    {
        $storeDataArray = [];
        $availableWebsites = $data['website_id'];
        $data['has_variants'] = 0;
        $vendorProductIdsWithStores = [];
        $eachWebsiteId = $data['website_id'];
        $storeCode = $data['store_id'];
        $stores = $this->storeManager->getStores();
        foreach ($stores as $store) {
            $vendorProductStore = $this->vendorProductStoreFactory->create();
            $vendorProductStore->setData('vendor_product_id', $vendorProductId);
            $vendorProductStore->setData('store_id', $store->getId());
            $vendorProductStore->setData('website_id', $eachWebsiteId);
            $vendorProductIdsWithStores[] = ['vendor_product_id' => $vendorProductId, 'store_id' => $store->getId()];

            try {
                $vendorProductStore->save();
            } catch (\Exception $e) {
                throw new \Exception($e->getMessage());
            }
        }
    }

    protected function saveWebsiteData($data, $product, $vendorProductId, $productType, $vendorSku)
    {
        $websiteDataArray = [];
        $isAssociate = false;
        $categoryIds = $product->getCategoryIds();

        $availableWebsites = $data['website_id'];
        $data['category_id'] = 0;
        if (!empty($categoryIds)) {
            $data['category_id'] = $categoryIds[0];
        }
        if ($productType == self::CORE_PRODUCT_TYPE_ASSOCIATED) {
            $isAssociate = true;
        }
        if ($product->getTypeId() == Configurable::TYPE_CODE) {
            $productType = Configurable::TYPE_CODE;
            $isAssociate = true;
        }

        $websiteCode = $data['website_id'];
        $vendorProductWebsite = $this->vendorProductWebsiteFactory->create();

        $this->setWebsiteDetails($vendorProductWebsite, $data);
        $vendorProductWebsite->setData('vendor_product_id', $vendorProductId);
        $vendorProductWebsite->setData('vendor_id', $data['vendor_id']);
        $vendorProductWebsite->setData('website_id', $data['website_id']);
        $vendorProductWebsite->setData('status', $data['status']);

        try {
            $vendorProductWebsite->save();
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    /**
     * @param $vendorProductWebsite
     * @param $websiteDataArray
     * @return void
     */
    protected function setWebsiteDetails(
        $vendorProductWebsite,
        $websiteDataArray
    )
    {
        foreach ($this->getVendorOfferWebsiteFields() as $field) {
            if (array_key_exists($field, $websiteDataArray)) {
                if (in_array($field, ['price', 'special_price']) &&
                    ($websiteDataArray[$field] && (int)$websiteDataArray[$field] === 0)
                ) {
                    $vendorProductWebsite->setData($field, $websiteDataArray[$field]);
                } elseif (!empty($websiteDataArray[$field])) {
                    $vendorProductWebsite->setData($field, $websiteDataArray[$field]);
                } elseif (in_array($field, ['status', 'category_id']) && ($websiteDataArray[$field] != '' || $websiteDataArray[$field] == 0)) {
                    $vendorProductWebsite->setData($field, $websiteDataArray[$field]);
                } elseif (!$websiteDataArray[$field]) {
                    $vendorProductWebsite->setData($field, null);
                }
            }
        }
    }

    /**
     * @return string[]
     */
    protected function getVendorOfferWebsiteFields()
    {
        return [
            'status',
            'condition',
            'price',
            'special_price',
            'special_from_date',
            'special_to_date',
            'reorder_level',
            'warranty_type',
            'category_id'
        ];
    }
}
