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
namespace Magedelight\Catalog\Model\Product\Request\Type;

use Magedelight\Catalog\Model\ProductRequest as VendorProductRequest;

class Configurable extends \Magedelight\Catalog\Model\Product\Request\Type\AbstractType
{

    /**
     * create request to create new simple product
     * @param $vendorId
     * @param $requestData
     * @param string $parentId
     * @param $variant
     * @param bool $isNew
     * @param bool $bulkimport
     * @return \Magento\Framework\Phrase
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function createRequestNewAssociateProduct(
        $vendorId,
        $requestData,
        $parentId = '',
        $variant,
        $isNew = true,
        $bulkimport = false
    ) {
        $errors = [];
        $this->handleImageRemoveError($requestData);
        $catId = $requestData['offer']['category_id'];
        $marketplaceProductId = $requestData['offer']['marketplace_product_id'];
        $currentStoreId = $this->_storeManager->getStore()->getId();
        if ($requestData['store'] == 'default' && $marketplaceProductId == '') {
            $requestData['store'] = '';
        }
        $storeId = (array_key_exists('store', $requestData) &&
            $requestData['store']!= '') ? $requestData['store'] : $currentStoreId;
        if (array_key_exists('website_ids', $requestData) &&
            isset($requestData['website_ids']) && !empty($requestData['website_ids'])) {
            $website_id = $requestData['website_ids'];
        } else {
            $store = $this->_storeManager->getStore(true);
            $website_id = [$store->getWebsiteId()];
        }
        $productRequest = $this->productRequestInterface->create();

        $vendorSku = $requestData['offer']['vendor_sku'];
        if ($marketplaceProductId == null || $marketplaceProductId == '') {
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

        $vitalData = [];
        $vitalAttributesArr = $this->getProcessedVitalAttributes($requestData, $errors);
        if (count($vitalAttributesArr['errors']) > 0) {
            $errors = $vitalAttributesArr['errors'];
        }

        $additionalAttributes = [];
        if (array_key_exists('additional', $requestData)) {
            $additionalAttributes = $requestData['additional'];
        }

        if (array_key_exists('weight', $variant)) {
            /* Add variant weight to additional attributes. */
            $additionalAttributes['weight'] = $variant['weight'];
        }

        $variantAttributes = [];
        if (array_key_exists('variant', $requestData)) {
            $variantAttributes = $requestData['variant'];
            foreach ($variantAttributes as $code => $attribute) {
                if (array_key_exists($code, $variant)) {
                    $variantAttributes[$code] = $variant[$code];
                }
            }
        }

        $mediaAttributes = [];
        if (array_key_exists('image', $variant) && $variant['image'] != '') {
            $mediaAttributes = $variant['image'];
            unset($mediaAttributes['name']);
            unset($mediaAttributes['images']); // remove images element to get mediaAttributes only.
        }

        $productRequest->setData('variants', $this->jsonEncoder->encode($variantAttributes));

        if (array_key_exists('vendor_product_id', $requestData['offer'])) {
            $productRequest->setData('vendor_product_id', $requestData['offer']['vendor_product_id']);
        }
        if (!$isNew || isset($requestData['edit'])) {
            $productRequest->setData('is_requested_for_edit', 1);
        }

        $this->processImages($productRequest, $variant);

        $offerPrice = $requestData['offer']['price'];

        $errors = $this->productRequest->validate(
            ['label' => __('Seller Sku in offer section'), 'value' => $vendorSku],
            $errors
        );
        $errors = $this->productRequest->validate(
            ['label' => __('Price in offer section'), 'value' => $offerPrice],
            $errors
        );

        $productRequest->setData('status', VendorProductRequest::STATUS_PENDING);
        $productRequest->setData('disapprove_message', '');

        if (!empty($website_id)) {
            $productRequest->setData('website_ids', implode(",", $website_id));
        }

        if (!empty($errors)) {
            throw new \Exception(implode(',', $errors));
        }
        $productRequest->setData('vendor_id', $vendorId);
        try {
            if (array_key_exists('name', $requestData['offer'])) {
                $productRequest->setData('name', $requestData['offer']['name']);
            }
            $offerAttributes = $this->_getOfferAttributes();
            foreach ($offerAttributes as $key) {
                $value = (array_key_exists($key, $requestData['offer'])) ? $requestData['offer'][$key] : '';
                if (!($value === null) && $value != '') {
                    $productRequest->setData($key, $value);
                } else {
                    $productRequest->unsetData($key);
                }
            }

            $productRequest->setData('store_id', $currentStoreId);
            $prodReq = $this->productRequestRepository->save($productRequest);
            if ($prodReq->getId()) {
                /* Save website data.*/
                $this->saveWebsiteData($requestData['offer'], $prodReq->getId());
                /* Save website data.*/

                /* Save store data.*/
                $requestData['attributes'] = array_merge(
                    $vitalAttributesArr,
                    $additionalAttributes,
                    $variantAttributes,
                    $mediaAttributes
                );
                $this->saveStoreData($requestData, $prodReq->getId());
                /* Save store data.*/

                if ($parentId) {
                    $link = $this->_configurableFactory->create();
                    $link->setData('product_request_id', $prodReq->getId());
                    $link->setData('parent_id', $parentId);
                    $link->save();
                }
            }
        } catch (\Exception $e) {
            return (__('There is some error while creating the product.'));
            //throw new \Exception(__('There is some error while creating the product.'));
        }
    }

    /**
     * @param array $requestData
     * @param array $errors
     * @return array
     */
    protected function getProcessedVitalAttributes(array $requestData = [], array $errors = [])
    {
        $vitalAttributesArr = [];
        if (array_key_exists('vital', $requestData)) {
            $vitalData = $requestData['vital'];
            $catAttrSetId = $requestData['offer']['attribute_set_id'];
            $attributes = $this->productRequest->getProductAttributes($catAttrSetId);
            $excludeAttributes = $this->productRequest->getExcludeAttributeList();
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
                    if ($code == 'name') {
                        if (array_key_exists('name', $requestData['offer'])) {
                            $value = $requestData['offer'][$code];
                        }
                    }
                    $errors = $this->productRequest->validate(['label' => $label, 'value' => $value], $errors);
                    $vitalAttributesArr[$code] = $value;
                }
            }
        }
        $vitalAttributesArr['errors'] = $errors;
        return $vitalAttributesArr;
    }

    /**
     *
     * @param $productRequest
     * @param array $requestData
     */
    protected function processImages($productRequest, array $requestData = [])
    {
        if (array_key_exists('image', $requestData) && $requestData['image'] != '') {
            $postDataObject = new \Magento\Framework\DataObject($requestData['image']);
            $productRequest->setData('images', $this->jsonEncoder->encode($postDataObject->getData('images')));
            $productRequest->setData('base_image', $this->jsonEncoder->encode($postDataObject->getData('image')));
        }
    }
}
