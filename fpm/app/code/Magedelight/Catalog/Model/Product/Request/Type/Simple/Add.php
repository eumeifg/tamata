<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magedelight\Catalog\Model\Product\Request\Type\Simple;

use Magedelight\Catalog\Model\ProductRequest as VendorProductRequest;

/**
 * Simple product type implementation
 */
class Add extends \Magedelight\Catalog\Model\Product\Request\Type\Simple
{

    /**
     * create request to create new simple product
     */
    public function execute($vendorId, $requestData, $isNew = true, $bulkimport = false)
    {
        $errors = [];
        $this->handleImageRemoveError($requestData);
        $productRequestId = $requestData['offer']['product_request_id'];

        if (array_key_exists('website_ids', $requestData) &&
            isset($requestData['website_ids']) && !empty($requestData['website_ids'])) {
            $website_id = $requestData['website_ids'];
        } else {
            $store = $this->_storeManager->getStore(true);
            $website_id = [$store->getWebsiteId()];
        }

        if ($productRequestId) {
            $productRequest = $this->productRequestRepository->getById($productRequestId);
        } else {
            $productRequest = $this->productRequestInterface->create();
        }

        /* validate vendor sku*/
        $this->validateVendorSku($vendorId, $requestData, $isNew);
        /* validate vendor sku*/

        /* Get attributes data. */
        $attrArray = $this->getAttributes($requestData, 'vital');
        $additionalAttributes = $this->getAttributes($requestData, 'additional');
        $variantAttributes = $this->getAttributes($requestData, 'variant');
        $mediaAttributes = $this->getAttributes($requestData, 'product', $errors);
        unset($mediaAttributes['images']); // remove images element to get mediaAttributes only.
        /* Get attributes data. */

        /* When edited approved product. */
        if (array_key_exists('vendor_product_id', $requestData['offer'])) {
            $productRequest->setData('vendor_product_id', $requestData['offer']['vendor_product_id']);
        }

        if (!$isNew || isset($requestData['edit'])) {
            $isNew = false;
            $productRequest->setData('is_requested_for_edit', 1);
        }
        /* When edited approved product. */

        /* Set images data. */
        if (array_key_exists('product', $requestData) && array_key_exists('images', $requestData['product'])) {
            $productRequest->setData('images', $this->jsonEncoder->encode($requestData['product']['images']));
            foreach ($requestData['product']['images'] as $item) {
                if (!strrpos($item['file'], '.tmp') &&
                    $this->_file->isExists($this->mediaDirectory->getAbsolutePath(
                        $this->mediaConfig->getMediaPath($item['file'])
                    ))
                ) {
                    $this->mediaDirectory->copyFile(
                        $this->mediaConfig->getMediaPath($item['file']),
                        $this->mediaConfig->getTmpMediaPath($item['file'])
                    );
                }
            }
        }
        /** set base image */
        if (array_key_exists('product', $requestData) && array_key_exists('image', $requestData['product'])) {
            $base_image = $requestData['product']['image'];
            $productRequest->setData('base_image', $this->jsonEncoder->encode($base_image));
        }
        /* Set images data. */

        $offerPrice = $requestData['offer']['price'];
        $errors = $this->productRequest->validate(
            ['label' => __('Seller Sku in offer section'), 'value' => $requestData['offer']['vendor_sku']],
            $errors
        );
        $errors = $this->productRequest->validate(
            ['label' => __('Price in offer section'), 'value' => $offerPrice],
            $errors
        );

        $productRequest->setData('vendor_id', $vendorId);
        $productRequest->setData('variants', $this->jsonEncoder->encode($variantAttributes));
        $productRequest->setData('status', VendorProductRequest::STATUS_PENDING);

        if (!empty($website_id)) {
            $productRequest->setData('website_ids', implode(",", $website_id));
        }

        if (!empty($errors)) {
            throw new \Exception(implode(',', $errors));
        }
        try {
            $offerAttributes = $this->_getOfferAttributes();
            foreach ($offerAttributes as $key) {
                $value = (array_key_exists($key, $requestData['offer'])) ? $requestData['offer'][$key] : '';
                if (!($value === null) && $value != '') {
                    $productRequest->setData($key, $value);
                } else {
                    $productRequest->unsetData($key);
                }
            }

            $productRequest->setData('store_id', $this->_storeManager->getStore()->getId());
            $prodReq = $this->productRequestRepository->save($productRequest);
            if ($prodReq->getId()) {
                /* Save website data.*/
                $this->saveWebsiteData($requestData['offer'], $prodReq->getId());
                /* Save website data.*/
                /* Save store data.*/
                $requestData['attributes'] = array_merge(
                    $attrArray,
                    $additionalAttributes,
                    $variantAttributes,
                    $mediaAttributes
                );
                $this->saveStoreData($requestData, $prodReq->getId(), $isNew);
                /* Save store data.*/
                $eventParams = ['post_data' => $this->postData,'request' => $prodReq];
                $this->_eventManager->dispatch('simple_product_request_new_after', $eventParams);
            }
        } catch (\Exception $e) {
            return (__('There is some error while creating the product.'));
            //throw new \Exception(__('There is some error while creating the product.'));
        }

        try {
            if ($prodReq->getId() && !$bulkimport) {
                $this->productEmailManagement->sendVendorNewProductRequestEmail($prodReq, $requestData);
            }
        } catch (\Exception $e) {
            throw new \Exception(
                __('Product Request has been created/updated but failed to send the email.
                 Please go to products listing to check your request.')
            );
        }
    }

    /**
     *
     * @param array $requestData
     * @param string $type
     * @return array
     */
    protected function getAttributes($requestData, $type, $errors = [])
    {
        $attributesData = [];
        if ($type == 'vital') {
            $vitalData = [];
            $attrArray = [];
            if (array_key_exists('vital', $requestData)) {
                $vitalData = $requestData['vital'];
                $catAttrSetId = $requestData['offer']['attribute_set_id'];
                $attributes = $this->productRequest->getProductAttributes($catAttrSetId);
                $excludeAttributes = $this->productRequest->getExcludeAttributeList();
                $attrArray = [];
                foreach ($attributes as $key => $attribute) {
                    $label = $attribute->getStoreLabel();
                    $code = $attribute->getAttributeCode();
                    if ($attribute->getIsVisible() &&
                            !in_array($attribute->getAttributeId(), $excludeAttributes) &&
                            !($attribute->getFrontendInput() === null) &&
                            $attribute->getIsRequired() &&
                            !($attribute->getFrontendInput() == 'select' &&
                                $attribute->getIsUserDefined() && $attribute->getIsGlobal()) &&
                            array_key_exists($code, $vitalData)
                        ) {
                        $value = $requestData['vital'][$code];
                        $errors = $this->productRequest->validate(['label' => $label, 'value' => $value], $errors);
                        $attrArray[$code] = $value;
                    }
                }
            }
            return $attrArray;
        } else {
            if (array_key_exists($type, $requestData)) {
                $attributesData = $requestData[$type];
            }
            return $attributesData;
        }
    }

    protected function validateVendorSku($vendorId, $requestData = [], $isNew)
    {
        $vendorSku = $requestData['offer']['vendor_sku'];
        if ($requestData['offer']['marketplace_product_id'] == null ||
            $requestData['offer']['marketplace_product_id'] == '') {
            if (!$this->helper->checkVendorSkuValidation()) {
                $this->productRequest->checkUniqueSKUWithOtherVendor($vendorSku, $vendorId);
            } else {
                $this->productRequest->checkUniqueSKU($vendorSku);
            }
        }

        if ($this->helper->checkVendorSkuValidation() && $isNew) {
            $error = $this->productRequest->validateUniqueVendorSku($vendorId, $vendorSku, $isNew);

            if (!empty($error)) {
                throw new \Exception($error);
            }
        }
    }
}
