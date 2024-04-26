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
namespace Magedelight\Catalog\Model\Product\Type;

/**
 * Simple product type implementation
 */
class Simple extends \Magedelight\Catalog\Model\Product\Type\AbstractType
{
    protected $productAttributes = [];

    /**
     * Create Simple Product
     *
     * @return \Magento\Catalog\Model\Product
     */
    public function createSimpleProduct($postData)
    {
        // if (array_key_exists('media_gallery', $postData['product'])) {
        //     foreach ($postData['product']['media_gallery']['images'] as $key => $image) {
        //         $imageExists = $this->helper->getTmpFileIfExists($image['file']);
        //         if (!$imageExists) {
        //             $this->helper->createTmpImageIfNotExists($image['file']);
        //         }
        //     }
        // }

        $storeId = ($postData['store_id']) ? $postData['store_id'] : 0;

        $store = $this->_storeManager->getStore($storeId);
        $this->_storeManager->setCurrentStore($store->getCode());

        $productId = $postData['product']['marketplace_product_id'];

        $data = $postData;
        $productTypeId = \Magento\Catalog\Model\Product\Type::DEFAULT_TYPE;

        $marketplaceProductId = $productId;
        $editExistingProduct = false;
        if ($data) {
            try {
                $params = $postData;

                if ($marketplaceProductId) {
                    $postData['id'] = $marketplaceProductId;
                    $editExistingProduct = true;
                }

                $product = $this->initializationHelper->initialize(
                    $this->build($postData)
                );

                if (!array_key_exists('weight', $postData['product'])) {
                    $product->setData('weight', 1);
                }

                if (!array_key_exists('product_has_weight', $postData['product'])) {
                    /* Set before processProduct() to work */
                    $product->setData('product_has_weight', 1);
                }

                $product->setData('visibility', \Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH);
                $this->productTypeManager->processProduct($product);

                if (isset($data['product'][$product->getIdFieldName()])) {
                    throw new \Magento\Framework\Exception\LocalizedException(__('Unable to save product'));
                }

                $originalSku = $product->getSku();

                //do not update URL key if approved product change request is here.
                if (!$postData['product']['is_requested_for_edit']) {
                    $product->setUrlKey(str_replace(' ', '-', strtolower($postData['product']['name'])));
                    if (!empty($postData['product']['url_key'])) {
                        /* Set custom url key if set by admin.*/
                        $product->setUrlKey($this->generateUrlKey($postData['product']['url_key']));
                    }
                }

                $isUnique = $this->checkUrlKeyDuplicates($originalSku, $product->getUrlKey());
                if (!$isUnique) {
                    throw new \Exception(
                        __('The value specified in the URL Key field would generate
                         a URL that already exists. Please enter unique url key.')
                    );
                }

                $product->setCategoryIds([$postData['product']['category_id']]);

                if (array_key_exists('website_id', $postData['product'])) {
                    if (is_array($postData['product']['website_id'])) {
                        $websiteId[] = $postData['product']['website_id'];
                    } else {
                        $websiteId[] = $postData['product']['website_id'];
                    }
                } else {
                    $websiteId[] = $this->_storeManager->getDefaultStoreView()->getWebsiteId();
                }

                $websiteId = (array_key_exists('website_id', $postData['product'])) ?
                    $postData['product']['website_id'] : $this->_storeManager->getDefaultStoreView()->getWebsiteId();

                $product->setWebsiteIds([$websiteId]);
                $product->setStockData(
                    [
                    'use_config_manage_stock' => 0,
                    'qty' => $postData['product']['qty'],
                    'is_qty_decimal' => 0,
                    'min_qty' => 0,
                    'max_sale_qty' => 10000,
                    'notify_stock_qty' => 1,
                    'qty_increments' => 1,
                    'min_sale_qty' => 1,
                    'manage_stock' => 1,
                    'use_config_min_qty' => 1,
                    'use_config_max_sale_qty' => 1,
                    'use_config_backorders' => 1,
                    'backorders' => 0,
                    'use_config_deferred_stock_update' => 1,
                    'deferred_stock_update' => 1,
                    'use_config_notify_stock_qty' => 1,
                    'use_config_enable_qty_inc' => 1,
                    'enable_qty_increments' => 0,
                    'use_config_qty_increments' => 1,
                    'use_config_min_sale_qty' => 1,
                    'is_qty_decimal' => 0,
                    'is_decimal_divided' => 0
                    ]
                );

                try {
                    $product->save();
                    $this->handleImageRemoveErrorProduct($data, $product->getId());
                    $this->setStoreWebsiteDataForProduct($product, $postData, $editExistingProduct);
                } catch (\Exception $e) {
                    $this->_logger->info($e->getMessage());
                    throw new \Exception(__($e->getMessage()));
                }

                try {
                    $this->getCategoryLinkManagement->assignProductToCategories(
                        $product->getSku(),
                        $product->getCategoryIds()
                    );
                } catch (\Exception $e) {
                    throw new \Exception(__('Could not assign category to product.'));
                }

                $productId = $product->getEntityId();
                $productTypeId = $product->getTypeId();

                return $product;
            } catch (\Exception $e) {
                $this->_logger->info($e->getMessage());
                if ($e->getCode() == 23000) {
                    throw new \Exception(__('Something went wrong.' . $e->getMessage()));
                }
                throw new \Exception($e->getMessage());
            }
        }
        return false;
    }

    /**
     *
     * @param type $product
     * @param type $data
     * @throws \Exception
     */
    protected function setStoreWebsiteDataForProduct($product, $data, $editExistingProduct)
    {
        /* Store data */
        $storeCollection = $this->_productRequestStoreCollectionFactory->create();
        $attributesDataArray = $storeCollection->addFieldToFilter(
            'product_request_id',
            $data['product']['product_request_id']
        )->addFieldToSelect(['attributes','store_id'])->getData();
        /* Store data */

        /* Website data */
        $websiteCollection = $this->_productRequestWebsiteCollectionFactory->create();
        $websiteDataArray = $websiteCollection
            ->addFieldToFilter('product_request_id', $data['product']['product_request_id'])
            ->addFieldToSelect(['website_id','price','special_price','special_from_date'])->getData();
        /* Website data */

        $attributes = $this->getProductAttributes($product);

        $productFactory = $this->productFactory->create();
        $this->productResourceModel->load($productFactory, $product->getId());

        if (!empty($websiteDataArray)) {
            foreach ($websiteDataArray as $webData) {
                unset($webData['row_id']);

                foreach ($attributesDataArray as $storeData) {
                    if ($editExistingProduct) {
                        $storeId = $data['store_id'];
                    } else {
                        $storeId = $storeData['store_id'];
                    }
                    $currentStoreWebsite = $this->_storeManager->getStore($storeId)->getWebsiteId();

                    if ($currentStoreWebsite != $webData['website_id']):
                        continue;
                    endif;

                    $productFactory->setStoreId($storeId);

                    try {
                        foreach ($webData as $key => $value) {
                            $productFactory->setData($key, $value);
                            if (in_array($key, $attributes)) {
                                $this->productResourceModel->saveAttribute($productFactory, $key);
                            }
                        }
                        $storeAttributes  = $this->jsonDecoder->decode($storeData['attributes']);
                        foreach ($storeAttributes as $webkey => $webvalue) {
                            $webvalue = (is_array($webvalue)) ? implode(",", $webvalue) : $webvalue;
                            if (in_array($webkey, $attributes)) {
                                $productFactory->setData($webkey, $webvalue);
                                $this->productResourceModel->saveAttribute($productFactory, $webkey);
                            }
                        }
                    } catch (\Exception $e) {
                        $this->_logger->info($e->getMessage());
                        throw new \Exception($e->getMessage());
                    }
                }
            }
        }
    }

    protected function getProductAttributes($product)
    {
        if (empty($this->productAttributes)) {
            $attributes = $product->getAttributes();
            foreach ($attributes as $attribute) {
                $this->productAttributes[] = $attribute->getAttributeCode();
            }
        }
        return $this->productAttributes;
    }
}
