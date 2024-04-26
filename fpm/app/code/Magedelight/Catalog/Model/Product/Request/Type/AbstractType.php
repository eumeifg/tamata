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

use Magedelight\Catalog\Api\Data\ProductRequestInterfaceFactory as ProductRequestInterface;
use Magedelight\Catalog\Api\ProductRequestRepositoryInterface;
use Magedelight\Catalog\Block\Sellerhtml\Sellexisting\Result;
use Magedelight\Catalog\Model\ProductRequest as VendorProductRequest;
use Magento\Catalog\Model\Product\Type;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magedelight\Catalog\Model\ResourceModel\ProductRequestWebsite\CollectionFactory as ProductRequestWebsiteCollectionFactory;
use Magedelight\Catalog\Model\ResourceModel\ProductRequestStore\CollectionFactory as ProductRequestStoreCollectionFactory;

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

    /**
     * @var \Magento\Framework\Json\Decoder
     */
    protected $jsonDecoder;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Framework\Filesystem
     */
    protected $mediaDirectory;

    /**
     * @var \Magento\Framework\Filesystem
     */
    protected $mediaDirectoryRead;

    /**
     * @var \Magento\Catalog\Model\Product\Media\Config
     */
    protected $mediaConfig;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var \Magento\Framework\Math\Random
     */
    protected $_mathRandom;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $_productRepository;

    /**
     * @var \Magedelight\Catalog\Model\ProductEmailManagement
     */
    protected $productEmailManagement;

    /**
     * @var \Magento\Framework\Json\Encoder
     */
    protected $jsonEncoder;

    /**
     * @var VendorProductRequest
     */
    protected $productRequest;

    /**
     * @var array variable
     */
    protected $postData;

    /**
     * @var \Magento\Framework\Controller\ResultRedirectFactory
     */
    protected $resultRedirect;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;

    protected $sellProducts;

    protected $vendorProductFactory;

    protected $directoryList;

    /**
     * @var \Magedelight\Catalog\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magento\Framework\Escaper
     */
    protected $_escaper;

    /**
     * @var \Magedelight\Catalog\Model\ProductRequestWebsiteFactory
     */
    protected $_productRequestWebsiteFactory;

    /**
     * @var \Magedelight\Catalog\Model\ProductRequestStoreFactory
     */
    protected $_productRequestStoreFactory;

    /**
     * @var ProductRequestWebsiteCollectionFactory
     */
    protected $_productRequestWebsiteCollectionFactory;

    /**
     * @var ProductRequestStoreCollectionFactory
     */
    protected $_productRequestStoreCollectionFactory;

    /**
     * @var \Magedelight\Catalog\Model\Product\Request\ConfigurableFactory
     */
    protected $_configurableFactory;

    /**
     * @var \Magento\Framework\Filesystem\Driver\File
     */
    protected $_file;

    /**
     * @var \Magedelight\Catalog\Model\ProductRequestFactory
     */
    protected $productRequestFactory;

    /**
     * @var ProductRequestRepositoryInterface
     */
    protected $productRequestRepository;

    /**
     * @var ProductRequestInterface
     */
    protected $productRequestInterface;

    /**
     * @var \Magento\Framework\Event\Manager
     */
    protected $_eventManager;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;
    /**
     * @var \Magento\MediaStorage\Model\File\UploaderFactory
     */
    protected $uploaderFactory;
    /**
     * @var \Magento\Framework\Filesystem\Io\File
     */
    protected $ioFile;
    /**
     * @var \Magento\Framework\Image\AdapterFactory
     */
    protected $adapterFactory;

    /**
     *
     * @param \Magedelight\Backend\App\Action\Context $context
     * @param \Magento\Framework\Json\Encoder $jsonEncoder
     * @param \Magento\Framework\Json\Decoder $jsonDecoder
     * @param \Magedelight\Catalog\Model\ProductRequestFactory $productRequestFactory
     * @param ProductRequestRepositoryInterface $productRequestRepository
     * @param ProductRequestInterface $productRequestInterface
     * @param \Magedelight\Catalog\Model\ProductEmailManagement $productEmailManagement
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepositoryInterface
     * @param \Magento\Framework\Math\Random $mathRandom
     * @param \Magento\Catalog\Model\Product\Media\Config $mediaConfig
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Catalog\Model\ProductFactory $vendorProductFactory
     * @param Result $sellProducts
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Event\Manager $eventManager
     * @param \Magedelight\Catalog\Helper\Data $helper
     * @param \Magento\Framework\Escaper $_escaper
     * @param \Magedelight\Catalog\Model\ProductRequestWebsiteFactory $productRequestWebsiteFactory
     * @param \Magedelight\Catalog\Model\ProductRequestStoreFactory $productRequestStoreFactory
     * @param ProductRequestWebsiteCollectionFactory $productRequestWebsiteCollectionFactory
     * @param ProductRequestStoreCollectionFactory $productRequestStoreCollectionFactory
     * @param \Magedelight\Catalog\Model\Product\Request\ConfigurableFactory $configurableFactory
     * @param \Magento\Framework\Filesystem\Driver\File $file
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\MediaStorage\Model\File\UploaderFactory $uploaderFactory
     * @param \Magento\Framework\Filesystem\Io\File $ioFile
     * @param \Magento\Framework\Image\AdapterFactory $adapterFactory
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function __construct(
        \Magedelight\Backend\App\Action\Context $context,
        \Magento\Framework\Json\Encoder $jsonEncoder,
        \Magento\Framework\Json\Decoder $jsonDecoder,
        \Magedelight\Catalog\Model\ProductRequestFactory $productRequestFactory,
        ProductRequestRepositoryInterface $productRequestRepository,
        ProductRequestInterface $productRequestInterface,
        \Magedelight\Catalog\Model\ProductEmailManagement $productEmailManagement,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepositoryInterface,
        \Magento\Framework\Math\Random $mathRandom,
        \Magento\Catalog\Model\Product\Media\Config $mediaConfig,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Catalog\Model\ProductFactory $vendorProductFactory,
        Result $sellProducts,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Event\Manager $eventManager,
        \Magedelight\Catalog\Helper\Data $helper,
        \Magento\Framework\Escaper $_escaper,
        \Magedelight\Catalog\Model\ProductRequestWebsiteFactory $productRequestWebsiteFactory,
        \Magedelight\Catalog\Model\ProductRequestStoreFactory $productRequestStoreFactory,
        ProductRequestWebsiteCollectionFactory $productRequestWebsiteCollectionFactory,
        ProductRequestStoreCollectionFactory $productRequestStoreCollectionFactory,
        \Magedelight\Catalog\Model\Product\Request\ConfigurableFactory $configurableFactory,
        \Magento\Framework\Filesystem\Driver\File $file,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\MediaStorage\Model\File\UploaderFactory $uploaderFactory,
        \Magento\Framework\Filesystem\Io\File $ioFile,
        \Magento\Framework\Image\AdapterFactory $adapterFactory
    ) {
        $this->productRequest = $productRequestFactory->create();
        $this->productRequestFactory = $productRequestFactory;
        $this->productRequestRepository = $productRequestRepository;
        $this->productRequestInterface = $productRequestInterface;
        $this->jsonEncoder = $jsonEncoder;
        $this->jsonDecoder = $jsonDecoder;
        $this->productEmailManagement = $productEmailManagement;
        $this->coreRegistry = $coreRegistry;
        $this->_productRepository = $productRepositoryInterface;
        $this->_mathRandom = $mathRandom;
        $this->mediaConfig = $mediaConfig;
        $this->mediaDirectory = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $this->mediaDirectoryRead = $filesystem->getDirectoryRead(DirectoryList::MEDIA);
        $this->_storeManager = $storeManager;
        $this->vendorProductFactory = $vendorProductFactory;
        $this->sellProducts = $sellProducts;
        $this->_eventManager = $eventManager;
        $this->helper = $helper;
        $this->_escaper = $_escaper;
        $this->_productRequestWebsiteFactory = $productRequestWebsiteFactory;
        $this->_productRequestStoreFactory = $productRequestStoreFactory;
        $this->_productRequestWebsiteCollectionFactory = $productRequestWebsiteCollectionFactory;
        $this->_productRequestStoreCollectionFactory = $productRequestStoreCollectionFactory;
        $this->_configurableFactory = $configurableFactory;
        $this->_file = $file;
        $this->request = $request;
        $this->uploaderFactory = $uploaderFactory;
        $this->ioFile = $ioFile;
        $this->adapterFactory = $adapterFactory;
    }

    /**
     *
     * @param array $postData
     * @param integer $productRequestId
     * @throws \Exception
     */
    protected function saveWebsiteData($postData, $productRequestId)
    {
        $request = new \Magento\Framework\DataObject($postData);
        $productRequestWebsite = $this->_productRequestWebsiteFactory->create();
        $productRequestWebsite->setData('condition', $request->getCondition());
        $productRequestWebsite->setData('price', $request->getPrice());
        $productRequestWebsite->setData(
            'special_price',
            ($request->getSpecialPrice()) ? $request->getSpecialPrice() : null
        );
        $productRequestWebsite->setData('special_from_date', $request->getSpecialFromDate());
        $productRequestWebsite->setData('special_to_date', $request->getSpecialToDate());
        $productRequestWebsite->setData('reorder_level', $request->getReorderLevel());
        $productRequestWebsite->setData('product_request_id', $productRequestId);
        $productRequestWebsite->setData('category_id', $request->getCategoryId());
        $productRequestWebsite->setData('website_id', $this->_storeManager->getStore()->getWebsiteId());
        $productRequestWebsite->setData('warranty_type', $request->getWarrantyType());
        $productRequestWebsite->setData('cost_price_iqd', $request->getCostPriceIqd());
        $productRequestWebsite->setData('cost_price_usd', $request->getCostPriceUsd());
        try {
            $productRequestWebsite->save();
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    /**
     *
     * @param array $postData
     * @param integer $productRequestId
     * @throws \Exception
     */
    protected function updateWebsiteData($postData, $productRequestId)
    {
        $request = new \Magento\Framework\DataObject($postData);
        $collection = $this->_productRequestWebsiteCollectionFactory->create();
        $productRequestWebsite = $collection->addFieldToFilter('product_request_id', $productRequestId)
            ->addFieldToFilter('website_id', $this->_storeManager->getStore()->getWebsiteId())->getFirstItem();

        if ($productRequestWebsite->getId()) {
            $productRequestWebsite->setData('condition', $request->getCondition());
            $productRequestWebsite->setData('price', $request->getPrice());
            $productRequestWebsite->setData(
                'special_price',
                ($request->getSpecialPrice()) ? $request->getSpecialPrice() : null
            );
            $productRequestWebsite->setData('special_from_date', $request->getSpecialFromDate());
            $productRequestWebsite->setData('special_to_date', $request->getSpecialToDate());
            $productRequestWebsite->setData('reorder_level', $request->getReorderLevel());
            $productRequestWebsite->setData('warranty_type', $request->getWarrantyType());
            try {
                $productRequestWebsite->save();
            } catch (\Exception $e) {
                throw new \Exception($e->getMessage());
            }
        }
    }

    /**
     *
     * @param array $postData
     * @param integer $productRequestId
     * @param booelan $isNew
     * @throws \Exception
     */
    protected function saveStoreData($postData, $productRequestId, $isNew = true)
    {
        $storeData = $this->processStoreData($postData);
        /* To Add All store entry */
        $stores = $this->getAllStoreIds();
        $request = new \Magento\Framework\DataObject($storeData);
        try {
            foreach ($stores as $storeId) {
                if (!$isNew && $storeId != $this->_storeManager->getStore()->getId()) {
                    /* Add current store data only if existing approved product is edited. */
                    continue;
                }
                $productRequestStore = $this->_productRequestStoreFactory->create();
                $productRequestStore->setData('product_request_id', $productRequestId);
                $productRequestStore->setData('condition_note', $request->getConditionNote());
                $productRequestStore->setData('warranty_description', $request->getWarrantyDescription());
                $productRequestStore->setData('name', $request->getName());
                $productRequestStore->setData(
                    'attributes',
                    ($request->getAttributes()) ? $this->jsonEncoder->encode($request->getAttributes()) : null
                );
                $productRequestStore->setData('website_id', $this->_storeManager->getStore()->getWebsiteId());
                $productRequestStore->setData('store_id', $storeId);
                $productRequestStore->save();
            }
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    /**
     * return []
     */
    private function getAllStoreIds()
    {
        $storeManagerDataList = $this->_storeManager->getStores();
        $options = [];
        $options[] = 0;
        foreach ($storeManagerDataList as $key => $value) {
            $options[] = $key;
        }
        return $options;
    }

    /**
     *
     * @param integer $productRequestId
     * @param integer $storeId
     */
    protected function getProductRequestStoreData($productRequestId, $storeId)
    {
        $collection = $this->_productRequestStoreCollectionFactory->create();
        return $collection->addFieldToFilter('product_request_id', $productRequestId)
                ->addFieldToFilter('store_id', $storeId)
                ->addFieldToFilter('website_id', $this->_storeManager->getStore()->getWebsiteId())
                ->getFirstItem();
    }

    /**
     *
     * @param array $postData
     * @param integer $productRequestId
     * @throws \Exception
     */
    protected function updateStoreData($postData, $productRequestId)
    {
        $storeData = $this->processStoreData($postData);
        $request = new \Magento\Framework\DataObject($storeData);
        $storeId = (!$request->getStoreId()) ? 0 : $request->getStoreId();
        $productRequestStore = $this->getProductRequestStoreData($productRequestId, $storeId);

        if ($productRequestStore->getId()) {
            try {
                $productRequestStore->setData('condition_note', $request->getConditionNote());
                $productRequestStore->setData('warranty_description', $request->getWarrantyDescription());
                if (!array_key_exists('is_offered', $postData)) {
                    /* Skip updating product name for sell existing products. */
                    $productRequestStore->setData('name', $request->getName());
                }
                $productRequestStore->setData(
                    'attributes',
                    ($request->getAttributes()) ? $this->jsonEncoder->encode($request->getAttributes()) : null
                );
                $productRequestStore->save();
            } catch (\Exception $e) {
                throw new \Exception($e->getMessage());
            }
        }
    }

    /**
     *
     * @param array $data
     * @return array
     */
    protected function excludeWebsiteFields($data = [])
    {
        foreach ($data as $index => $value) {
            if (in_array($index, $this->getWebsiteDataColumns())) {
                unset($data[$index]);
            }
        }
        return $data;
    }

    /**
     * @param array $requestData
     * @return array
     */
    public function processStoreData($requestData)
    {
        $data = [];
        if (array_key_exists('vital', $requestData)) {
            $data['name'] = $requestData['vital']['name'];
        }

        if (array_key_exists('offer', $requestData)) {
            if (array_key_exists('name', $requestData['offer'])) {
                $data['name'] = $requestData['offer']['name'];
            }
        }

        if (array_key_exists('attributes', $requestData)) {
            $data['attributes'] = $requestData['attributes'];
        }

        $data['condition_note'] = array_key_exists(
            'condition_note',
            $requestData['offer']
        ) ? $requestData['offer']['condition_note'] : null;
        $data['warranty_description'] = array_key_exists(
            'warranty_description',
            $requestData['offer']
        ) ? $requestData['offer']['warranty_description'] : null;
        $data['store_id'] = array_key_exists('store', $requestData) ? $requestData['store'] : 0;
        return $data;
    }

    /**
     * delele removed image element from postData Array
     * @param array $requestData
     * @return void
     */
    protected function handleImageRemoveError($requestData)
    {
        if (isset($requestData['product']['images'])) {
            $removableKeys = [];
            foreach ($requestData['product']['images'] as $key => $image) {
                if (!empty($image['removed'])) {
                    $removableKeys[] = $key;
                }
            }
            // unset removed image element from postArray
            if (count($removableKeys)) {
                foreach ($removableKeys as $key) {
                    unset($requestData['product']['images'][$key]);
                }
            }
        }
    }

    /**
     *
     * @param type $productRequest
     * @param array $requestData
     * @param string $websiteId
     */
    protected function prepareConfigurableData($productRequest, $requestData, $websiteId = 'default')
    {
        $catId = $requestData['offer']['category_id'];

        if (array_key_exists('has_variants', $requestData) &&
            $requestData['has_variants'] && array_key_exists('variants_data', $requestData)) {
            $productRequest->setData('has_variants', $requestData['has_variants']);

            $productRequest->setData('used_product_attribute_ids', $requestData['usedAttributeIds']);

            if (!empty($requestData['usedAttributeIds'])) {
                foreach ($this->jsonDecoder->decode($requestData['usedAttributeIds']) as $id => $code) {
                    $attributeIds[] = $id;
                    $attributeCodes[] = $code;
                }
                $productRequest->setData('configurable_attributes', $this->jsonEncoder->encode($attributeIds));
                $productRequest->setData('configurable_attribute_codes', $this->jsonEncoder->encode($attributeCodes));
                $productRequest->setData('configurable_attributes_data', $this->jsonEncoder->encode($attributeCodes));
            }
        }
    }

    /**
     *
     * @return array
     */
    protected function _getOfferAttributesForConfigurable()
    {
        return [
            'vendor_sku',
            'category_id',
            'attribute_set_id',
            'main_category_id',
            'marketplace_product_id',
            'cost_price_iqd',
            'cost_price_usd'
        ];
    }

    /**
     *
     * @return array
     */
    public function getWebsiteDataColumns()
    {
        return
        [
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
     * @param array $requestData
     * @return array
     */
    protected function getWebsiteDataArray($requestData)
    {
        $fields = $this->getWebsiteDataColumns();
        foreach ($fields as $field) {
            if (array_key_exists($field, $requestData['offer']) && $requestData['offer'][$field]) {
                $data[$field] = $requestData['offer'][$field];
            } else {
                $data[$field] = null;
            }
        }
        return $data;
    }

    protected function _getOfferAttributes()
    {
        $offerAttributeArray = $this->getOfferAttributesForTax();
        return $offerAttributeArray;
    }

    /**
     * @return array
     */
    public function getOfferAttributesForTax()
    {
        return [
            'vendor_sku',
            'qty',
            'attribute_set_id',
            'main_category_id',
            'marketplace_product_id',
            'cost_price_iqd',
            'cost_price_usd'
        ];
    }

    /**
     *
     * @param integer $vendorId
     * @param array $requestData
     * @param string $productType
     * @throws \Exception
     */
    public function updateProductRequest($vendorId, $requestData, $productType = Type::TYPE_SIMPLE)
    {
        $errors = [];
        if (isset($requestData['store']) && $requestData['store'] == 'default') {
            $storeId = $websiteId = 'default';
        } else {
            $storeId = $requestData['store'];
            $websiteId = $this->_storeManager->getStore($storeId)->getWebsiteId();
        }

        $catId = $requestData['offer']['category_id'];
        $marketplaceProductId = $requestData['offer']['marketplace_product_id'];
        $productRequestId = $requestData['offer']['product_request_id'];
        $this->productRequest->load($productRequestId);
        $attrArray = $additionalAttributes = $variantAttributes = $mediaAttributes = [];
        if (isset($requestData['vital'])) {
            /* Get attributes data. */
            $attrArray = $this->getAttributes($requestData, 'vital');
            $additionalAttributes = $this->getAttributes($requestData, 'additional');
            $variantAttributes = $this->getAttributes($requestData, 'variant');
            $mediaAttributes = $this->getAttributes($requestData, 'product', $errors);
            /* Get attributes data. */

            if (array_key_exists('product', $requestData) && array_key_exists('images', $requestData['product'])) {
                $this->productRequest->setData('images', $this->jsonEncoder->encode($requestData['product']['images']));
                foreach ($requestData['product']['images'] as $item) {
                    if (!strrpos($item['file'], '.tmp') &&
                        $this->_file->isExists(
                            $this->mediaDirectory->getAbsolutePath($this->mediaConfig->getMediaPath($item['file']))
                        )
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
                $this->productRequest->setData('base_image', $this->jsonEncoder->encode($base_image));
                if (!strrpos($base_image, '.tmp') &&
                    $this->_file->isExists(
                        $this->mediaDirectory->getAbsolutePath($this->mediaConfig->getMediaPath($base_image))
                    )
                ) {
                    $this->mediaDirectory->copyFile(
                        $this->mediaConfig->getMediaPath($base_image),
                        $this->mediaConfig->getTmpMediaPath($base_image)
                    );
                }
            }

            $this->productRequest->setData('images', $this->jsonEncoder->encode($mediaAttributes['images']));
            unset($mediaAttributes['images']); // remove images element to get mediaAttributes only.
            ($marketplaceProductId) ? $this->productRequest->setData('is_requested_for_edit', 1) : '';
        }

        if ($productType == Type::TYPE_SIMPLE) {
            $offerAttributes = $this->_getOfferAttributes();
            foreach ($this->_getOfferAttributes() as $field) {
                if (array_key_exists($field, $requestData['offer'])) {
                    $this->productRequest->setData($field, $requestData['offer'][$field]);
                }
            }
        }

        if ($this->productRequest->getData('status') == 2) {
            $this->productRequest->setData('status', 0);
        }

        if (!empty($errors)) {
            $this->coreRegistry->register('vendor_current_product_request', $this->productRequest);
            throw new \Exception();
        }

        $offerAttributes = $this->_getOfferAttributesForConfigurable();

        foreach ($offerAttributes as $key) {
            $value = (array_key_exists($key, $requestData['offer'])) ? $requestData['offer'][$key] : '';
            if ($value !== null && $value !== '') {
                $this->productRequest->setData($key, $value);
            } else {
                $this->productRequest->unsetData($key);
            }
        }
        $productRequestId = $requestData['offer']['product_request_id'];
        if ($productRequestId) {
            $this->productRequest->setId($productRequestId);
        }
        try {
            $prodReq = $this->productRequest->save();
            $this->updateWebsiteData($requestData['offer'], $productRequestId);
            /* Update store data.*/
            $requestData['attributes'] = array_merge(
                $attrArray,
                $additionalAttributes,
                $variantAttributes,
                $mediaAttributes
            );
            $this->updateStoreData($requestData, $productRequestId);
            /* Update store data.*/
        } catch (\Exception $e) {
            throw new \Exception(__($e->getMessage()));
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
                            !(
                                $attribute->getFrontendInput() == 'select' &&
                                $attribute->getIsUserDefined() && $attribute->getIsGlobal()
                            ) &&
                            array_key_exists($code, $vitalData)
                        ) {
                        $value = $requestData['vital'][$code];
                        $errors = $this->productRequest->validate(['label' => $label, 'value' => $value], $errors);
                        $attrArray[$code] = $this->_escaper->escapeHtml($value);
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
}
