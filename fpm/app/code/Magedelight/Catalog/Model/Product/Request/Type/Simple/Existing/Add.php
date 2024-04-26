<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magedelight\Catalog\Model\Product\Request\Type\Simple\Existing;

use Magedelight\Catalog\Model\ProductRequest as VendorProductRequest;
use Magedelight\Catalog\Model\ProductRequestManagement;

/**
 * Simple product type implementation
 */
class Add extends \Magedelight\Catalog\Model\Product\Request\Type\Simple
{

    /**
     * create request to add offer to exsting simple product
     * @param $vendorId
     * @param $requestData
     * @return \Magento\Framework\Controller\ResultRedirectFactory
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute($vendorId, $requestData)
    {
        $errors = [];
        $catAttrSetId = $requestData['offer']['attribute_set_id'];
        $catId = $requestData['offer']['category_id'];
        $pid = $requestData['offer']['marketplace_product_id'];
        $vendorSku = $requestData['offer']['vendor_sku'];
        $offerPrice = $requestData['offer']['price'];
        if (array_key_exists(
            'product_request_id',
            $requestData['offer']
        ) && $prid = $requestData['offer']['product_request_id']) {
            $productRequest = $this->productRequestRepository->loadById($prid);
            $productRequest->setData('status', VendorProductRequest::STATUS_PENDING);
            $productRequest->setData('disapprove_message', '');
        } else {
            $productRequest = $this->productRequestInterface->create();
        }
        $errors = $this->productRequest->validate(
            ['label' => __('Seller Sku in offer section'), 'value' => $vendorSku],
            $errors
        );
        $errors = $this->productRequest->validate(
            ['label' => __('Price in offer section'), 'value' => $offerPrice],
            $errors
        );

        if ($this->helper->checkVendorSkuValidation()) {
            $error = $this->productRequest->checkUniqueSKU($vendorSku);
            if (!empty($error)) {
                throw new \Exception($error);
            }
        } else {
            $this->productRequest->checkUniqueSKUWithOtherVendor($vendorSku, $vendorId);
        }

        if (!empty($errors)) {
            $this->coreRegistry->register('vendor_current_product_request', $this->productRequest);
            throw new \Exception();
        }

        $productRequest->setData('vendor_id', $vendorId);

        $offerAttributes = $this->_getOfferAttributes();
        foreach ($offerAttributes as $key) {
            $value = (array_key_exists($key, $requestData['offer'])) ? $requestData['offer'][$key] : '';
            if ($value !== null && $value !== '') {
                $productRequest->setData($key, $value);
            }
        }

        $product = $this->_productRepository->getById($requestData['offer']['marketplace_product_id']);
        $productRequest->setData('name', $product->getName());
        $productRequest->setData('base_image', $this->jsonEncoder->encode($product->getImage()));
        $productRequest->setData('store_id', $this->_storeManager->getStore()->getId());
        $requestData['vital']['name'] = $product->getName();
        $productRequest->setData('website_ids', $this->_storeManager->getStore()->getWebsiteId());
        $productRequest->setData('status', ProductRequestManagement::STATUS_APPROVED_NON_LIVE);
        /* Make product request as offered product request.*/
        $productRequest->setData('is_offered', 1);
        /* Make product request as offered product request.*/

        try {
            $prodReq = $this->productRequestRepository->save($productRequest);
            if ($prodReq->getId()) {
                /* Save website data.*/
                $this->saveWebsiteData($requestData['offer'], $prodReq->getId());
                /* Save website data.*/

                /* Save store data.*/
                $this->saveStoreData($requestData, $prodReq->getId());
                /* Save store data.*/
                try {
                    if ($prodReq->getId()) {
                        $this->productEmailManagement->sendVendorNewProductRequestEmail($prodReq);
                    }
                } catch (\Exception $e) {
                    throw new \Exception(
                        __('Product Request has been created/updated but failed to send the email.
                         Please go to products listing to check your request.')
                    );
                }
            }
            return $this->resultRedirect;
        } catch (\Exception $e) {
            throw new \Exception(__($e->getMessage()));
        }
    }
}
