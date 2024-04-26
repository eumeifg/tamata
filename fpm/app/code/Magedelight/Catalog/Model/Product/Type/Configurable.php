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

use Magedelight\Catalog\Model\ProductRequestManagement;
use Magento\Catalog\Model\Product\Attribute\Source\Status as Status;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable as CoreConfigurable;

/**
 * Configurable product type implementation
 */
class Configurable extends \Magedelight\Catalog\Model\Product\Type\AbstractType
{
    protected $_vendorProductIds = [];

    /**
     * Create super produts and child products.
     * @param boolean $editMode
     * @return Config Product Object
     * @throws \Exception
     */
    public function createConfigurableProduct($editMode = false, $postData)
    {
        $varianData = $this->jsonDecoder->decode($postData['product']['variants']);
        $associateProductsData = [];
        $associatedProductIds = [];

        if ($editMode == false) {
            if (is_array($varianData)) {
                foreach ($varianData as $data) {
                    //create child associated product
                    $data['has_variants'] = 1;
                    $id = $this->createSimpleAssociateProduct($data, $postData);

                    $associatedProductIds[] = $id;
                    $associateProductsData[$id] = $data;
                }
            }
        }

        /* Set post value back to request data to upload images for Parent product
         * which was unset to remove images for child products. */
        $this->request->setPostValue('product', $postData['product']);
        /* Set post value Parent product. */

        /* Create cloned images to add image. */
        $this->createTmpImagesIfNotExists($postData);
        /* Create cloned images to add image. */

        if ($editMode) {
            foreach ($varianData as $data) {
                if (array_key_exists('vendor_product_id', $data) && isset($data['vendor_product_id'])) {
                    $associatedProductIds[] = $this->_simpleVendorProduct->updateVendorProductForEdit($data);
                }
            }
        }
        $params = [
            'set' => '',
            'type' => CoreConfigurable::TYPE_CODE
        ];
        if (array_key_exists('marketplace_product_id', $postData['product'])) {
            $params['id'] = $postData['product']['marketplace_product_id'];
        }

        $postData['params'] = $params;
        $postData['type'] = CoreConfigurable::TYPE_CODE;
        $cp = $this->initializationHelper->initialize(
            $this->build($postData)
        );

        $cp->setName($postData['product']['name']);

        if ($editMode == false) {
            $cp->setSku($this->_mathRandom->getRandomString(8));
            $cp->setAttributeSetId($postData['product']['attribute_set_id']);
            $cp->setStatus(Status::STATUS_ENABLED);
            $cp->setCategoryIds([$postData['product']['category_id']]);
            $cp->setStockData(
                [
                'use_config_manage_stock' => 0,
                'manage_stock' => 1,
                'is_in_stock' => 1,
                ]
            );

            // do not update URL key for existing loaded product
            if (!isset($params['id'])) {
                $cp->setUrlKey(str_replace(' ', '-', strtolower($postData['product']['name'])) . '-' .
                    $this->_mathRandom->getRandomString(6, '0123456789'));
            }

            foreach ($postData as $key => $value) {
                if (in_array($key, $this->_getExcludeAttributes())) {
                    continue;
                }
                $cp->setData($key, $value);
            }

            $configurableAttributesData = $this->_getConfigurableAttributesData($associateProductsData, $postData);
            $configurableOptions = $this->optionFactory->create($configurableAttributesData);

            $extensionConfigurableAttributes = $cp->getExtensionAttributes();
            $extensionConfigurableAttributes->setConfigurableProductOptions($configurableOptions);

            $extensionConfigurableAttributes->setConfigurableProductLinks($associatedProductIds);
            $cp->setExtensionAttributes($extensionConfigurableAttributes);
        }
        if (array_key_exists('website_ids', $postData['product'])) {
            if (is_array($postData['product']['website_ids'])) {
                $websiteId = $postData['product']['website_ids'];
            } else {
                $websiteId = explode(',', $postData['product']['website_ids']);
            }
        } else {
            $websiteId = [$this->_storeManager->getDefaultStoreView()->getWebsiteId()];
        }

        $websiteId = (array_key_exists('website_id', $postData['product'])) ?
            $postData['product']['website_id'] : $this->_storeManager->getDefaultStoreView()->getWebsiteId();
        $cp->setWebsiteIds([$websiteId]);
        $cp->setPrice(0);
        $cp->setVisibility(
            \Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH
        );

        try {
            $cp->save();
            $this->handleImageRemoveErrorProduct($postData, $cp->getId());
            $this->getCategoryLinkManagement->assignProductToCategories(
                $cp->getSku(),
                $cp->getCategoryIds()
            );
            /* Delete cloned images. */
            $this->deleteTmpImages($postData);
            /* Delete cloned images. */

            return $cp;
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    /**
     * Create temporary images to resolve images not found issue while creating product.
     * @param type $postData
     */
    protected function createTmpImagesIfNotExists($postData = [], $isAssociate = false)
    {
        if (array_key_exists('media_gallery', $postData['product'])) {
            foreach ($postData['product']['media_gallery']['images'] as $key => $image) {
                $imageExists = $this->helper->getTmpFileIfExists($image['file']);
                if (!$imageExists) {
                    if ($this->helper->getTmpFileIfExists('/clone' . $image['file'])) {
                        $this->helper->cloneTmpFile('clone' . $image['file'], $image['file']);
                    } else {
                        $this->helper->createTmpImageIfNotExists($image['file']);
                    }
                } else {
                    if ($isAssociate) {
                        /* For child products only. */
                        $this->helper->cloneTmpFile($image['file'], 'clone' . $image['file']);
                    }
                }
            }
        }
    }

    /**
     * Delete temporary created images once product is created.
     * @param type $postData
     */
    protected function deleteTmpImages($postData = [])
    {
        if (array_key_exists('media_gallery', $postData['product'])) {
            foreach ($postData['product']['media_gallery']['images'] as $key => $image) {
                if ($this->helper->getTmpFileIfExists('/clone' . $image['file'])) {
                    $this->helper->deleteTmpFile('clone' . $image['file']);
                }
            }
        }
    }

    /**
     * Create simple product with Visibility: Not Visible Individually status
     * @param array $data
     * @return int Product ID
     */
    public function createSimpleAssociateProduct($data, $postData)
    {
        /*
         * Removed existing logic to clone images for child products to save same images for all child products.
         * Images will not be saved for child products now.
         * Unset media_gallery for child products.
         */
        if (array_key_exists('media_gallery', $postData['product'])) {
            unset($postData['product']['media_gallery']);
            $this->request->setPostValue('product', $postData['product']);
        }
        /* Unset media_gallery for child products. */
        if (isset($data['images'])) {
            $postData['product']['media_gallery']['images'] = $this->jsonDecoder->decode($data['images']);
            $postData['product']['images'] = $data['images'];
            $postData['product']['base_image'] = $data['image'];
            $postData['product']['image'] = $data['image'];
            $postData['product']['small_image'] = $data['image'];
            $postData['product']['thumbnail'] = $data['image'];
            $data['media_gallery']['images'] = $this->jsonDecoder->decode($data['images']);
        }
        $params = [
            'set' => '',
            'type' => \Magento\Catalog\Model\Product\Type::DEFAULT_TYPE
        ];

        if (array_key_exists('vendor_product_id', $data) &&
            isset($data['vendor_product_id']) &&
            array_key_exists('marketplace_product_id', $data) &&
            isset($data['marketplace_product_id'])) {
            $params['id'] = $data['marketplace_product_id'];
        }

        /* remove extension attributes ( swatch options attributes which we added using hydrators for API)
        because of it product extension attribute not able to access */
        unset($postData['product']['extension_attributes']);

        $postData['params'] = $params;
        $product = $this->initializationHelper->initialize(
            $this->build($postData)
        );

        $product->setData('sku', $this->_mathRandom->getRandomString(8));

        $data = array_replace($postData['product'], $data); // overwrite same index value from data to postdata
        /*echo "After <pre>";var_dump($postData['product']);die;*/
        if (!$product->getId()) {
            foreach ($data as $key => $value) {
                if (in_array($key, $this->_getExcludeAttributes())) {
                    continue;
                }
                if (!is_array($value)) {
                    $product->setData($key, $value);
                }
            }
            //$product->setData('name', $data['vendor_sku']);

            // do not update URL key for existing loaded product
            if (!isset($params['id'])) {
                $product->setUrlKey(str_replace(' ', '-', strtolower($postData['product']['name'])) . '-' .
                    $this->_mathRandom->getRandomString(5, '0123456789'));
            }

            $product->setData(
                'stock_data',
                [
                    'use_config_manage_stock' => 0,
                    'manage_stock' => 1,
                    'is_in_stock' => 1,
                    'qty' => $data['qty']
                ]
            );
            $product->setVisibility(
                \Magento\Catalog\Model\Product\Visibility::VISIBILITY_NOT_VISIBLE
            );
            if (isset($data['images'])) {
                $product->setData('media_gallery', ['images' => $this->jsonDecoder->decode($data['images'])]);
            }
        }

        $product->setCategoryIds($postData['product']['category_ids']);
        $websiteId = (array_key_exists('website_id', $postData['product'])) ?
            $postData['product']['website_id'] : $this->_storeManager->getDefaultStoreView()->getWebsiteId();
        $product->setWebsiteIds([$websiteId]);

        try {
            $product->save();
            $this->getCategoryLinkManagement->assignProductToCategories(
                $product->getSku(),
                $product->getCategoryIds()
            );

            $this->_vendorProductIds[] = $this->_simpleVendorProduct->createVendorProduct(
                $product,
                $data,
                false,
                $productType = ProductRequestManagement::CORE_PRODUCT_TYPE_ASSOCIATED,
                $data['vendor_sku'],
                $postData
            );
            return $product->getId();
        } catch (\Exception $e) {
            throw new \Exception($data['vendor_sku'] . ' -- ' . $e->getMessage());
        }
    }

    /**
     * @return array
     */
    public function getVendorProductIds()
    {
        return $this->_vendorProductIds;
    }

    /**
     *
     * @param type $associateProductsData
     * @return array $configurableAttributesData
     */
    protected function _getConfigurableAttributesData($associateProductsData, $postData)
    {
        $configurableAttributesData = [];

        if ($this->getUsedConfigurableAttributeCodes($postData)) {
            $position = 0;
            foreach ($this->getUsedConfigurableAttributeCodes($postData) as $attId => $code) {
                $attributeValues = [];

                foreach ($associateProductsData as $productId => $value) {
                    $attributeValues[$value[$code]] = [
                        'label'         => $code,
                        'attribute_id'  => $attId,
                        'value_index'   => $value[$code]
                    ];
                }

                $configurableAttributesData[$attId] = [
                    'attribute_id'  => $attId,
                    'code'          =>  $code,
                    'label'         => $code,
                    'position'      =>  $position,
                    'values'        => $attributeValues,
                ];
                $position++;
            }
        }

        return $configurableAttributesData;
    }

    /**
     * Retrive attribute codes which used for configurable product.
     * @return boolean false | Array
     */
    public function getUsedConfigurableAttributeCodes($postData)
    {
        if ($postData['product']['used_product_attribute_ids']) {
            $IdsCodes = $this->jsonDecoder->decode($postData['product']['used_product_attribute_ids']);
            return $IdsCodes;
        }
        return false;
    }

    /**
     * List of fields which doesn't required while create product
     * @return array
     */
    protected function _getExcludeAttributes()
    {
        return  [
            'vendor_id',
            'configurable_attributes',
            'configurable_attribute_codes',
            'configurable_attributes_data',
            'vendor_sku',
            'product_request_id',
            'has_variants',
            'old_status',
            'can_list',
            'disapprove_message',
            'marketplace_product_id',
            'vendor_product_id',
            'catalog_number',
            'is_requested_for_edit',
            'status',
            'is_deleted',
            'approved_at',
            'variants',
            'attributes',
            'used_product_attribute_ids',
            'product',
            'visibility'
        ];
    }
}
