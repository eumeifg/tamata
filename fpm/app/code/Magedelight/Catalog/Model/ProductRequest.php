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
namespace Magedelight\Catalog\Model;

use Magedelight\Catalog\Api\Data\ProductRequestInterface;
use Magedelight\Catalog\Helper\Data as CatalogHelper;
use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Framework\Model\AbstractExtensibleModel;

class ProductRequest extends AbstractExtensibleModel implements ProductRequestInterface
{
    const XML_PATH_EXCLUDE_ATTRIBUTES = 'vendor_product/vital_info/attributes';

    const STATUS_PARAM_NAME = 'status';
    const STATUS_PENDING = 0;
    const STATUS_APPROVED = 1;
    const STATUS_DISAPPROVED = 2;

    const CONDITION_USED = 0;
    const CONDITION_NEW = 1;
    const CONDITION_RENTAL = 2;

    const WARRANTY_MANUFACTURER = 1;
    const WARRANTY_SELLER = 2;

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'vendor_product_request';

    /**
     * Product type singleton instance
     *
     * @var \Magento\Catalog\Model\Product\Type\AbstractType
     */
    protected $_typeInstance = null;

    /**
     * @var \Magento\Catalog\Model\Product\Type
     */
    protected $_catalogProductType;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $productFactory;

    /**
     * @var \Magedelight\Catalog\Model\ResourceModel\Product\Core\Collection
     */
    protected $vendorProductCollection;

    /**
     * @var \Magento\Framework\Json\Decoder
     */
    protected $jsonDecoder;

    const CACHE_TAG = 'md_vendor_product_request';

    /**
     * Cache tag
     *
     * @var string
     */
    protected $_cacheTag = self::CACHE_TAG;

    /**
     * Event object
     *
     * @var string
     */
    protected $_eventObject = 'product_request';

    /**
     * @var CatalogHelper
     */
    protected $catalogHelper;

    /**
     * ProductRequest constructor.
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param ResourceModel\Product\CollectionFactory $vendorProdCollectionFactory
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Catalog\Model\Product\Type $_catalogProductType
     * @param \Magento\Framework\Json\Decoder $jsonDecoder
     * @param ExtensionAttributesFactory $extensionFactory
     * @param AttributeValueFactory $customAttributeFactory
     * @param CatalogHelper $catalogHelper
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magedelight\Catalog\Model\ResourceModel\Product\CollectionFactory $vendorProdCollectionFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Catalog\Model\Product\Type $_catalogProductType,
        \Magento\Framework\Json\Decoder $jsonDecoder,
        ExtensionAttributesFactory $extensionFactory,
        AttributeValueFactory $customAttributeFactory,
        CatalogHelper $catalogHelper,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->productFactory = $productFactory;
        $this->scopeConfig = $scopeConfig;
        $this->_catalogProductType = $_catalogProductType;
        $this->vendorProductCollection = $vendorProdCollectionFactory->create();
        $this->jsonDecoder = $jsonDecoder;
        $this->catalogHelper = $catalogHelper;
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $resource,
            $resourceCollection,
            $data
        );
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Magedelight\Catalog\Model\ResourceModel\ProductRequest::class);
    }

    public function getExcludeAttributeList()
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;

        $collection =  $this->scopeConfig->getValue(self::XML_PATH_EXCLUDE_ATTRIBUTES, $storeScope);
        $collection = explode(',', $collection);

        return $collection;
    }

    /**
     * return list of product attributes based
     */
    public function getProductAttributes($attributeSetId)
    {
        $product = $this->productFactory->create();
        $product->setStoreId('0');
        $product->setTypeId('simple');
        $product->setAttributeSetId($attributeSetId);
        return $product->getAttributes();
    }

    /**
     * Validate product request attributes
     *
     * @return string
     */
    public function validate($attribute, $errors)
    {
        if (!\Zend_Validate::is($attribute['value'], 'NotEmpty')) {
            $errors[] = __('Please enter %1.', $attribute['label']);
        }
        return $errors;
    }

    /**
     * load if product request already exist for product for vendor.
     * @param int $productId
     * @param int $vendorId
     * @return boolean
     */
    public function loadByProductVendorId($productId, $vendorId)
    {
        $this->load($this->getResource()->loadByProductVendorId($productId, $vendorId));
        return $this;
    }

    public function existButNotApproved($productId, $vendorId)
    {
        return $this->getResource()->existButNotApproved($productId, $vendorId) ? true : false;
    }

    /**
     * @return array
     */
    public function getAvailableStatuses()
    {
        return [
            self::STATUS_PENDING => __('Pending'),
            self::STATUS_APPROVED => __('Approved'),
            self::STATUS_DISAPPROVED => __('Disapproved')
        ];
    }

    /**
     * @return array
     */
    public function getHasVariantsBoolean()
    {
        return [self::STATUS_PENDING => __('No'), self::STATUS_APPROVED => __('Yes')];
    }

    /**
     * @return array
     */
    public function getAvailableCondition()
    {
        return [
            self::CONDITION_USED => __('Used'),
            self::CONDITION_NEW => __('New'),
            self::CONDITION_RENTAL => __('Rental')
        ];
    }

    /**
     * @return mixed
     */
    public function getStatusLabel()
    {
        $statuses = $this->getAvailableStatuses();
        return $statuses[$this->getStatus()];
    }

    /**
     * @return array
     */
    public function getWarrantyTypes()
    {
        return [self::WARRANTY_MANUFACTURER => __('Manufacturer'), self::WARRANTY_SELLER => __('Vendor')];
    }

    /**
     * Retrieve type instance of the product.
     * Type instance implements product type depended logic and is a singleton shared by all products of the same type.
     *
     * @return \Magento\Catalog\Model\Product\Type\AbstractType
     */
    public function getTypeInstance()
    {
        if ($this->_typeInstance === null) {
            $this->_typeInstance = $this->_catalogProductType->factory($this->productFactory->create());
        }
        return $this->_typeInstance;
    }

    /**
     * Set type instance for the product
     *
     * @param \Magento\Catalog\Model\Product\Type\AbstractType|null $instance  Product type instance
     * @return \Magento\Catalog\Model\Product
     */
    public function setTypeInstance($instance)
    {
        $this->_typeInstance = $instance;
        return $this;
    }

    /**
     *
     * @param array $varients
     * @return null | string
     */
    public function validateUniqueVariantsVendorSku($vendorId, $postData = [], $storeId = 0)
    {
        $variants = $postData['variants_data'];
        $vendorRequestId = $postData['offer']['product_request_id'];
        $pid = $postData['offer']['marketplace_product_id'];
        if (count($variants) > 0) {
            $vendorSku = [];
            $vprSkuCond = [];
            foreach ($variants as $variant) {
                $vendorSku[] = $variant['vendor_sku'];
                $vprSkuCond[] = ['like' => "%\"vendor_sku\":\"{$variant['vendor_sku']}\"%"];
            }
            if (count($vendorSku) !== count(array_unique($vendorSku))) {
                return __('Found duplicate vendor sku. Please correct.');
            }
            /* check if varients vendor_sku exist in vendor_product table */
            $vendorProductSkus = $this->vendorProductCollection
                ->addFieldToSelect('vendor_sku')
                ->addFieldToFilter('vendor_id', ['eq' => $vendorId])
                ->addFieldToFilter('vendor_sku', ['in' => $vendorSku])
                ->addFieldToFilter('parent_id', ['neq' => $pid])
                ->getColumnValues('vendor_sku');

            if (count($vendorProductSkus) > 0) {
                return __('Vendor Sku(s) %1 already exist.', implode(', ', $vendorProductSkus));
            } else {
                /* check if varients vendor_sku exist in vendor_product_request table */
                $variantsColl = $this->getResourceCollection();
                $variantsColl->addFieldToSelect('vendor_sku')
                    ->addFieldToFilter('vendor_sku', ['in' => $vendorSku])
                    ->addFieldToFilter('vendor_id', ['eq' => $vendorId]);
                /* except record if request is re-submitted.*/
                if ($this->getId() || $vendorRequestId > 0) {
                    $vendorRequestId = ($this->getId() > 0) ? $this->getId() : $vendorRequestId;
                    $variantsColl->addFieldToFilter('product_request_id', ['neq' => $vendorRequestId]);
                }

                $vendorProductRequestSkus = $variantsColl->getColumnValues('vendor_sku');
                if (count($vendorProductRequestSkus) > 0) {
                    return __('Vendor Sku(s) %1 already exists.', implode(', ', $vendorProductRequestSkus));
                }
            }
        }
    }
    /**
     * Check vendor sku is already exist in approved or pending request of vendor.
     * @param type $vendorId
     * @param type $vendorSku
     * @return string
     */
    public function validateUniqueVendorSku($vendorId, $vendorSku, $isNew = false)
    {
        if (!$isNew) {
            /* check if varients vendor_sku exist in vendor_product table */
            $vendorProductSkus = $this->vendorProductCollection
                ->addFieldToSelect('vendor_sku')
                ->addFieldToFilter('vendor_id', ['eq' => $vendorId])
                ->addFieldToFilter('vendor_sku', ['eq' => $vendorSku]);

            if ($vendorProductSkus->count() > 0) {
                if ($this->getId()) {
                    if ($this->getVendorSku() != $vendorSku) {
                        return __('This vendor SKU already exists. Please try again with a unique sku.');
                    }
                } else {
                    return __('This vendor SKU already exists. Please try again with a unique sku.');
                }
            } else {
                /* check if variants vendor_sku exist in vendor_product_request table */
                $vprSkuColl = $this->getResourceCollection()
                    ->addFieldToSelect('vendor_sku')
                    ->addFieldToFilter('vendor_id', ['eq' => $vendorId])
                    ->addFieldToFilter('vendor_sku', ['eq' => $vendorSku]);

                /* except record if request is re-submitted.*/
                if ($this->getId()) {
                    $vprSkuColl->addFieldToFilter('product_request_id', ['neq' => $this->getId()]);
                }
                if ($vprSkuColl->count() > 0) {
                    return __('This vendor SKU already exists. Please try again with a unique sku.');
                }

                return false;
            }
        }
        return false;
    }

    public function checkUniqueSKU($vendorSku)
    {
        if ($vendorSku) {
            /* check if varients vendor_sku exist in vendor_product table */
            $vendorProductSkus = $this->vendorProductCollection
                ->addFieldToSelect('vendor_sku')
                ->addFieldToFilter('vendor_sku', ['eq' => $vendorSku]);
            if ($vendorProductSkus->count() > 0) {
                throw new \Exception('This vendor SKU already exists. Please try again with a unique sku.');
            } else {
                /* check if varients vendor_sku exist in vendor_product_request table */
                $vprSkuColl = $this->getResourceCollection()
                    ->addFieldToSelect('vendor_sku')
                    ->addFieldToFilter('vendor_sku', ['eq' => $vendorSku]);
                /* except record if request is re-submitted.*/
                if ($this->getId()) {
                    $vprSkuColl->addFieldToFilter('product_request_id', ['neq' => $this->getId()]);
                }
                if ($vprSkuColl->count() > 0) {
                    throw new \Exception('Vendor SKU already exist.');
                }
            }
        }
    }

    public function checkUniqueSKUWithOtherVendor($vendorSku, $vendorId)
    {
        if ($vendorSku) {
            /* check if varients vendor_sku exist in vendor_product table */
            $vendorProductSkus = $this->vendorProductCollection
                ->addFieldToSelect('vendor_sku')
                ->addFieldToFilter('vendor_id', ['eq' => $vendorId])
                ->addFieldToFilter('vendor_sku', ['eq' => $vendorSku]);
            if ($vendorProductSkus->count() > 0) {
                throw new \Exception('This vendor SKU already exists. Please try again with a unique sku.');
            } else {
                /* check if varients vendor_sku exist in vendor_product_request table */
                $vprSkuColl = $this->getResourceCollection()
                    ->addFieldToSelect('vendor_sku')
                    ->addFieldToFilter('vendor_id', ['eq' => $vendorId])
                    ->addFieldToFilter('vendor_sku', ['eq' => $vendorSku]);
                /* except record if request is re-submitted.*/
                if ($this->getId()) {
                    $vprSkuColl->addFieldToFilter('product_request_id', ['neq' => $this->getId()]);
                }
                if ($vprSkuColl->count() > 0) {
                    throw new \Exception('Vendor SKU already exist.');
                }
            }
        }
    }
    public function setDataSaveAllowed($flag = true)
    {
        $this->_dataSaveAllowed = $flag;
    }

    /**
     * Get identities
     *
     * @return array
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    /**
     * {@inheritDoc}
     */
    public function getProductRequestId()
    {
        return $this->getData(ProductRequestInterface::REQUEST_ID);
    }

    /**
     * {@inheritDoc}
     */
    public function setProductRequestId($requestId)
    {
        return $this->setData(ProductRequestInterface::REQUEST_ID, $requestId);
    }

    /**
     * set Product Id
     *
     * @param mixed $marketplaceProductId
     * @return ProductRequestInterface
     */
    public function setMarketplaceProductId($marketplaceProductId)
    {
        return $this->setData(ProductRequestInterface::MARKETPLACE_PRODUCT_ID, $marketplaceProductId);
    }

    /**
     * {@inheritDoc}
     */
    public function getMarketplaceProductId()
    {
        return $this->getData(ProductRequestInterface::MARKETPLACE_PRODUCT_ID);
    }

    /**
     * {@inheritDoc}
     */
    public function setVendorId($vendorId)
    {
        return $this->setData(ProductRequestInterface::VENDOR_ID, $vendorId);
    }

    /**
     * {@inheritDoc}
     */
    public function getVendorId()
    {
        return $this->getData(ProductRequestInterface::VENDOR_ID);
    }

    /**
     * {@inheritDoc}
     */
    public function setMainCategoryId($mainCategoryId)
    {
        return $this->setData(ProductRequestInterface::MAIN_CATEGORY_ID, $mainCategoryId);
    }

    /**
     * {@inheritDoc}
     */
    public function getMainCategoryId()
    {
        return $this->getData(ProductRequestInterface::MAIN_CATEGORY_ID);
    }

    /**
     * {@inheritDoc}
     */
    public function setCategoryId($categoryId)
    {
        return $this->setData(ProductRequestInterface::CATEGORY_ID, $categoryId);
    }

    /**
     * {@inheritDoc}
     */
    public function getCategoryId()
    {
        return $this->getData(ProductRequestInterface::CATEGORY_ID);
    }

    /**
     * {@inheritDoc}
     */
    public function setAttributeSetId($attributeSetId)
    {
        return $this->setData(ProductRequestInterface::ATTRIBUTE_SET_ID, $attributeSetId);
    }

    /**
     * {@inheritDoc}
     */
    public function getAttributeSetId()
    {
        return $this->getData(ProductRequestInterface::ATTRIBUTE_SET_ID);
    }

    /**
     * {@inheritDoc}
     */
    public function setStatus($status)
    {
        return $this->setData(ProductRequestInterface::STATUS, $status);
    }

    /**
     * {@inheritDoc}
     */
    public function getStatus()
    {
        return $this->getData(ProductRequestInterface::STATUS);
    }

    /**
     * {@inheritDoc}
     */
    public function setDisapproveMessage($disapproveMessage)
    {
        return $this->setData(ProductRequestInterface::DISAPPROVE_MESSAGE, $disapproveMessage);
    }

    /**
     * {@inheritDoc}
     */
    public function getDisapproveMessage()
    {
        return $this->getData(ProductRequestInterface::DISAPPROVE_MESSAGE);
    }

    /**
     * {@inheritDoc}
     */
    public function setHasVariants($hasVariants)
    {
        return $this->setData(ProductRequestInterface::HAS_VARIANTS, $hasVariants);
    }

    /**
     * {@inheritDoc}
     */
    public function getHasVariants()
    {
        return $this->getData(ProductRequestInterface::HAS_VARIANTS);
    }

    /**
     * {@inheritDoc}
     */
    public function setUsedProductAttributeIds($usedProductAttributeIds)
    {
        return $this->setData(ProductRequestInterface::USED_PRODUCT_ATTRIBUTE_IDS, $usedProductAttributeIds);
    }

    /**
     * {@inheritDoc}
     */
    public function getUsedProductAttributeIds()
    {
        return $this->getData(ProductRequestInterface::USED_PRODUCT_ATTRIBUTE_IDS);
    }

    /**
     * {@inheritDoc}
     */
    public function setConfigurableAttributes($configurableAttributes)
    {
        return $this->setData(ProductRequestInterface::CONFIGURABLE_ATTRIBUTES, $configurableAttributes);
    }

    /**
     * {@inheritDoc}
     */
    public function getConfigurableAttributes()
    {
        return $this->getData(ProductRequestInterface::CONFIGURABLE_ATTRIBUTES);
    }

    /**
     * {@inheritDoc}
     */
    public function setConfigurableAttributeCodes($configurableAttributeCodes)
    {
        return $this->setData(ProductRequestInterface::CONFIGURABLE_ATTRIBUTE_CODES, $configurableAttributeCodes);
    }

    /**
     * {@inheritDoc}
     */
    public function getConfigurableAttributeCodes()
    {
        return $this->getData(ProductRequestInterface::CONFIGURABLE_ATTRIBUTE_CODES);
    }

    /**
     * {@inheritDoc}
     */
    public function setConfigurableAttributesData($configurableAttributesData)
    {
        return $this->setData(ProductRequestInterface::CONFIGURABLE_ATTRIBUTES_DATA, $configurableAttributesData);
    }

    /**
     * {@inheritDoc}
     */
    public function getConfigurableAttributesData()
    {
        return $this->getData(ProductRequestInterface::CONFIGURABLE_ATTRIBUTES_DATA);
    }

    /**
     * {@inheritDoc}
     */
    public function setVendorSku($vendorSku)
    {
        return $this->setData(ProductRequestInterface::VENDOR_SKU, $vendorSku);
    }

    /**
     * get Vendor SKU
     *
     * @return string
     */
    public function getVendorSku()
    {
        return $this->getData(ProductRequestInterface::VENDOR_SKU);
    }

    /**
     * {@inheritDoc}
     */
    public function setQty($qty)
    {
        return $this->setData(ProductRequestInterface::QTY, $qty);
    }

    /**
     * {@inheritDoc}
     */
    public function getQty()
    {
        return $this->getData(ProductRequestInterface::QTY);
    }

    /**
     * {@inheritDoc}
     */
    public function setImages($images)
    {
        return $this->setData(ProductRequestInterface::IMAGES, $images);
    }

    /**
     * {@inheritDoc}
     */
    public function getImages()
    {
        return $this->getData(ProductRequestInterface::IMAGES);
    }

    /**
     * {@inheritDoc}
     */
    public function setBaseImage($baseImage)
    {
        return $this->setData(ProductRequestInterface::BASE_IMAGE, $baseImage);
    }

    /**
     * {@inheritDoc}
     */
    public function getBaseImage()
    {
        return $this->getData(ProductRequestInterface::BASE_IMAGE);
    }

    /**
     * {@inheritDoc}
     */
    public function setIsRequestedForEdit($isRequestedForEdit)
    {
        return $this->setData(ProductRequestInterface::IS_REQUESTED_FOR_EDIT, $isRequestedForEdit);
    }

    /**
     * {@inheritDoc}
     */
    public function getIsRequestedForEdit()
    {
        return $this->getData(ProductRequestInterface::IS_REQUESTED_FOR_EDIT);
    }

    /**
     * {@inheritDoc}
     */
    public function setVendorProductId($vendorProductId)
    {
        return $this->setData(ProductRequestInterface::VENDOR_PRODUCT_ID, $vendorProductId);
    }

    /**
     * {@inheritDoc}
     */
    public function getVendorProductId()
    {
        return $this->getData(ProductRequestInterface::VENDOR_PRODUCT_ID);
    }

    /**
     * {@inheritDoc}
     */
    public function setStoreId($storeId)
    {
        return $this->setData(ProductRequestInterface::STORE_ID, $storeId);
    }

    /**
     * {@inheritDoc}
     */
    public function getStoreId()
    {
        return $this->getData(ProductRequestInterface::STORE_ID);
    }

    /**
     * {@inheritDoc}
     */
    public function setTaxClassId($taxClassId)
    {
        return $this->setData(ProductRequestInterface::TAX_CLASS_ID, $taxClassId);
    }

    /**
     * {@inheritDoc}
     */
    public function getTaxClassId()
    {
        return $this->getData(ProductRequestInterface::TAX_CLASS_ID);
    }

    /**
     * {@inheritDoc}
     */
    public function setIsOffered($isOffered)
    {
        return $this->setData(ProductRequestInterface::IS_OFFERED, $isOffered);
    }

    /**
     * {@inheritDoc}
     */
    public function getIsOffered()
    {
        return $this->getData(ProductRequestInterface::IS_OFFERED);
    }

    /**
     * {@inheritDoc}
     */
    public function setWebsiteIds($websiteIds)
    {
        return $this->setData(ProductRequestInterface::WEBSITE_IDS, $websiteIds);
    }

    /**
     * {@inheritDoc}
     */
    public function getWebsiteIds()
    {
        return $this->getData(ProductRequestInterface::WEBSITE_IDS);
    }

    /**
     * @param bool $addVendorData
     * @return \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
     */
    public function getCollection($addVendorData = false)
    {
        $collection = parent::getCollection();
        $collection->getSelect()->group('main_table.product_request_id');
        $collection->addFilterToMap('status', 'main_table.status');
        if ($addVendorData) {
            $this->_addVendorData($collection);
        }
        return $collection;
    }

    /**
     * @param $websiteId
     * @return mixed
     */
    public function getDefaultStoreId($websiteId)
    {
        if ($websiteId) {
            $connection = $this->getResource()->getConnection();
            $select = $connection->select()->from(
                ['rbsg' => $this->getResource()->getTable('store_group')],
                ['default_store_id']
            );

            $select->where('`rbsg`.`website_id` =' . $websiteId);
            $data = $connection->fetchAll($select);
            return $data[0]['default_store_id'];
        }
    }

    /**
     * @param $collection
     */
    protected function _addVendorData($collection)
    {
        $collection->getSelect()->joinLeft(
            ['rv' => $this->getResource()->getTable('md_vendor')],
            "main_table.vendor_id = rv.vendor_id",
            ['email']
        )->join(
            ['rvwd' => $this->getResource()->getTable('md_vendor_website_data')],
            "rvwd.vendor_id = rv.vendor_id",
            ['vendor_name' => 'rvwd.name','rvwd.business_name', 'rvwd.logo']
        );
    }

    /**
     * {@inheritDoc}
     */
    public function getPrice()
    {
        return $this->getData(ProductRequestInterface::PRICE);
    }

    /**
     * {@inheritDoc}
     */
    public function setPrice($price)
    {
        return $this->setData(ProductRequestInterface::PRICE, $price);
    }

    /**
     * {@inheritDoc}
     */
    public function getSpecialPrice()
    {
        return $this->getData(ProductRequestInterface::SPECIAL_PRICE);
    }

    /**
     * {@inheritDoc}
     */
    public function setSpecialPrice($specialPrice)
    {
        return $this->setData(ProductRequestInterface::SPECIAL_PRICE, $specialPrice);
    }

    /**
     * Product reorder level
     *
     * @return int|NULL
     */
    public function getReorderLevel()
    {
        return $this->getData(ProductRequestInterface::REORDER_LEVEL);
    }

    /**
     * Set product reorder level
     *
     * @param int|NULL $reorderLevel
     * @return $this
     */
    public function setReorderLevel($reorderLevel)
    {
        return $this->setData(ProductRequestInterface::REORDER_LEVEL, $reorderLevel);
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return $this->getData(ProductRequestInterface::NAME);
    }

    /**
     * {@inheritDoc}
     */
    public function setName($name)
    {
        return $this->setData(ProductRequestInterface::NAME, $name);
    }

    /**
     * {@inheritDoc}
     */
    public function getBaseImageUrl()
    {
        $baseImage = $this->getData(ProductRequestInterface::BASE_IMAGE_URL);
        if (!$baseImage) {
            return $this->catalogHelper->getBaseTmpImage($this);
        }
        return $this->getData(ProductRequestInterface::BASE_IMAGE_URL);
    }

    /**
     * {@inheritDoc}
     */
    public function setBaseImageUrl($baseImage = null)
    {
        return $this->setData(ProductRequestInterface::BASE_IMAGE_URL, $baseImage);
    }

    /**
     * @inheritdoc
     *
     * @return \Magento\Framework\Api\ExtensionAttributesInterface
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * @inheritdoc
     *
     * @param \Magedelight\Catalog\Api\Data\ProductRequestExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Magedelight\Catalog\Api\Data\ProductRequestExtensionInterface $extensionAttributes
    ) {
        return $this->_setExtensionAttributes($extensionAttributes);
    }
}
