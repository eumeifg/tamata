<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magedelight\Catalog\Model\Product\Request\Type\Configurable;

use Magedelight\Catalog\Model\ProductRequest as VendorProductRequest;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable as CoreConfigurable;

class Add extends \Magedelight\Catalog\Model\Product\Request\Type\Configurable
{

    /**
     *
     * create request to create New configurable product
     * @param integer $vendorId
     * @param array $requestData
     * @param boolean $isNew
     * @throws \Exception
     */
    public function execute($vendorId, $requestData, $isNew = true)
    {
        $errors = [];
        if (isset($requestData['website_ids']) && !empty($requestData['website_ids'])) {
            $website_id = $requestData['website_ids'];
        } else {
            $store = $this->_storeManager->getStore(true);
            $website_id = [$store->getWebsiteId()];
        }
        $this->handleImageRemoveError($requestData);
        $marketplaceProductId = $requestData['offer']['marketplace_product_id'];
        $productRequestId = $requestData['offer']['product_request_id'];
        if ($productRequestId) {
            $productRequest = $this->productRequestRepository->getById($productRequestId);
        } else {
            $productRequest = $this->productRequestInterface->create();
            // $this->productRequestRepository->loadById($productRequestId);
        }

        if ($marketplaceProductId == null || $marketplaceProductId == '') {
            if ($this->helper->checkVendorSkuValidation() && $isNew) {
                $error = $this->productRequest->validateUniqueVariantsVendorSku($vendorId, $requestData);
                if (!empty($error)) {
                    throw new \Exception($error);
                }
            }
        }
        /* Get attributes data. */
        $attrArray = $this->getAttributes($requestData, 'vital');
        $additionalAttributes = $this->getAttributes($requestData, 'additional');
        $variantAttributes = $this->getAttributes($requestData, 'variant');
        $mediaAttributes = $this->getAttributes($requestData, 'product', $errors);
        unset($mediaAttributes['images']); // remove images element to get mediaAttributes only.
        /* Get attributes data. */

        /** set images gallery (extra images) */
        if (array_key_exists('product', $requestData) && array_key_exists('images', $requestData['product'])) {
            $productRequest->setData('images', $this->jsonEncoder->encode($requestData['product']['images']));
        }
        /** set base image */
        if (array_key_exists('product', $requestData) && array_key_exists('image', $requestData['product'])) {
            $base_image = $requestData['product']['image'];
            $productRequest->setData('base_image', $this->jsonEncoder->encode($base_image));
        }

        $this->prepareConfigurableData($productRequest, $requestData);

        if (!empty($errors)) {
            $this->coreRegistry->register('vendor_current_product_request', $productRequest);
            throw new \Exception();
        }

        $productRequest->setData('vendor_id', $vendorId);
        $productRequest->setData('type_id', CoreConfigurable::TYPE_CODE);

        if (array_key_exists('vital', $requestData)) {
            $requestData['offer']['vendor_sku'] = $this->createSkuFromName($requestData, $requestData['vital']['name']);
        }
        $offerAttributes = $this->_getOfferAttributesForConfigurable();

        foreach ($offerAttributes as $key) {
            $value = (array_key_exists($key, $requestData['offer'])) ? $requestData['offer'][$key] : '';
            if ($value !== null && $value !== '') {
                $productRequest->setData($key, $value);
            } else {
                $productRequest->unsetData($key);
            }
        }

        $productRequest->setData('status', VendorProductRequest::STATUS_PENDING);
        if (!$isNew || isset($requestData['edit'])) {
            $productRequest->setData('is_requested_for_edit', 1);
        }
        if (array_key_exists('vendor_product_id', $requestData['offer'])) {
            $productRequest->setData('vendor_product_id', $requestData['offer']['vendor_product_id']);
        }
        $productRequest->setData('disapprove_message', '');

        if (!empty($website_id)) {
            $productRequest->setData('website_ids', implode(",", $website_id));
        }
        $productRequest->setData('status', VendorProductRequest::STATUS_PENDING);
        $productRequest->setData('disapprove_message', '');

        $productRequest->setData('store_id', $this->_storeManager->getStore()->getId());

        try {
            $width = $this->helper->getImageWidth();
            $height = $this->helper->getImageHeight();
            $files = $this->request->getFiles('variants_data');
            $allowedFileTypes = ['jpg', 'jpeg', 'png'];
            $results = [];
            if (is_array($files)) {
				foreach ($files as $file) {
					if ($file['child_image']['name'] != '') {
						$uploader = $this->uploaderFactory->create(['fileId' => $file['child_image']]);
						$fileExtension = $this->ioFile->getPathInfo($file['child_image']['name'])['extension'];
						$uploader->setAllowedExtensions($allowedFileTypes);
						$commonText = __('Your product request was not created due to: ');
						if ($fileExtension && $uploader->checkAllowedExtension($fileExtension)) {
							if ($this->validateImageDimensions($file, $width, $height)) {
								$imageAdapter = $this->adapterFactory->create();
								$uploader->addValidateCallback(
									'catalog_product_image',
									$imageAdapter,
									'validateUploadFile'
								);
								$uploader->setAllowRenameFiles(true);
								$uploader->setFilesDispersion(true);
								$result = $uploader->save(
									$this->mediaDirectoryRead->getAbsolutePath($this->mediaConfig->getBaseTmpMediaPath())
								);
								$stringName = $this->randomStrings(11);
								$result['images'][$stringName]['position'] = '1';
								$result['images'][$stringName]['file'] = $result['file'];
								$result['images'][$stringName]['value_id'] = '';
								$result['images'][$stringName]['label'] = '';
								$result['images'][$stringName]['disabled'] = '0';
								$result['images'][$stringName]['media_type'] = 'image';
								$result['images'][$stringName]['removed'] = '';
								$result['images'][$stringName]['small_image'] = $result['file'];
								$result['images'][$stringName]['thumbnail'] = $result['file'];
								$result['image'] = $result['file'];
								$result['small_image'] = $result['file'];
								$result['thumbnail'] = $result['file'];
								unset($result['file']);
								unset($result['tmp_name']);
								unset($result['path']);
								unset($result['type']);
								unset($result['error']);
								unset($result['size']);
								$results[] = $result;
							} else {
								$results = [
									'error' => __('%1 Please upload image as per mentioned dimensions.
									i.e(%2px x %3px)', $commonText, $width, $height)
								];
							}
						} else {
							$results = [
								'error' => __('%1 Please upload file from given types %2', $commonText, implode(', ', $allowedFileTypes))
							];
						}
					} else {
						$results[] = '';
					}
				}
			} else {
                $results[] = '';
            }

            if (isset($results['error'])) {
                throw new \Exception($results['error']);
            }

            $prodReq = $this->productRequestRepository->save($productRequest);
            if ($prodReq->getId()) {
                /* Save website data.*/
                $this->saveWebsiteData($requestData['offer'], $prodReq->getId());
                /* Save website data.*/

                /* Save store data.*/
                $requestData['variants'] = $requestData['variants_data'];
                $requestData['attributes'] = array_merge($attrArray, $additionalAttributes, $mediaAttributes);
                $this->saveStoreData($requestData, $prodReq->getId());
                /* Save store data.*/

				if (!empty($result)) {
					$total = count($requestData['variants_data']);
					for ($k=0; $k<$total; $k++) {
						$requestData['variants_data'][$k]['image'] = $results[$k];
					}
				}

                foreach ($requestData['variants_data'] as $variant) {
                    foreach ($variant as $field => $value) {
                        $requestData['offer'][$field] = $value;
                    }
                    $this->createRequestNewAssociateProduct($vendorId, $requestData, $prodReq->getId(), $variant);
                }
            }
        } catch (\Exception $e) {
            throw new \Exception(__($e->getMessage()));
        }

        try {
            if ($prodReq->getId()) {
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
     * validate image dimensions
     * @param array $files
     * @param string $width
     * @param string $height
     * @return bool
     */
    protected function validateImageDimensions($files = [], $width = '', $height = '')
    {
        if (!empty($files)) {
            $image_dimensions_info = getimagesize($files["child_image"]["tmp_name"]);
            $image_width = $image_dimensions_info[0];
            $image_height = $image_dimensions_info[1];

            if ($width && $image_width < $width) {
                return false;
            }

            if ($height && $image_height < $height) {
                return false;
            }

            return true;
        }
    }

    /**
     * @param $length_of_string
     * @return bool|string
     */
    protected function randomStrings($length_of_string)
    {
        // String of all alphanumeric character
        $str_result = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
        // Shufle the $str_result and returns substring
        // of specified length
        return substr(
            str_shuffle($str_result),
            0,
            $length_of_string
        );
    }

    /**
     *
     * @return array
     */
    protected function _getAttributes()
    {
        return [
            'vendor_sku',
            'category_id',
            'attribute_set_id',
            'main_category_id',
            'marketplace_product_id'
        ];
    }

    /**
     *
     * @param array $requestData
     * @param string $string
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function createSkuFromName($requestData, $string = null)
    {
        if (($string === null) && !empty($requestData['offer']['marketplace_product_id'])) {
            $string = $this->_productRepository->getById($requestData['offer']['marketplace_product_id'])->getName();
            $string = substr($string, 0, 5) . $this->_mathRandom->getRandomString(8);
        } elseif (!($string === null) && $requestData['has_variants']) {
            $string = substr($string, 0, 5) . $this->_mathRandom->getRandomString(8);
        }

        //Lower case everything
        $string = strtolower($string);
        //Make alphanumeric (removes all other characters)
        $string = preg_replace("/[^a-z0-9_\s-]/", "", $string);
        //Clean up multiple dashes or whitespaces
        $string = preg_replace("/[\s-]+/", " ", $string);
        //Convert whitespaces and underscore to dash
        $string = preg_replace("/[\s_]/", "-", $string);

        return $string;
    }
}
