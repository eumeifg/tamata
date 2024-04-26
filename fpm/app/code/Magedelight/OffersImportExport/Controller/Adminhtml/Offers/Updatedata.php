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

class Updatedata extends AbstractSave
{

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Redirect|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $postData = $this->getRequest()->getParams();
        $_files = $this->getRequest()->getFiles()->toArray();
        $websiteId = $postData['website_id'];
        $vendorId = $postData['vendor_id'];
        $resultRedirect = $this->resultRedirectFactory->create();
        try {
            if (isset($_files['vendor_offers'])) {
                $csvFile = $_files['vendor_offers'];
                $errors = $this->offersValidator->execute($csvFile, 'edit', $this->getRequest());
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
        return $resultRedirect->setPath('vendoroffers/offers/update');
    }

    /**
     * @param $file
     * @param $websiteId
     * @param $vendorId
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
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

            if ($this->_importErrors) {
                $error = __(
                    'We couldn\'t import this file because of these errors: %1',
                    implode(" \n", $this->_importErrors)
                );
                throw new \Magento\Framework\Exception\LocalizedException($error);
            }

            if (!empty($row)) {
                try {
                    /* Get Vendor Product Id by sku. */
                    $vendorSku = $row['vendor_sku'];
                    $vendorProductId = 0;
                    $vendorProductObject =  $this->vendorProductFactory->create()->getCollection()
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
                                $productStoreModel = $this->productStoreRepository
                                    ->getProductStoreData($vendorProductItem->getId(), $storeId);

                                if ($productStoreModel) {
                                    $productStoreModel->setData('vendor_product_id', $vendorProductItem->getId());
                                    $productStoreModel->setData('store_id', $storeId);

                                    $this->productStoreRepository->save($productStoreModel);
                                }

                                $vendorProductItem->save();
                                $updatedDataCount++;
                                if ($row['status'] == Product::STATUS_LISTED) {
                                    $products[$vendorProductItem->getVendorProductId()]['qty'] =
                                        $vendorProductItem->getQty();
                                    $products[$vendorProductItem->getVendorProductId()]['marketplace_product_id'] =
                                        $vendorProductItem->getMarketplaceProductId();
                                    $productIds[] = $vendorProductItem->getMarketplaceProductId();
                                    if ($vendorProductItem->getParentId()) {
                                        $parentIds[] = $vendorProductItem->getParentId();
                                    }
                                }
                            }
                            $eventParams = [
                                'marketplace_product_id' => $vendorProductItem->getMarketplaceProductId(),
                                'old_qty' => (int) $vendorProductItem->getQty(),
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
                                    'old_qty' => (int) $model->getQty(),
                                    'vendor_product' => $model
                                ];

                                $this->_eventManager->dispatch('vendor_product_list_after', $eventParams);

                                $model->unsetData();
                            }
                            $newDataCount++;
                        }
                    }
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
     * Parse and validate positive decimal value
     * Return false if value is not decimal or is not positive
     *
     * @param string $value
     * @return bool|float
     */
    protected function _parseDecimalValue($value)
    {
        if (!is_numeric($value)) {
            return false;
        }
        $value = (double)sprintf('%.4F', $value);
        if ($value < 0.0000) {
            return false;
        }
        return $value;
    }
}
