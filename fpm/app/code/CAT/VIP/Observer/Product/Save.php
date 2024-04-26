<?php

namespace CAT\VIP\Observer\Product;

use Magedelight\Catalog\Model\Product;
use Magedelight\Catalog\Model\ProductRequestManagement;
use Magento\Catalog\Model\Product\Visibility;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\Event\ObserverInterface;


class Save implements ObserverInterface
{  

    const ERROR_CODE_DUPLICATE_ENTRY = 23000;
    protected $messageManager;
    protected $dataHelper;
    protected $_request;
    protected $vipProductsFactory;

    public function __construct( \Magento\Framework\App\RequestInterface $request,\CAT\VIP\Helper\Data $dataHelper, \CAT\VIP\Model\VIPProductsFactory $vipProductsFactory,\Magento\Framework\Message\ManagerInterface $messageManager) {
        $this->_request = $request;
        $this->dataHelper = $dataHelper;
        $this->messageManager = $messageManager;
        $this->vipProductsFactory = $vipProductsFactory;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $vipOfferData = $this->_request->getParam('vip-offer', []);
        if ($this->dataHelper->getConfig('vip/general/enable') && !empty($vipOfferData)) {
           

            $product = $observer->getProduct();

            $productId = $product->getId();

            if (!empty($vipOfferData)) {

                try {
                    $vipOfferData['product_id'] = $productId;
                    if (array_key_exists('vendor_product_id', $vipOfferData)) {
                        $this->_updateVendorOffer($product, $vipOfferData);
                    } else {
                            $this->_createVendorProduct($product, $vipOfferData);
                    }
                } catch (\Zend_Db_Statement_Exception $e) {
                    if ($e->getCode() == self::ERROR_CODE_DUPLICATE_ENTRY) {
                        $this->messageManager->addError(
                            __(
                                'VIP offer already exists with vendor. Please try another vendor.'
                            )
                        );
                    } else {
                        $this->messageManager->addError($e->getMessage());
                    }
                } catch (\Magento\Framework\Exception\LocalizedException $e) {
                    $this->messageManager->addError($e->getMessage());
                } catch (\RuntimeException $e) {
                    $this->messageManager->addError($e->getMessage());
                } catch (\Exception $e) {
                    $this->messageManager->addException(
                        $e,
                        __('Something went wrong while saving record.' . $e->getMessage())
                    );
                }
            }
        }
    }

    /**
     *
     * @param object $product \Magentp\Product\Model\Product
     * @param $postData
     * @return int productId
     * @throws \Exception
     */
    public function _updateVendorOffer($product, $postData)
    {
        $vendorOfferData = $postData['vendor-offer'];
        try {
            if (array_key_exists('vendor_product_id', $vendorOfferData)) {
                /* Only for child products in case of Configurable product. */
                $parentId = $this->getProductData($product->getId());
                /* Only for child products in case of Configurable product. */
                $vendorProduct = $this->vendorProductRepository->getById($vendorOfferData['vendor_product_id']);
                $vendorProductStore = $this->productStoreRepository->getProductStoreData(
                    $vendorOfferData['vendor_product_id'],
                    $vendorOfferData['store_id']
                );
                $vendorProductWebsite = $this->productWebsiteRepository->getProductWebsiteData(
                    $vendorOfferData['vendor_product_id'],
                    $vendorOfferData['website_id']
                );
                $websiteId = (array_key_exists('website_id', $vendorOfferData)) ? $vendorOfferData['website_id'] :
                    $this->_storeManager->getDefaultStoreView()->getWebsiteId();

                if (!$vendorProduct->getId()) {
                    throw new \Exception(__('This vendor product no longer exists.'));
                }

                $vendorProduct->setData('vendor_sku', $vendorOfferData['vendorsku']);
                $vendorProduct->setData('qty', $vendorOfferData['qty']);

                if (empty($vendorProduct->getAttributeSetId())) {
                    $vendorProduct->setData('attribute_set_id', $product->getData('attribute_set_id'));
                }

                /* Only for child products in case of Configurable product. */
                if (!empty($parentId) && $product->getTypeId() != Configurable::TYPE_CODE) {
                    $vendorProduct->setParentId($parentId);
                    $vendorOfferData['vendor_id'] = $vendorProduct->getVendorId();
                    $parentVendorProductId = $this->createVendorConfigurableProductIfNotExists(
                        $postData,
                        $product,
                        $parentId,
                        $vendorOfferData,
                        $vendorProduct->getCategoryId()
                    );
                }
                /* Only for child products in case of Configurable product. */

                /* Set website data. */
                $websiteFields = $this->getWebsiteDataColumns();
                foreach ($websiteFields as $websiteField) {
                    if (array_key_exists($websiteField, $vendorOfferData) && $vendorOfferData[$websiteField]) {
                        if (in_array($websiteField, ['special_price','special_from_date','special_to_date'])) {
                            if ($vendorOfferData['special_price'] != '' && (int)$vendorOfferData['special_price'] > 0) {
                                $vendorProductWebsite->setData($websiteField, $vendorOfferData[$websiteField]);
                            }
                        } else {
                            $vendorProductWebsite->setData($websiteField, $vendorOfferData[$websiteField]);
                        }
                    } else {
                        $vendorProductWebsite->setData($websiteField, null);
                    }
                }
                /* Set website data. */

                /* Set store data. */
                $storeFields = $this->getStoreDataColumns();

                foreach ($storeFields as $storeField) {
                    if (array_key_exists($storeField, $vendorOfferData) && $vendorOfferData[$storeField]) {
                        $vendorProductStore->setData($storeField, $vendorOfferData[$storeField]);
                    }
                    /* else {
                         $vendorProductStore->setData($storeField, NULL);
                     }*/
                }

                /* Set store data. */

                try {
                    $vendorProduct->save();
                    $vendorProductId = $vendorProduct->getId();
                } catch (\Exception $e) {
                    throw new \Exception($e->getMessage());
                }

                try {
                    $vendorProductWebsite->save();

                    if (isset($vendorProductStore) && $vendorProductStore == true) {
                        $vendorProductStore->save();
                    }

                    /* Event for single store save. */
                    $this->_eventManager->dispatch(
                        'vendor_product_store_save_after',
                        [
                            'vendor_product_store_data' => [
                                'vendor_product_id' => $vendorProductId,
                                'store_id' => $vendorOfferData['store_id']
                            ]
                        ]
                    );
                    /* Event for single store save. */

                    /* Event for all stores save. */
                    $eventParams = [
                        'post_data'   => $postData,
                        'vendor_product_ids_with_stores' => [
                            0 => [
                            'vendor_product_id' => $vendorProductId,
                            'store_id' => $vendorOfferData['store_id']
                            ]
                        ]
                    ];
                    $this->_eventManager->dispatch('vendor_product_stores_save_after', $eventParams);

                    $eventParams = [
                        'marketplace_product_id' => $vendorProduct->getMarketplaceProductId(),
                        'old_qty'   => $vendorProduct->getQty(),
                        'vendor_product' => $vendorProduct
                    ];
                    $this->_eventManager->dispatch('vendor_product_list_after', $eventParams);
                    /* Event for all stores save. */
                } catch (\Exception $e) {
                    throw new \Exception($e->getMessage());
                }
            }
            return $vendorProduct->getId();
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    /**
     * @return array
     */
    public function getStoreDataColumns()
    {
        return $storeDataColumns = ['condition_note', 'warranty_description'];
    }

    /**
     * @return array
     */
    public function getWebsiteDataColumns()
    {
        return $websiteDataColumns = [
            'price',
            'special_price',
            'special_from_date',
            'special_to_date',
            'condition',
            'reorder_level',
            'warranty_type',
            'category_id'
        ];
    }

    /**
     *
     * @param object $product \Magentp\Product\Model\Product
     * @param array $data
     * @param boolean $isconfig
     * @return int productId
     * @throws \Exception
     */
    public function _createVendorProduct($product, $data)
    {
        $vendorOfferData = $data;
        
        try {
                // Create vip Product
                $model = $this->vipProductsFactory->create();
                $model->setData($vendorOfferData);
                $model->save();
            } catch (\Magento\Framework\Exception\AlreadyExistsException $e) {
                $this->messageManager->addError(
                    __(
                        'VIP offer already exists with vendor. Please try another vendor.'
                    )
                );
            } catch (\Exception $e) {
                throw new \Exception($e->getMessage());
            }
        
    }

    /**
     * @param string $productId
     * @return boolean
     */
    protected function checkIfOfferExistsOnProduct($productId = '')
    {
        if ($productId) {
            $offer = $this->vendorProductFactory->create()->getCollection()->addFieldToFilter(
                'marketplace_product_id',
                $productId
            )->getFirstItem();
            return ($offer && $offer->getId()) ? true : false;
        }
    }

    /**
     * @param $data
     * @param $vendorProductId
     * @param $productType
     * @param $vendorSku
     * @param array $vendorOfferData
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function saveStoreData($data, $vendorProductId, $productType, $vendorSku = null, $vendorOfferData = [])
    {
        $storeDataArray = [];
        if (array_key_exists('store_data', $data)) {
            $storeDataArray = $this->jsonDecoder->decode($data['store_data']);
        }

        $availableWebsites = (is_array($data['website_ids'])) ? $data['website_ids'] :
            explode(",", $data['website_ids']);
        if (!empty($vendorOfferData) && array_key_exists('website_id', $vendorOfferData)) {
            $availableWebsites = explode(",", $vendorOfferData['website_id']);
        }

        if (!empty($storeDataArray) && !empty($availableWebsites)) {
            $stores = $this->_storeManager->getStores();
            $websites = $this->_storeManager->getWebsites();
            $vendorProductIdsWithStores = [];
            foreach ($availableWebsites as $websiteId) {
                foreach ($stores as $store) {
                    $eachWebsiteId = $this->_storeManager->getStore($store->getId())->getWebsiteId();
                    if (!in_array(0, $availableWebsites) && !in_array($eachWebsiteId, $availableWebsites)):
                        continue;
                    endif;
                    $storeCode = (in_array($store->getId(), array_keys($storeDataArray))) ? $store->getId() : 'default';
                    $vendorProductStore = $this->vendorProductStoreFactory->create();
                    if (!$data['has_variants'] || $data['has_variants'] == '') {
                        $vendorProductStore->setData('condition_note', $storeDataArray[$storeCode]['condition_note']);
                        $vendorProductStore->setData(
                            'warranty_description',
                            $storeDataArray[$storeCode]['warranty_description']
                        );
                    }
                    $vendorProductStore->setData('vendor_product_id', $vendorProductId);
                    $vendorProductStore->setData('store_id', $store->getId());
                    $vendorProductStore->setData('website_id', $eachWebsiteId);
                    $vendorProductIdsWithStores[] = [
                        'vendor_product_id' => $vendorProductId,
                        'store_id' => $store->getId()
                    ];
                    /* Event for single store save. */
                    $this->_eventManager->dispatch(
                        'vendor_product_store_save_after',
                        [
                            'vendor_product_store_data' => [
                                'vendor_product_id' => $vendorProductId,
                                'store_id' => $store->getId()
                            ]
                        ]
                    );
                    /* Event for single store save. */
                    try {
                        $vendorProductStore->save();
                    } catch (\Exception $e) {
                        throw new \Exception($e->getMessage());
                    }
                }
            }
            /* Event for all stores save. */
            $this->_eventManager->dispatch(
                'vendor_product_stores_save_after',
                [
                'vendor_product_ids_with_stores' => $vendorProductIdsWithStores,
                'post_data' => $data
                ]
            );
            /* Event for all stores save. */
        }
    }

    /**
     *
     * @param $data
     * @param $product
     * @param $vendorProductId
     * @param $productType
     * @param $vendorSku
     * @param array $vendorOfferData
     * @throws \Exception
     */
    protected function saveWebsiteData(
        $data,
        $product,
        $vendorProductId,
        $productType,
        $vendorSku = null,
        $vendorOfferData = []
    ) {
        $websiteDataArray = $availableWebsites = [];
        $isAssociate = false;
        $availableWebsites = $data['website_ids'];
        if (array_key_exists('website_data', $data) && $data['website_data'] != '') {
            $websiteDataArray = $this->jsonDecoder->decode($data['website_data']);
            $availableWebsites = $data['website_ids'];
        }
        $availableWebsites = (is_array($availableWebsites)) ? $availableWebsites :
            explode(",", $availableWebsites);
        if (!empty($vendorOfferData) && array_key_exists('website_id', $vendorOfferData)) {
            $availableWebsites = explode(",", $vendorOfferData['website_id']);
        }

        if ($productType == ProductRequestManagement::CORE_PRODUCT_TYPE_ASSOCIATED) {
            $isAssociate = true;
        }
        if ($product->getTypeId() == Configurable::TYPE_CODE) {
            $productType = Configurable::TYPE_CODE;
            $isAssociate = true;
        }
        if (!empty($websiteDataArray) && !empty($availableWebsites)) {
            $websites = $this->_storeManager->getWebsites();

            foreach ($websites as $websiteId => $website) {
                if (!in_array(0, $availableWebsites) && !in_array($websiteId, $availableWebsites)):
                    continue;
                endif;
                $websiteCode = (in_array($websiteId, array_keys($websiteDataArray))) ? $websiteId : 'default';
                $vendorProductWebsite = $this->vendorProductWebsiteFactory->create();

                $this->setWebsiteDetails(
                    $vendorProductWebsite,
                    $websiteDataArray,
                    $websiteCode,
                    $vendorSku,
                    $isAssociate,
                    $productType
                );
                $vendorProductWebsite->setData('vendor_product_id', $vendorProductId);
                $vendorProductWebsite->setData('vendor_id', $data['vendor_id']);
                $vendorProductWebsite->setData('website_id', $websiteId);
                $vendorProductWebsite->setData('status', 1);
                try {
                    $vendorProductWebsite->save();
                } catch (\Exception $e) {
                    throw new \Exception($e->getMessage());
                }
            }
        }
    }

    /**
     * @param $vendorProductWebsite
     * @param $websiteDataArray
     * @param $websiteCode
     * @param $vendorSku
     * @param bool $isAssociate
     * @param string $productType
     */
    protected function setWebsiteDetails(
        $vendorProductWebsite,
        $websiteDataArray,
        $websiteCode,
        $vendorSku,
        $isAssociate = false,
        $productType = 'simple'
    ) {
        if (!$isAssociate) {
            $vendorProductWebsite->setData('status', ProductRequestManagement::STATUS_APPROVED_LIVE);
            $vendorProductWebsite->setData('price', $websiteDataArray[$websiteCode]['price']);
            $vendorProductWebsite->setData('special_price', $websiteDataArray[$websiteCode]['special_price']);
            $vendorProductWebsite->setData('special_from_date', $websiteDataArray[$websiteCode]['special_from_date']);
            $vendorProductWebsite->setData('special_to_date', $websiteDataArray[$websiteCode]['special_to_date']);
            $vendorProductWebsite->setData('reorder_level', $websiteDataArray[$websiteCode]['reorder_level']);
            $vendorProductWebsite->setData('category_id', $websiteDataArray[$websiteCode]['category_id']);
        } else {
            if ($productType == Configurable::TYPE_CODE) {
                $skus = array_keys($websiteDataArray['default']);
                $vendorSku = (!empty($skus)) ? $skus[0] : $vendorSku;
            }
            if ($vendorSku) {
                $vendorProductWebsite->setData('status', ProductRequestManagement::STATUS_APPROVED_LIVE);
                $vendorProductWebsite->setData('price', $websiteDataArray[$websiteCode][$vendorSku]['price']);
                $vendorProductWebsite->setData(
                    'special_price',
                    $websiteDataArray[$websiteCode][$vendorSku]['special_price']
                );
                $vendorProductWebsite->setData(
                    'special_from_date',
                    $websiteDataArray[$websiteCode][$vendorSku]['special_from_date']
                );
                $vendorProductWebsite->setData(
                    'special_to_date',
                    $websiteDataArray[$websiteCode][$vendorSku]['special_to_date']
                );
                $vendorProductWebsite->setData(
                    'reorder_level',
                    $websiteDataArray[$websiteCode][$vendorSku]['reorder_level']
                );
                $vendorProductWebsite->setData(
                    'category_id',
                    $websiteDataArray[$websiteCode][$vendorSku]['category_id']
                );
            }
        }
    }

    /**
     *
     * @param $data
     * @param $product
     * @param $productId
     * @param $vendorOfferData
     * @param $fristProductCategory
     * @return mixed|null
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function createVendorConfigurableProductIfNotExists(
        $data,
        $product,
        $productId,
        $vendorOfferData,
        $fristProductCategory
    ) {
        $coreProduct = $this->productRepository->getById($productId);
        if ($coreProduct->getId()) {
            /* Check if configurable product exists or not.
             * Removed vendor_id filter as offers are added on child only and
             * so there is no need of vendor specific parent/main product.
             * There will be only one Parent product even though multiple vendor adds offer on it.
             */
            $vendorProduct = $this->vendorProductFactory->create()->getCollection()
                ->addFieldToFilter('marketplace_product_id', $productId)
                ->addFieldToFilter('rbvpw.status', 1)
                ->addFieldToFilter('main_table.type_id', Configurable::TYPE_CODE)
                ->addFieldToFilter('is_deleted', 0)->getFirstItem();

            $vendorSku = $this->mathRandom->getRandomString(8);
            if (empty($vendorProduct->getVendorProductId())) {
                $model = $this->vendorProductFactory->create();
                $model->setMarketplaceProductId($productId)
                        ->setVendorId($vendorOfferData['vendor_id'])
                        ->setName($coreProduct->getName())
                        ->setTypeId(Configurable::TYPE_CODE)
                        ->setStatus(Product::STATUS_LISTED)
                        ->setVendorSku($vendorSku)
                        ->setCategoryId($fristProductCategory)
                        ->setAttributeSetId($coreProduct->getData('attribute_set_id'));
                try {
                    $model->save();
                    $vendorProductId = $model->getId();

                    if ($vendorProductId) {
                        $this->saveWebsiteData(
                            $data,
                            $product,
                            $vendorProductId,
                            ProductRequestManagement::CORE_PRODUCT_TYPE_DEFAULT,
                            $vendorSku,
                            $vendorOfferData
                        );
                        $this->saveStoreData(
                            $data,
                            $vendorProductId,
                            ProductRequestManagement::CORE_PRODUCT_TYPE_DEFAULT,
                            $vendorSku,
                            $vendorOfferData
                        );
                    }
                    return $vendorProductId;
                } catch (\Zend_Db_Statement_Exception $e) {
                    if ($e->getCode() == self::ERROR_CODE_DUPLICATE_ENTRY) {
                        $this->messageManager->addError($e->getMessage());
                        $this->messageManager->addError(
                            __(
                                'Vendor offer already exists with vendor sku %1. Please try another vendor sku.',
                                $vendorSku
                            )
                        );
                    } else {
                        $this->messageManager->addError($e->getMessage());
                    }
                } catch (\Magento\Framework\Exception\LocalizedException $e) {
                    $this->messageManager->addError($e->getMessage());
                } catch (\RuntimeException $e) {
                    $this->messageManager->addError($e->getMessage());
                } catch (\Exception $e) {
                    $this->messageManager->addException(
                        $e,
                        __('Something went wrong while saving record.' . $e->getMessage())
                    );
                }
            }
        }
        return null;
    }

    /**
     * @param $id
     * @return string|null
     */
    protected function getProductData($id)
    {
        $parentId = null;
        $parentByChild = $this->_catalogProductTypeConfigurable->getParentIdsByChild($id);
        if (isset($parentByChild[0])) {
            $parentId = $parentByChild[0];
        }
        return $parentId;
    }

    /**
     * @param $productId
     * @param $productName
     * @throws \Exception
     */
    public function updateVendorProductName($productId, $productName)
    {
        $collection = $this->vendorProductFactory->create()->getCollection();
        $collection->addFieldToFilter('marketplace_product_id', ['eq' => $productId]);
        if ($collection->count() > 0) {
            foreach ($collection->getData() as $vendorProduct) {
                if (strcmp($vendorProduct['name'], $productName) != 0) {
                    $model = $this->vendorProductFactory->create();
                    $model->load($vendorProduct['vendor_product_id']);
                    $model->setName($productName);
                    $model->save();
                }
            }
        }
    }

    /**
     * @return StoreManagerInterface
     * @deprecated
     */
    protected function getStoreManager()
    {
        if (null === $this->_storeManager) {
            $this->_storeManager = \Magento\Framework\App\ObjectManager::getInstance()
                ->get(\Magento\Store\Model\StoreManagerInterface::class);
        }
        return $this->_storeManager;
    }

    /**
     * @param $postData
     * @param $vendorData
     * @param $vendorOfferData
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function preparePostData($postData,$vendorOfferData)
    {
        foreach ($postData['vip-offer'] as $key => $dataValue) {
            $key = ($key == 'vendorsku') ? 'sku' : $key;
            $postData['product'][$key] = $dataValue;
        }

        if (array_key_exists('current_product_id', $postData['product'])) {
            $postData['product']['marketplace_product_id'] = $postData['product']['current_product_id'];
        } else {
            $postData['product']['marketplace_product_id'] = null;
        }

        $postData['editMode'] = true;
        $postData['store_id'] = ($this->_storeManager->getStore()->getId()) ?
            $this->_storeManager->getStore()->getId() : 0;
        $postData['product']['is_requested_for_edit'] = false;
        $postData['product']['can_list'] = 1;
        $postData['product']['has_variants'] = 0;
        $postData['product']['vendor_sku'] = $vendorOfferData['vendorsku'];

        $postData['product']['website_ids'] = ($this->_storeManager->getStore()->getWebsiteId()) ?
            $this->_storeManager->getStore()->getWebsiteId() : 0;

        $websiteId =  ($postData['product']['website_ids'] == 0) ? 'default' : $postData['product']['website_ids'];
        $currentStoreId =  ($postData['store_id'] == 0) ? 'default' : $postData['store_id'];

        $storeData[$currentStoreId] = [
            'name' => $vendorData['name'],
            'condition_note' => null,
            'warranty_description' => null
        ];

        $websiteData[$websiteId] = [
                'price' => $vendorOfferData['price'],
                'special_price' => (array_key_exists(
                    'special_price',
                    $vendorOfferData
                ) && $vendorOfferData['special_price']) ? $vendorOfferData['special_price'] : null,
                'special_from_date' => (array_key_exists(
                    'special_from_date',
                    $vendorOfferData
                ) && $vendorOfferData['special_from_date']) ? $vendorOfferData['special_from_date'] : null,
                'special_to_date' => (array_key_exists(
                    'special_to_date',
                    $vendorOfferData
                ) && $vendorOfferData['special_to_date']) ? $vendorOfferData['special_to_date'] : null,
                'condition' => ProductRequestManagement::CONDITION_NEW,
                'reorder_level' => $vendorOfferData['reorder_level'],
                'warranty_type' => ProductRequestManagement::WARRANTY_MANUFACTURER
            ];

        if (!array_key_exists('vendor_product_id', $vendorOfferData)) {
            $websiteData[$websiteId]['category_id'] = $postData['product']['category_id'];
        }

        $postData['product']['store_data'] = $this->jsonEncoder->encode($storeData);
        $postData['product']['website_data'] = $this->jsonEncoder->encode($websiteData);

        return $postData;
    }
}
