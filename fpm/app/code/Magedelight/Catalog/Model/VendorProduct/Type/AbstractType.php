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
namespace Magedelight\Catalog\Model\VendorProduct\Type;

use Magedelight\Catalog\Api\ProductRequestRepositoryInterface;
use Magedelight\Catalog\Model\ProductRequestManagement;
use Magedelight\Catalog\Model\ResourceModel\ProductRequestStore\CollectionFactory as ProductRequestStoreCollectionFactory;
use Magedelight\Catalog\Model\ResourceModel\ProductRequestWebsite\CollectionFactory as ProductRequestWebsiteCollectionFactory;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;

/**
 * @api
 * Abstract model for product type implementation
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 100.0.2
 */
abstract class AbstractType
{
    const ERROR_CODE_DUPLICATE_ENTRY = 23000;

    /**
     * @var \Magento\Framework\Json\Decoder
     */
    protected $jsonDecoder;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $_productRepository;

    /**
     * @var \Magedelight\Catalog\Model\Product
     */
    protected $vendorProductFactory;

    /**
     * @var \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\ConfigurableFactory
     */
    protected $_typeConfigurableFactory;

    /**
     * @var Configurable
     */
    protected $typeConfigurableModelFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magedelight\Catalog\Model\ProductWebsiteFactory
     */
    protected $vendorProductWebsiteFactory;

    /**
     * @var \Magedelight\Catalog\Model\ProductStoreFactory
     */
    protected $vendorProductStoreFactory;

    /**
     * @var \Magedelight\Catalog\Model\ResourceModel\ProductRequest\CollectionFactory
     */
    protected $_productRequestCollectionFactory;

    /**
     * @var \Magedelight\Catalog\Model\ResourceModel\ProductRequestStore\CollectionFactory
     */
    protected $_productRequestStoreCollectionFactory;

    /**
     * @var ProductRequestWebsiteCollectionFactory
     */
    protected $_productRequestWebsiteCollectionFactory;

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $_eventManager;

    /**
     * @var \Magedelight\Catalog\Model\ResourceModel\ProductStore\CollectionFactory
     */
    protected $_vendorProductStoreCollectionFactory;

    /**
     * @var ProductRequestRepositoryInterface
     */
    protected $_productRequestRepository;

    /**
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Json\Decoder $jsonDecoder
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Magedelight\Catalog\Model\ProductFactory $vendorProductFactory
     * @param \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\ConfigurableFactory $typeConfigurableFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magedelight\Catalog\Model\ProductStoreFactory $vendorProductStoreFactory
     * @param \Magedelight\Catalog\Model\ResourceModel\ProductStore\CollectionFactory $vendorProductStoreCollectionFactory
     * @param \Magedelight\Catalog\Model\ProductWebsiteFactory $vendorProductWebsiteFactory
     * @param \Magedelight\Catalog\Model\ResourceModel\ProductRequest\CollectionFactory $productRequestCollectionFactory
     * @param ProductRequestStoreCollectionFactory $productRequestStoreCollectionFactory
     * @param ProductRequestWebsiteCollectionFactory $productRequestWebsiteCollectionFactory
     * @param ProductRequestRepositoryInterface $productRequestRepository
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Json\Decoder $jsonDecoder,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magedelight\Catalog\Model\ProductFactory $vendorProductFactory,
        \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\ConfigurableFactory $typeConfigurableFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magedelight\Catalog\Model\ProductStoreFactory $vendorProductStoreFactory,
        \Magedelight\Catalog\Model\ResourceModel\ProductStore\CollectionFactory $vendorProductStoreCollectionFactory,
        \Magedelight\Catalog\Model\ProductWebsiteFactory $vendorProductWebsiteFactory,
        \Magedelight\Catalog\Model\ResourceModel\ProductRequest\CollectionFactory $productRequestCollectionFactory,
        ProductRequestStoreCollectionFactory $productRequestStoreCollectionFactory,
        ProductRequestWebsiteCollectionFactory $productRequestWebsiteCollectionFactory,
        ProductRequestRepositoryInterface $productRequestRepository,
        \Magento\Framework\Event\ManagerInterface $eventManager
    ) {
        $this->jsonDecoder = $jsonDecoder;
        $this->_productRepository = $productRepository;
        $this->vendorProductFactory = $vendorProductFactory;
        $this->_typeConfigurableFactory = $typeConfigurableFactory;
        $this->_storeManager = $storeManager;
        $this->vendorProductStoreFactory = $vendorProductStoreFactory;
        $this->vendorProductWebsiteFactory = $vendorProductWebsiteFactory;
        $this->_productRequestCollectionFactory = $productRequestCollectionFactory;
        $this->_productRequestStoreCollectionFactory = $productRequestStoreCollectionFactory;
        $this->_productRequestWebsiteCollectionFactory = $productRequestWebsiteCollectionFactory;
        $this->_eventManager = $eventManager;
        $this->_vendorProductStoreCollectionFactory = $vendorProductStoreCollectionFactory;
        $this->_productRequestRepository = $productRequestRepository;
    }

    /**
     *
     * @param object $product \Magentp\Product\Model\Product
     * @param array $data
     * @param boolean $isconfig
     * @param string $productType
     * @param null $vendorSku
     * @param array $postData
     * @return array productId
     * @throws \Exception
     */
    public function createVendorProduct(
        $product,
        $data,
        $isconfig = false,
        $productType = ProductRequestManagement::CORE_PRODUCT_TYPE_DEFAULT,
        $vendorSku = null,
        $postData = []
    ) {
        try {
            $data = (array_key_exists('product', $data)) ? $data['product'] : $data;

            $websiteId = (array_key_exists('website_id', $data)) ?
                $data['website_id'] : $this->_storeManager->getDefaultStoreView()->getWebsiteId();
            $data['website_ids'] = $websiteId;
            $vendorProduct = $this->vendorProductFactory->create();

            $vendorProduct->setData('marketplace_product_id', $product->getId());
            $vendorProduct->setData('vendor_id', $data['vendor_id']);
            $vendorProduct->setData('is_offered', $data['is_offered']);

            if ($isconfig) {
                $vendorProduct->setData('type_id', Configurable::TYPE_CODE);
                $vendorProduct->setData('category_id', $data['category_id']);
                $vendorProduct->setData('attribute_set_id', $data['attribute_set_id']);
                $vendorProduct->setData('vendor_sku', $data['vendor_sku']);
                $vendorProduct->setData('qty', null);
            } else {
                $vendorProduct->setData('category_id', $data['category_id']);
                $vendorProduct->setData('attribute_set_id', $data['attribute_set_id']);
                $vendorProduct->setData('vendor_sku', $data['vendor_sku']);
                if (array_key_exists('parent_id', $data)) {
                    $vendorProduct->setData('parent_id', $data['parent_id']);
                }
                $vendorProduct->setData('qty', $data['qty']);
            }

            try {
                try {
                    $vendorProduct->save();
                } catch (Exception $e) {
                    throw new \Exception($e->getMessage());
                }
                $vendorSku = $vendorProduct->getVendorSku();
                $vendorProductId = $vendorProduct->getId();
                $vendorProductIds[] = $vendorProductId;

                if ($vendorProductId) {
                    if ($productType == ProductRequestManagement::CORE_PRODUCT_TYPE_ASSOCIATED) {
                        $productRequestCollection = $this->_productRequestCollectionFactory->create()
                            ->addFieldToFilter('vendor_sku', $data['vendor_sku'])
                            ->addFieldToSelect(['product_request_id'])->getFirstItem();
                        $data['product_request_id'] = ($productRequestCollection &&
                            $productRequestCollection->getId()) ?
                            $productRequestCollection->getId() : $data['product_request_id'];
                    }
                    $this->saveWebsiteData($data, $product, $vendorProductId, $productType, $vendorSku);
                    $this->saveStoreData($data, $product, $vendorProductId, $productType, $vendorSku, $postData);
                    if ($productType == ProductRequestManagement::CORE_PRODUCT_TYPE_ASSOCIATED) {
                        $this->_productRequestRepository->deleteById($data['product_request_id']);
                    }
                    $this->_eventManager->dispatch(
                        'vendor_product_save_after',
                        [
                            'vendor_product' =>  $vendorProduct
                        ]
                    );
                }
            } catch (\Exception $e) {
                throw new \Exception($e->getMessage());
            }

            if (array_key_exists('can_list', $data) && $data['can_list']) {
                $this->_listProduct($vendorProductIds, $productType);
            }

            return $vendorProductIds;
        } catch (\Zend_Db_Statement_Exception $e) {
            if ($e->getCode() == self::ERROR_CODE_DUPLICATE_ENTRY) {
                throw new \Exception(__('Product is already exist vendor with same store.'));
            } else {
                throw new \Exception(__($e->getMessage()));
            }
        } catch (\Exception $e) {
            /* $this->_deleteCoreProduct($product->getId()); */
            throw new \Exception($e->getMessage());
        }
    }

    /**
     *
     * @param object $product \Magentp\Product\Model\Product
     * @param $postData
     * @param boolean $isconfig
     * @return int productId
     * @throws \Exception
     */
    public function updateVendorProduct($product, $postData, $isconfig = false)
    {
        $data = $postData['product'];
        $storeData = new \Magento\Framework\DataObject();
        $vendorProduct = $this->vendorProductFactory->create()->load($data['vendor_product_id']);
        $websiteId = (array_key_exists('website_id', $data)) ?
            $data['website_id'] : $this->_storeManager->getDefaultStoreView()->getWebsiteId();
        $vendorProductWebsite = $this->vendorProductWebsiteFactory->create()->load(
            $data['vendor_product_id'],
            'vendor_product_id'
        );

        if (!$vendorProduct->getId()) {
            throw new \Exception(__('This vendor product no longer exists.'));
        }
        try {
            $vendorProduct->setData('store_id', $data['current_store_id']);
            if ($product && $product->getId()) {
                $vendorProduct->setData('marketplace_product_id', $product->getId());
            }
            $vendorProduct->setData('vendor_id', $data['vendor_id']);

            if ($isconfig) {
                $vendorProduct->setData('type_id', Configurable::TYPE_CODE);
                $vendorProduct->setData('category_id', $data['category_id']);
                $vendorProduct->setData('attribute_set_id', $data['attribute_set_id']);

                /* Set website data. */
                if ($postData['product']['can_list'] == 1) {
                    $vendorProductWebsite->setData('status', ProductRequestManagement::STATUS_APPROVED_LIVE);
                } elseif ($postData['product']['can_list'] != 1 && $data['existing'] == 1) {
                    $vendorProductWebsite->setData('status', ProductRequestManagement::STATUS_APPROVED_LIVE);
                } else {
                    $vendorProductWebsite->setData('status', ProductRequestManagement::STATUS_APPROVED_NON_LIVE);
                } // set as live bydefault
                $vendorProduct->setData('vendor_sku', $data['vendor_sku']);
                $vendorProductWebsite->setData('condition', $product->getCondition());
                $vendorProductWebsite->setData('price', $data['price']);
                $vendorProductWebsite->setData('cost_price_iqd', $data['cost_price_iqd']);
                $vendorProductWebsite->setData('cost_price_usd', $data['cost_price_usd']);
                if ($data['special_price'] != '' && (int)$data['special_price'] > 0) {
                    $vendorProductWebsite->setData('special_price', $data['special_price']);
                    $vendorProductWebsite->setData('special_from_date', $data['special_from_date']);
                    $vendorProductWebsite->setData('special_to_date', $data['special_to_date']);
                } else {
                    $vendorProductWebsite->setData('special_price', null);
                    $vendorProductWebsite->setData('special_from_date', null);
                    $vendorProductWebsite->setData('special_to_date', null);
                }
                $vendorProduct->setData('qty', null);
                $vendorProductWebsite->setData('reorder_level', $product->getReorderLevel());
                /* Set website data. */

                /* Set store data. */
                $storeData->setData('condition_note', $product->getConditionNote());
                $storeData->setData('warranty_description', $product->getWarrantyDescription());
            /* Set store data. */
            } else {
                $vendorProduct->setData('category_id', $data['category_id']);
                $vendorProduct->setData('attribute_set_id', $data['attribute_set_id']);
                $vendorProduct->setData('name', $data['name']);
                $vendorProduct->setData('vendor_sku', $data['vendor_sku']);

                /* Set website data. */
                (array_key_exists('condition', $data)) ?
                    $vendorProductWebsite->setData('condition', $data['condition']) :
                    $vendorProductWebsite->setData('condition', null);

                $vendorProductWebsite->setData('price', $data['price']);

                if ($postData['product']['can_list'] == 1) {
                    $vendorProductWebsite->setData('status', ProductRequestManagement::STATUS_APPROVED_LIVE);
                } elseif ($postData['product']['can_list'] != 1 && $data['existing'] == 1) {
                    $vendorProductWebsite->setData('status', ProductRequestManagement::STATUS_APPROVED_NON_LIVE);
                } else {
                    $vendorProductWebsite->setData('status', ProductRequestManagement::STATUS_APPROVED_NON_LIVE);
                }
                $vendorProductWebsite->setData('cost_price_iqd', $data['cost_price_iqd']);
                $vendorProductWebsite->setData('cost_price_usd', $data['cost_price_usd']);
                if ($data['special_price'] != '' && (int)$data['special_price'] > 0) {
                    $vendorProductWebsite->setData('special_price', $data['special_price']);
                    $vendorProductWebsite->setData('special_from_date', $data['special_from_date']);
                    $vendorProductWebsite->setData('special_to_date', $data['special_to_date']);
                } else {
                    $vendorProductWebsite->setData('special_price', null);
                    $vendorProductWebsite->setData('special_from_date', null);
                    $vendorProductWebsite->setData('special_to_date', null);
                }
                $vendorProduct->setData('qty', $data['qty']);
                $vendorProductWebsite->setData('reorder_level', $data['reorder_level']);
                /* Set website data. */

                /* Set store data. */
                $storeData->setData('condition_note', $data['condition_note']);
                $storeData->setData('warranty_description', $data['warranty_description']);
                /* Set store data. */
            }

            try {
                $vendorProduct->save();
            } catch (\Exception $e) {
                throw new \Exception($e->getMessage());
            }
            $vendorProductId = $vendorProduct->getId();

            $vendorProductWebsite->setData('vendor_product_id', $vendorProductId);
            $vendorProductWebsite->setData('vendor_id', $data['vendor_id']);

            try {
                $vendorProductWebsite->save();
                $stores = $this->_storeManager->getStores();
                foreach ($stores as $store) {
                    $requestStoreData = $this->_productRequestStoreCollectionFactory->create()
                            ->addFieldToFilter('website_id', $websiteId)
                            ->addFieldToFilter('store_id', $store->getId())
                            ->addFieldToFilter('product_request_id', $data['product_request_id'])->getFirstItem();
                    if ($requestStoreData && $requestStoreData->getId()) {
                        $websiteId = $this->_storeManager->getStore($store->getId())->getWebsiteId();
                        $vendorProductStore = $this->_vendorProductStoreCollectionFactory->create()
                        ->addFieldToFilter('website_id', $websiteId)
                        ->addFieldToFilter('store_id', $store->getId())
                        ->addFieldToFilter('vendor_product_id', $data['vendor_product_id'])->getFirstItem();

                        if ($vendorProductStore && $vendorProductStore->getId()) {
                            $vendorProductStore->setData('condition_note', $storeData->getData('condition_note'));
                            $vendorProductStore->setData(
                                'warranty_description',
                                $storeData->getData('warranty_description')
                            );
                        } else {
                            $vendorProductStore = $this->vendorProductStoreFactory->create();
                            $vendorProductStore->setData('condition_note', $storeData->getData('condition_note'));
                            $vendorProductStore->setData(
                                'warranty_description',
                                $storeData->getData('warranty_description')
                            );
                            $vendorProductStore->setData('vendor_product_id', $vendorProductId);
                            $vendorProductStore->setData('store_id', $store->getId());
                            $vendorProductStore->setData('website_id', $websiteId);
                        }
                        $vendorProductStore->save();
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
                    }
                }

                /* Event for all stores save. */
                $eventParams = [
                    'post_data'   => $postData,
                    'vendor_product_ids_with_stores' => [
                        0 => [
                        'vendor_product_id' => $vendorProductId,
                        'store_id' => $data['current_store_id']
                        ]
                    ]
                ];
                $this->_eventManager->dispatch('vendor_product_stores_save_after', $eventParams);
                /* Event for all stores save. */
            } catch (\Exception $e) {
                throw new \Exception($e->getMessage());
            }

            return $vendorProduct->getId();
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    /**
     *
     * @param type $data
     * @param type $product
     * @param type $vendorProductId
     * @throws \Exception
     */
    protected function saveStoreData($data, $product, $vendorProductId, $productType, $vendorSku = null, $postData = [])
    {
        $collection = $this->_productRequestStoreCollectionFactory->create();
        $productDataStorewise = $collection->addFieldToFilter('product_request_id', $data['product_request_id']);
        $availableWebsites = (is_array($data['website_ids'])) ?
            $data['website_ids'] : explode(",", $data['website_ids']);

        if (isset($productDataStorewise) && !empty($availableWebsites)) {
            $websites = $this->_storeManager->getWebsites();
            $vendorProductIdsWithStores = [];
            foreach ($availableWebsites as $websiteId) {
                foreach ($productDataStorewise as $product) {
                    $eachWebsiteId = $this->_storeManager->getStore($product->getStoreId())->getWebsiteId();
                    if (!in_array(0, $availableWebsites) && !in_array($eachWebsiteId, $availableWebsites)):
                        if ($product->getStoreId() != 0):
                            continue;
                        endif;
                    endif;

                    $vendorProductStore = $this->vendorProductStoreFactory->create();
                    if (!$data['has_variants'] || $data['has_variants'] == '') {
                        $vendorProductStore->setData('condition_note', $product->getConditionNote());
                        $vendorProductStore->setData('warranty_description', $product->getWarrantyDescription());
                    } elseif ($data['has_variants'] &&
                        $data['has_variants'] == 1 &&
                        array_key_exists('product', $postData)
                    ) {
                        $varianData = $this->jsonDecoder->decode($postData['product']['variants']);
                        foreach ($varianData as $variant) {
                            if (array_key_exists('vendor_sku', $variant) &&
                                $variant['vendor_sku'] == $data['vendor_sku']
                            ) {
                                $vendorProductStore->setData('condition_note', $variant['condition_note']);
                                $vendorProductStore->setData('warranty_description', $variant['warranty_description']);
                                break;
                            }
                        }
                    }
                    $vendorProductStore->setData('vendor_product_id', $vendorProductId);
                    $vendorProductStore->setData('store_id', $product->getStoreId());
                    $vendorProductStore->setData('website_id', $eachWebsiteId);
                    $vendorProductIdsWithStores[] = [
                        'vendor_product_id' => $vendorProductId,
                        'store_id' => $product->getStoreId()
                    ];
                    /* Event for single store save. */
                    $this->_eventManager->dispatch(
                        'vendor_product_store_save_after',
                        [
                            'vendor_product_store_data' => [
                                'vendor_product_id' => $vendorProductId,
                                'store_id' => $product->getStoreId()
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
     * update parent_id for all newly created child product in vendor produt table
     * @param type Magento\Catalog\Model\Product $product
     * return void
     * @param array $vendorProductIds
     * @throws \Exception
     */
    public function updateParentIdForChildProduct($product, $vendorProductIds = [])
    {
        if ($vendorProductIds) {
            foreach ($vendorProductIds as $id) {
                if (is_array($id)) {
                    foreach ($id as $_id) {
                        $vendorProduct = $this->vendorProductFactory->create()->load($_id);
                        $vendorProduct->setData('parent_id', $product->getId());
                        $vendorProduct->save();
                    }
                } else {
                    $vendorProduct = $this->vendorProductFactory->create()->load($id);
                    $vendorProduct->setData('parent_id', $product->getId());
                    $vendorProduct->save();
                }
            }
        }
    }

    /**
     *
     * @param type $data
     * @param type $product
     * @param type $vendorProductId
     * @throws \Exception
     */
    protected function saveWebsiteData($data, $product, $vendorProductId, $productType, $vendorSku = null)
    {
        $websiteDataArray = [];
        $isAssociate = false;

        $collection = $this->_productRequestWebsiteCollectionFactory->create();
        $productRequestWebsiteColln = $collection->addFieldToFilter('product_request_id', $data['product_request_id']);

        if ($productType == ProductRequestManagement::CORE_PRODUCT_TYPE_ASSOCIATED) {
            $isAssociate = true;
        }
        if ($product->getTypeId() == Configurable::TYPE_CODE) {
            $productType = Configurable::TYPE_CODE;
            $isAssociate = true;
        }
        if ($productRequestWebsiteColln) {
            $websites = $this->_storeManager->getWebsites();

            foreach ($productRequestWebsiteColln as $productRequestWebsite) {
                $vendorProductWebsite = $this->vendorProductWebsiteFactory->create();
                $websiteData = $productRequestWebsite->getData();
                $this->setWebsiteDetails(
                    $vendorProductWebsite,
                    $websiteData,
                    $productRequestWebsite->getWebsiteId(),
                    $vendorSku,
                    $isAssociate,
                    $productType
                );
                $vendorProductWebsite->setData('vendor_product_id', $vendorProductId);
                $vendorProductWebsite->setData('vendor_id', $data['vendor_id']);
                $vendorProductWebsite->setData('website_id', $productRequestWebsite->getWebsiteId());
                $vendorProductWebsite->setData('cost_price_iqd', $data['cost_price_iqd']);
                $vendorProductWebsite->setData('cost_price_usd', $data['cost_price_usd']);
                if ($data['can_list'] == '') {
                    $vendorProductWebsite->setData('status', 0);
                } elseif ($data['can_list'] == 1) {
                    $vendorProductWebsite->setData('status', 1);
                }
                try {
                    $vendorProductWebsite->save();
                } catch (\Exception $e) {
                    throw new \Exception($e->getMessage());
                }
            }
        }
    }

    protected function setWebsiteDetails(
        $vendorProductWebsite,
        $websiteDataArray,
        $websiteCode,
        $vendorSku,
        $isAssociate = false,
        $productType = 'simple'
    ) {
        if (!is_array($websiteDataArray)) {
            /* String in case of configurable product. */
            $websiteDataArray = $this->jsonDecoder->decode($websiteDataArray);
        }
        $finalWebsiteData = $websiteDataArray;
        if (!$isAssociate) {
            $finalWebsiteData = $websiteDataArray;
        } else {
            if ($productType == Configurable::TYPE_CODE) {
                $skus = array_keys($websiteDataArray);
                $vendorSku = (!empty($skus)) ? $skus[0] : $vendorSku;
            }
            if ($vendorSku) {
                $finalWebsiteData = $websiteDataArray;
            }

            if ($productType == ProductRequestManagement::CORE_PRODUCT_TYPE_ASSOCIATED) {
                $finalWebsiteData = $websiteDataArray;
            }
        }

        $vendorProductWebsite->setData('status', ProductRequestManagement::STATUS_APPROVED_LIVE);
        if (!empty($finalWebsiteData)) {
            foreach ($this->getVendorOfferWebsiteFields() as $field) {
                if (array_key_exists($field, $finalWebsiteData)) {
                    if (in_array($field, ['price','special_price','condition']) && (int)$finalWebsiteData[$field] === 0) {
                        /* Allow fields to have 0 value.*/
                        $vendorProductWebsite->setData($field, $finalWebsiteData[$field]);
                    } elseif (!empty($finalWebsiteData[$field])) {
                        $vendorProductWebsite->setData($field, $finalWebsiteData[$field]);
                    }
                }
            }
        }
    }

    /**
     * @return array
     */
    public function getVendorOfferWebsiteFields()
    {
        return[
            'status',
            'condition',
            'price',
            'special_price',
            'special_from_date',
            'special_to_date',
            'reorder_level',
            'warranty_type',
            'category_id',
            'cost_price_iqd',
            'cost_price_usd'
        ];
    }

    /**
     * List product by Admin
     * @param array $ids
     */
    protected function _listProduct($ids, $productType)
    {
        if (is_array($ids)) {
            foreach ($ids as $id) {
                $vendorProduct = $this->vendorProductFactory->create()->load($id);
                if ($productType != ProductRequestManagement::CORE_PRODUCT_TYPE_ASSOCIATED &&
                    $this->_storeManager->getDefaultStoreView()->getId() == $vendorProduct->getStoreId()
                ) {
                    $this->_eventManager->dispatch('vendor_product_admin_list', ['status' => 'List','id' => $id]);
                }
                $eventParams = [
                    'marketplace_product_id' => $vendorProduct->getMarketplaceProductId(),
                    'old_qty'   => (int) $vendorProduct->getQty(),
                    'vendor_product' => $vendorProduct
                ];
                $this->_eventManager->dispatch('vendor_product_list_after', $eventParams);
            }
        }
    }

    /**
     *
     * @param array $data
     * @return int vendor product ID
     * @throws \Exception
     */
    public function updateVendorProductForEdit($data)
    {
        $vendorProductId = $data['vendor_product_id'];
        $vendorProduct = $this->vendorProductFactory->create()->load($vendorProductId);

        $vendorProduct->setData('vendor_sku', $data['vendor_sku']);
        if (array_key_exists('manufacturer_sku', $data)) {
            $vendorProduct->setData('manufacturer_sku', $data['manufacturer_sku']);
        }

        if (array_key_exists('condition', $data)) {
            $vendorProduct->setData('condition', $data['condition']);
        }

        if (array_key_exists('condition_note', $data)) {
            $vendorProduct->setData('condition_note', $data['condition_note']);
        }

        $vendorProductWebsite = $this->vendorProductWebsiteFactory->create()->load(
            $data['vendor_product_id'],
            'vendor_product_id'
        );

        $vendorProductWebsite->setData('price', $data['price']);

        /*$vendorProduct->setData('tax_class_id', $data['tax_class_id']);*/

        if ($data['special_price'] != '' && (int)$data['special_price'] > 0) {
            $vendorProductWebsite->setData('special_price', $data['special_price']);
            $vendorProductWebsite->setData('special_from_date', $data['special_from_date']);
            $vendorProductWebsite->setData('special_to_date', $data['special_to_date']);
        } else {
            $vendorProductWebsite->setData('special_price', null);
            $vendorProductWebsite->setData('special_from_date', null);
            $vendorProductWebsite->setData('special_to_date', null);
        }

        $vendorProduct->setData('qty', $data['qty']);

        if (array_key_exists('reorder_level', $data)) {
            $vendorProductWebsite->setData('reorder_level', $data['reorder_level']);
        }
        if (array_key_exists('warranty_description', $data)) {
            $vendorProduct->setData('warranty_description', $data['warranty_description']);
        }

        try {
            $vendorProductWebsite->save();
            $vendorProduct->save();
            return $vendorProduct->getMarketplaceProductId();
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    /**
     *
     * @param integer $id
     * @return integer
     */
    public function getProductData($id)
    {
        $parentId = null;
        $parentByChild = $this->_typeConfigurableFactory->create()->getParentIdsByChild($id);
        if (isset($parentByChild[0])) {
            $parentId = $parentByChild[0];
        }
        return $parentId;
    }

    /**
     * Retrive attribute codes which used for configurable product.
     * @return boolean false | Array
     */
    protected function _getUsedConfigurableAttributeCodes($postData)
    {
        if ($postData['product']['used_product_attribute_ids']) {
            $IdsCodes = $this->jsonDecoder->decode($postData['product']['used_product_attribute_ids']);
            return $IdsCodes;
        }
        return false;
    }
}
