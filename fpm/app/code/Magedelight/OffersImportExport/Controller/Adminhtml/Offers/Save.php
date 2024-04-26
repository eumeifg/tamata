<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_OffersImportExport
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\OffersImportExport\Controller\Adminhtml\Offers;

use Magedelight\Catalog\Model\Product;
use Magedelight\Catalog\Model\ProductRequestManagement;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;

class Save extends AbstractSave
{

    /**
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        $postData = $this->getRequest()->getParams();
        $_filesParam = $this->getRequest()->getFiles()->toArray();

        $websiteId = $postData['website_id'];
        $vendorId = $postData['vendor_id'];

        $resultRedirect = $this->resultRedirectFactory->create();
        try {
            if (isset($_filesParam['vendor_offers'])) {
                $csvFile = $_filesParam['vendor_offers'];
                $errors = $this->offersValidator->execute($csvFile, null, $this->getRequest());
                if (count($errors) > 0) {
                    $this->messageManager->addErrorMessage(__('Validation Results %1', implode(" \n", $errors)));
                } else {
                    $result = $this->importFromCsvFile($csvFile, $websiteId, $vendorId);
                    if ($result) {
                        if ($result['updatedDataCount'] > 0) {
                            $this->messageManager->addSuccessMessage(__('%1 Offer(s) updated.', $result['updatedDataCount']));
                        }
                        if ($result['newDataCount'] > 0) {
                            $this->messageManager->addSuccessMessage(__('%1 Offer(s) saved.', $result['newDataCount']));
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage($e, $e->getMessage());
        }
        return $resultRedirect->setPath('vendoroffers/offers/add');
    }

    /**
     * @param $file
     * @param $websiteId
     * @param $vendorId
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function importFromCsvFile($file, $websiteId, $vendorId)
    {
        if (!isset($file)) {
            throw new \Magento\Framework\Exception\LocalizedException(__('Invalid file upload attempt.'));
        }

        $importProductRawData = $this->csvProcessor->getData($file['tmp_name']);

        $headers = $importProductRawData[0];
        unset($importProductRawData[0]);

        $predefinedHeaders = array_keys($this->helper->getCSVFields());

        if ($headers === false) {
            throw new \Magento\Framework\Exception\LocalizedException(__('Please correct offers file format.'));
        }
        if (array_diff($predefinedHeaders, $headers)) {
            throw new \Magento\Framework\Exception\LocalizedException(__('Sample file and Uploading file\'s header fields are not matched.'));
        }
        $fields = $predefinedHeaders;
        $updatedDataCount = 0;
        $newDataCount = 0;
        $productIds = $parentIds = $products = [];

        foreach ($importProductRawData as $rowIndex => $dataRow) {
            $rowData = [];
            foreach ($fields as $key => $field) {
                if (in_array($field, ['status'])) {
                    $rowData[$field] = (isset($dataRow[$key])) ? $dataRow[$key] : null;
                } else {
                    $rowData[$field] = (!empty($dataRow[$key])) ? trim($dataRow[$key]) : null;
                }
            }

            $row = $this->_getImportRow($rowData, $predefinedHeaders);

            if (!empty($row)) {
                try {
                    $vendorProductId = null;
                    $vendorProductObject = $this->vendorProductFactory->create()->getCollection();
                    $vendorProductObject->addFieldToFilter('main_table.vendor_id', ['eq' => $row['vendor_id']])
                                            ->addFieldToFilter('vendor_sku', ['eq' => $row['vendor_sku']]);
                    $totalData = $vendorProductObject->count();

                    if (($vendorId == $row['vendor_id']) && ($totalData == 0)) {
                        $vendorProduct = $this->vendorProductFactory->create();
                        $vendorProductStore = $this->vendorProductStoreFactory->create();
                        $vendorProductWebsite = $this->vendorProductWebsiteFactory->create();
                        $row['website_id'] = $websiteId;
                        $row['editMode'] =  true;

                        $product = $this->productRepository->getById($row['marketplace_product_id'], true);

                        /*Create vendor offer on existing simple product*/
                        $vendorProduct->setData('marketplace_product_id', $row['marketplace_product_id']);
                        $vendorProduct->setData('vendor_id', $row['vendor_id']);
                        $vendorProduct->setData('is_offered', 1);
                        /*$vendorProduct->setData('category_id', $data['category_id']);*/
                        $vendorProduct->setData('attribute_set_id', $row['attribute_set_id']);
                        $vendorProduct->setData('name', $row['name']);
                        $vendorProduct->setData('vendor_sku', $row['vendor_sku']);
                        $vendorProduct->setData('condition', 1);

                        $vendorProduct->setData('qty', $row['qty']);

                        $storeId = $vendorProduct->getDefaultStoreId($websiteId);
                        $row['store_id'] = $storeId;

                        /* Only for child products in case of Configurable product. */
                        $parentId = $this->getProductData($product->getId());
                        if (!empty($parentId) && $product->getTypeId() != Configurable::TYPE_CODE) {
                            $vendorProduct->setParentId($parentId);
                            $parentVendorProductId = $this->createVendorConfigurableProductIfNotExists(
                                $row,
                                $product,
                                $parentId
                            );
                        }
                        /* Only for child products in case of Configurable product. */

                        $vendorProduct->save();
                        $vendorProductId = $vendorProduct->getId();
                        if ($vendorProductId) {
                            $this->saveWebsiteData(
                                $row,
                                $product,
                                $vendorProductId,
                                self::CORE_PRODUCT_TYPE_DEFAULT,
                                $row['vendor_sku']
                            );
                            $this->saveStoreData(
                                $row,
                                $vendorProductId,
                                self::CORE_PRODUCT_TYPE_DEFAULT,
                                $row['vendor_sku']
                            );
                            if ($row['status'] == Product::STATUS_LISTED) {
                                $products[$vendorProduct->getVendorProductId()]['qty'] = $vendorProduct->getQty();
                                $products[$vendorProduct->getVendorProductId()]['marketplace_product_id'] =
                                    $vendorProduct->getMarketplaceProductId();
                                $productIds[] = $vendorProduct->getMarketplaceProductId();
                                if ($vendorProduct->getParentId()) {
                                    $parentIds[] = $vendorProduct->getParentId();
                                }
                            }
                        }
                        $eventParams = [
                            'marketplace_product_id' => $vendorProductItem->getMarketplaceProductId(),
                            'old_qty' => (int) $vendorProductItem->getQty(),
                            'vendor_product' => $vendorProductItem
                        ];

                        $this->_eventManager->dispatch('vendor_product_list_after', $eventParams);
                    }
                    $newDataCount++;
                } catch (\Exception $e) {
                    if ($e->getCode() === self::ERROR_CODE_DUPLICATE_ENTRY
                        && preg_match('#SQLSTATE\[23000\]: [^:]+: 1062[^\d]#', $e->getMessage())
                    ) {
                        throw new \Magento\Framework\Exception\LocalizedException(__('Vendor offer already exists.'));
                    }
                    throw new \Magento\Framework\Exception\LocalizedException(__($e->getMessage()));
                }
            }
        }

        if (!empty($productIds)) {
            $eventParams = [
                'marketplace_product_ids' => array_unique($productIds) ,
                'vendor_products' => $products,
                'parent_ids' => array_unique($parentIds) ];
            $this->_eventManager->dispatch('vendor_product_mass_list_after', $eventParams);
        }
        return ['newDataCount' => $newDataCount,'updatedDataCount' => $updatedDataCount];
    }

    /**
     * @param $data
     * @param $product
     * @param $productId
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function createVendorConfigurableProductIfNotExists($data, $product, $productId)
    {
        $coreProduct = $this->productRepository->getById($productId);
        if ($coreProduct->getId()) {
            /* Check if configurable product exists or not. */
            $vendorProduct = $this->vendorProductFactory->create()->getCollection()
                ->addFieldToFilter('marketplace_product_id', $productId)
                ->addFieldToFilter('vendor_id', $data['vendor_id'])
                ->addFieldToFilter('rbvpw.status', 1)
                ->addFieldToFilter('is_deleted', 0)->getFirstItem();

            $vendorSku = $this->mathRandom->getRandomString(8);
            if (empty($vendorProduct->getVendorProductId())) {
                $model = $this->vendorProductFactory->create();
                $model->setMarketplaceProductId($productId)
                        ->setVendorId($data['vendor_id'])
                        ->setName($coreProduct->getName())
                        ->setTypeId(\Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE)
                        ->setStatus(Product::STATUS_LISTED)
                        ->setVendorSku($vendorSku)
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
                            $vendorSku
                        );
                        $this->saveStoreData(
                            $data,
                            $vendorProductId,
                            ProductRequestManagement::CORE_PRODUCT_TYPE_DEFAULT,
                            $vendorSku
                        );
                    }
                    return $vendorProductId;
                } catch (\Zend_Db_Statement_Exception $e) {
                    if ($e->getCode() == self::ERROR_CODE_DUPLICATE_ENTRY) {
                        $this->messageManager->addErrorMessage($e->getMessage());
                        $this->messageManager->addErrorMessage(__('Vendor offer already exists with vendor sku %1. Please try another vendor sku.', $vendorSku));
                    } else {
                        $this->messageManager->addErrorMessage($e->getMessage());
                    }
                } catch (\Magento\Framework\Exception\LocalizedException $e) {
                    $this->messageManager->addErrorMessage($e->getMessage());
                } catch (\RuntimeException $e) {
                    $this->messageManager->addErrorMessage($e->getMessage());
                } catch (\Exception $e) {
                    $this->messageManager->addExceptionMessage($e, __('Something went wrong while saving record.' . $e->getMessage()));
                }
            }
        }
    }

    /**
     * @return \Magento\Store\Model\StoreManagerInterface|mixed
     */
    public function getStoreManager()
    {
        if (null === $this->storeManager) {
            $this->storeManager = \Magento\Framework\App\ObjectManager::getInstance()
                ->get('Magento\Store\Model\StoreManagerInterface');
        }
        return $this->storeManager;
    }

    /**
     *
     * @param $data
     * @param $vendorProductId
     * @param $productType
     * @param $vendorSku
     * @throws \Exception
     */
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
            $vendorProductIdsWithStores[] = ['vendor_product_id' => $vendorProductId,'store_id' => $store->getId()];

            try {
                $vendorProductStore->save();
            } catch (\Exception $e) {
                throw new \Exception($e->getMessage());
            }
        }
    }

    /**
     *
     * @param $data
     * @param $product
     * @param $vendorProductId
     * @param $productType
     * @param $vendorSku
     * @throws \Exception
     */
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

        $websiteCode =  $data['website_id'];
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
            'category_id'
        ];
    }

    /**
     * @param $id
     * @return string|null
     */
    protected function getProductData($id)
    {
        $parentId = null;
        $parentByChild = $this->catalogProductTypeConfigurable->getParentIdsByChild($id);
        if (isset($parentByChild[0])) {
            $parentId = $parentByChild[0];
        }
        return $parentId;
    }
}
