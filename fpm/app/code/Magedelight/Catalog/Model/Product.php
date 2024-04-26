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

use Magedelight\Catalog\Api\Data\ProductVendorDataInterface;
use Magedelight\Vendor\Model\Source\Status as VendorStatus;
use Magento\Catalog\Model\Product\Type;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\Exception\NoSuchEntityException;

class Product extends \Magento\Framework\Model\AbstractModel implements ProductVendorDataInterface
{

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $_localeDate;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magedelight\Catalog\Model\ResourceModel\Indexer\Vendor\DefaultVendorsFactory
     */
    protected $_defaultVendorIndexersFactory;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $datetime;
    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * @var array
     */
    protected $_defaultVendor = [];
    /**
     * @var bool
     */
    protected $_availableVendors = false;
    /**
     * @var bool
     */
    protected $_defaultVendorProduct = false;
    /**
     * @var array
     */
    protected $_defaultVendorPrice = [];
    /**
     * @var array
     */
    protected $_productMinPrice = [];
    /**
     * @var array
     */
    protected $_productMinSpecialPrice = [];
    /**
     * @var null
     */
    protected $_collectionData = null;
    /**
     * @var null
     */
    protected $_productWebsites = null;
    /**
     * @var null
     */
    protected $_productStores = null;
    /** @var  array */
    private $extenstionAttributes;
    /**
     * @var \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\ConfigurableFactory
     */
    protected $_typeConfigurableFactory;

    /**
     * @var \Magento\Catalog\Model\ProductRepository
     */
    protected $productRepository;
    /**
     * @var ProductWebsiteRepository
     */
    protected $productWebsiteRepository;
    /**
     * @var ProductStoreRepository
     */
    protected $productStoreRepository;
    /**
     * @var \Magento\Eav\Api\AttributeRepositoryInterface
     */
    protected $_attributeRepository;

    const STATUS_PARAM_NAME = 'status';
    const STATUS_UNLISTED = 0;
    const STATUS_LISTED = 1;
    const CONDITION_USED = 0;
    const CONDITION_NEW = 1;
    const CONDITION_RENTAL = 2;
    const WARRANTY_MANUFACTURER = 1;
    const WARRANTY_SELLER = 2;
    const STORE_ID = 'store_id';
    const CURRENT_PRODUCT_ID = 'current_product_id';
    const M_W_ENABLE = 'vendor/general/allow_multiwebsite';

    /**
     *
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $datetime
     * @param \Magento\Framework\App\Request\Http $request
     * @param \Magedelight\Catalog\Model\ResourceModel\Indexer\Vendor\DefaultVendorsFactory $defaultVendorsFactory
     * @param \Magento\Catalog\Model\ProductRepository $productRepository
     * @param \Magedelight\Catalog\Model\ProductWebsiteRepository $productWebsiteRepository
     * @param \Magedelight\Catalog\Model\ProductStoreRepository $productStoreRepository
     * @param \Magento\Eav\Api\AttributeRepositoryInterface $attributeRepository
     * @param \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\ConfigurableFactory $typeConfigurableFactory
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context                                                  $context,
        \Magento\Framework\Registry                                                       $registry,
        \Magento\Store\Model\StoreManagerInterface                                        $storeManager,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface                              $localeDate,
        \Magento\Framework\Stdlib\DateTime\DateTime                                       $datetime,
        \Magento\Framework\App\Request\Http                                               $request,
        \Magedelight\Catalog\Model\ResourceModel\Indexer\Vendor\DefaultVendorsFactory     $defaultVendorsFactory,
        \Magento\Catalog\Model\ProductRepository                                          $productRepository,
        \Magedelight\Catalog\Model\ProductWebsiteRepository                               $productWebsiteRepository,
        \Magedelight\Catalog\Model\ProductStoreRepository                                 $productStoreRepository,
        \Magento\Eav\Api\AttributeRepositoryInterface                                     $attributeRepository,
        \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\ConfigurableFactory $typeConfigurableFactory,
        \Magento\Framework\Model\ResourceModel\AbstractResource                           $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb                                     $resourceCollection = null,
        array                                                                             $data = []
    )
    {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->_storeManager = $storeManager;
        $this->_localeDate = $localeDate;
        $this->datetime = $datetime;
        $this->request = $request;
        $this->productRepository = $productRepository;
        $this->productWebsiteRepository = $productWebsiteRepository;
        $this->productStoreRepository = $productStoreRepository;
        $this->_attributeRepository = $attributeRepository;
        $this->_defaultVendorIndexersFactory = $defaultVendorsFactory->create();
        $this->_typeConfigurableFactory = $typeConfigurableFactory;
    }

    /**
     * Process operation before object load
     *
     * @return Product
     * @throws NoSuchEntityException
     * @since 100.2.0
     */
    public function afterLoad()
    {
        $websiteProduct = $this->productWebsiteRepository->getProductWebsiteData($this->getId(), $this->getWebsiteId());
        if ($websiteProduct && $websiteProduct->getPrice()) {
            foreach ($websiteProduct->getData() as $key => $value) {
                $this->setData($key, $value);
            }
        }
        $storeProduct = $this->productStoreRepository->getProductStoreData($this->getId(), $this->getStoreId());
        if ($storeProduct) {
            foreach ($storeProduct->getData() as $key => $value) {
                $this->setData($key, $value);
            }
        }
        $this->setName($this->productRepository->getById($this->getMarketplaceProductId())->getName());
        return $this;
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Magedelight\Catalog\Model\ResourceModel\Product::class);
    }

    /**
     *
     * @return array
     */
    public function getAvailableStatuses()
    {
        return [self::STATUS_UNLISTED => __('Approved but not listed'), self::STATUS_LISTED => __('Listed')];
    }

    /**
     *
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
     * Collect all vendors for loaded product as per vendor ratings
     * @param bool $productId
     * @param bool $vendorId
     * @param bool $addQtyFilter
     * @return collection object | boolean false
     * @throws NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getAvailableVendorsForProduct($productId = false, $vendorId = false, $addQtyFilter = false)
    {
        if ($productId) {
            if (!$this->_availableVendors || !array_key_exists($productId, $this->_availableVendors)) {
                $collection = $this->getCollection(true);
                /*$collection->getSelect()->reset('where');*/
                $collection->addFieldToFilter('marketplace_product_id', $productId);
                if ($vendorId) {
                    $collection->addFieldToFilter('rv.vendor_id', ['eq' => $vendorId]);
                }

                $fullActionName = $this->request->getFullActionName();
                $requestStringName = $this->request->getRequestString();
                $allowedActions = ['catalog_category_view', "__", "catalogsearch_result_index", "microsite_products_vendor"];

                if (in_array($fullActionName, $allowedActions) && $requestStringName === "/graphql") {
                    $collection->processCollectionForFrontendForGraphQlPrice($collection, $addQtyFilter);
                } else {
                    $collection->processCollectionForFrontend($collection, $addQtyFilter);
                }
//                $collection->processCollectionForFrontend($collection, $addQtyFilter);
                $this->_availableVendors[$productId] = $collection;
            }
            return $this->_availableVendors[$productId];
        }
        return false;
    }

    /**
     * Collect all vendors for loaded product as per vendor ratings
     * @param bool $productId
     * @param bool $vendorId
     * @param bool $addQtyFilter
     * @return collection object | boolean false
     * @throws NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getAvailableVendorsForProductForGraphQl($productId = false, $vendorId = false, $addQtyFilter = false)
    {
        if ($productId) {
            if (!$this->_availableVendors || !array_key_exists($productId, $this->_availableVendors)) {
                $collection = $this->getCollection(true);
                $collection->addFieldToFilter('marketplace_product_id', $productId);
                $this->_availableVendors[$productId] = $collection;
            }
            return $this->_availableVendors[$productId];
        }
        return false;
    }

    /**
     * Collect all vendors for loaded product without vendor Ratings
     * @param bool $productId
     * @return vendors | boolean false
     * @throws NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getAvailableVendorsForProductWithoutRating($productId = false)
    {
        if ($productId) {
            $collection = $this->getCollection()->addFieldToFilter('marketplace_product_id', $productId);
            $collection->processCollectionForFrontend($collection);
            if ($collection->count() > 0) {
                return $collection;
            }
        }
        return false;
    }

    /**
     * return active vendors record only.
     * @param bool $vendorId
     * @param bool $productId
     * @param bool $addQtyFilter
     * @param bool $addStatusFilter
     * @return Vendor Product Object
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getVendorProduct(
        $vendorId = false,
        $productId = false,
        $addQtyFilter = true,
        $addStatusFilter = true
    )
    {
        try {
            if (!$productId && !$vendorId) {
                throw new \Exception();
            }
            $collection = $this->getCollection();
            $collection->processCollectionForFrontend(
                $collection,
                $addQtyFilter,
                [VendorStatus::VENDOR_STATUS_ACTIVE],
                true,
                true,
                $addStatusFilter
            );
            $collection->addFieldToFilter('marketplace_product_id', $productId);
            if ($vendorId) {
                $collection->addFieldToFilter('rv.vendor_id', ['eq' => $vendorId]);
            }

            if ($collection->count()) {
                $item = $collection->getFirstItem();
                if ($item) {
                    $this->setSpecialPriceForItem($item);
                }
                return $item;
            } else {
                return false;
            }
        } catch (\NoSuchEntityException $e) {
            throw new \Magento\Framework\Exception\LocalizedException(__('Invalid product and vendor.'), $e);
        }
    }

    /**
     * get Vendor Price for product
     * @param bool $vendorId
     * @param bool $productId
     * @return Vendor Object |boolean
     */
    public function getVendorPrice($vendorId = false, $productId = false)
    {
        try {
            if (!$productId && !$vendorId) {
                throw new \Exception();
            }
            $collection = $this->getCollection();
            $collection->addFieldToFilter('marketplace_product_id', ['eq' => $productId]);
            $collection->addFieldToFilter('vendor_id', ['eq' => $vendorId]);
            $collection->addFieldToFilter('is_deleted', ['eq' => 0]);
            $collection->processCollectionForFrontend($collection);
            if ($collection->count()) {
                $item = $collection->getFirstItem();
                if ($item) {
                    $this->setSpecialPriceForItem($item);
                }
                return $item;
            }
        } catch (\Exception $e) {
            return false;
        }
        return false;
    }

    /**
     * Retrive default vendor of product on basis of Maximum Rating & lowest price criteria.
     * @param bool $vendorId
     * @param bool $productId
     * @param bool $addQtyFilter
     * @return Vendor object | boolean
     * @throws NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getProductDefaultVendor($vendorId = false, $productId = false, $addQtyFilter = true)
    {
        if (!array_key_exists($productId, $this->_defaultVendor)) {
            if ($productId) {
                if ($vendorId) {
                    $collection = $this->getAvailableVendorsForProduct($productId, $vendorId, $addQtyFilter);
                } else {
                    $collection = $this->getAvailableVendorsForProduct($productId, false, $addQtyFilter);
                }
                if ($collection && $collection->getFirstItem()) {
                    $this->_defaultVendor[$productId] = $collection->getFirstItem();
                }
            }
        }
        return $this->_defaultVendor[$productId];
    }

    /**
     *
     * @param bool $vendorId
     * @param bool $productId
     * @return boolean
     * @throws NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function checkVendordSimpleStockStatus($vendorId = false, $productId = false)
    {
        $collection = $this->getCollection()->addFieldToFilter('marketplace_product_id', $productId)
            ->addFieldToFilter('vendor_id', $vendorId);
        $collection->processCollectionForFrontend($collection);
        if ($collection->count() == 1) {
            if ($collection->getFirstItem()->getQty() > 0) {
                return true;
            }
        }
        return false;
    }

    /**
     * get chipest vendor price for product
     * @param int $productId
     * @return Product Price
     */

    /**
     * get chipest vendor price for product
     * @param int $productId
     * @param string $type
     * @param boolean $addQtyFilter
     * @param boolean $returnVendorSpecificOnly
     * @return string
     * @throws NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getMinPrice(
        $productId,
        $type = Type::TYPE_SIMPLE,
        $addQtyFilter = true,
        $returnVendorSpecificOnly = false
    )
    {
        $this->setCurrentProductId($productId);
        if (!array_key_exists($productId, $this->_productMinPrice)) {
            $collection = $this->getCollection();

            if ($defaultVendor = $this->getProductDefaultVendor(null, $productId, $addQtyFilter)) {
                $vendorId = $defaultVendor->getVendorId();
            }
            if ($this->request->getParam('vid')) {
                $vendorId = $this->request->getParam('vid');
            }
            if ($this->request->getParam('v')) {
                $vendorId = $this->request->getParam('v');
            }
            if (isset($vendorId) && $vendorId != '') {
                $collection->addFieldToFilter('rv.vendor_id', $vendorId);
            }
            if (in_array($type, [Type::TYPE_SIMPLE, Type::TYPE_VIRTUAL])) {
                $collection->addFieldToFilter('marketplace_product_id', $productId);
            } elseif (in_array($type, [Configurable::TYPE_CODE])) {
                $collection->addFieldToFilter('parent_id', $productId);
            }
            if (stripos($this->request->getRequestString(), "graphql") == false) {
                $collection->processCollectionForFrontendForGraphQlPrice($collection, $addQtyFilter);
            } else {
                $collection->processCollectionForFrontend($collection, $addQtyFilter);
            }
//            $collection->processCollectionForFrontend($collection, $addQtyFilter);
            $collection = $this->getDefaultVendorPrice($collection, 'price', $productId);
            if ($returnVendorSpecificOnly) {
                $this->_productMinPrice[$productId] = ($collection && $collection->getFirstItem()->getData('price')) ?
                    $collection->getFirstItem()->getData('price') : null;
            } else {
                $this->_productMinPrice[$productId] = ($collection && $collection->getFirstItem()->getData('price')) ?
                    $collection->getFirstItem()->getData('price') : $collection->getMinPrice($productId);
            }
        }
        return $this->_productMinPrice[$productId];
    }

    /**
     *
     * @param type $productId
     * @param string $type $type
     * @param bool $addQtyFilter
     * @return int
     * @throws NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getMinPriceExcludingRating($productId, $type = Type::TYPE_SIMPLE, $addQtyFilter = true)
    {
        $price = 0;
        $collection = $this->getCollection();
        if (in_array($type, [Type::TYPE_SIMPLE, Type::TYPE_VIRTUAL])) {
            $collection->addFieldToFilter('marketplace_product_id', $productId);
        } elseif (in_array($type, [Configurable::TYPE_CODE])) {
            $collection->addFieldToFilter('parent_id', $productId);
        }
        $collection->processCollectionForFrontend($collection, true, [1], false);
        $collection = $this->getDefaultVendorPrice($collection, 'price', $productId);
        $price = ($collection && $collection->getFirstItem()->getData('price')) ?
            $collection->getFirstItem()->getData('price') : 0;
        return $price;
    }

    /**
     * get chipest vendor special price for product
     * @param int $productId
     * @param string $type
     * @param bool $addQtyFilter
     * @return string
     * @throws NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getMinSpecialPriceExcludingRating($productId, $type = Type::TYPE_SIMPLE, $addQtyFilter = true)
    {
        $price = 0;
        $collection = $this->getCollection();
        $collection->getSelect()->where('(CURDATE() between rbvpw.special_from_date AND rbvpw.special_to_date)');
        if (in_array($type, [Type::TYPE_SIMPLE, Type::TYPE_VIRTUAL])) {
            $collection->addFieldToFilter('marketplace_product_id', $productId);
        } elseif (in_array($type, [Configurable::TYPE_CODE])) {
            $collection->addFieldToFilter('parent_id', $productId);
        }
        $collection->processCollectionForFrontend($collection);
        $collection = $this->getDefaultVendorPrice($collection, 'special_price', $productId);
        $price = ($collection->getFirstItem() && $collection->getFirstItem()->getData('special_price')) ?
            $collection->getFirstItem()->getData('special_price') : 0;
        return $price;
    }

    /**
     * get chipest vendor special price for product
     * @param int $productId
     * @return Product Price
     */

    /**
     * get chipest vendor special price for product
     * @param int $productId
     * @param string $type
     * @param boolean $addQtyFilter
     * @param boolean $returnVendorSpecificOnly
     * @return string
     * @throws NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getMinSpecialPrice(
        $productId,
        $type = Type::TYPE_SIMPLE,
        $addQtyFilter = true,
        $returnVendorSpecificOnly = false
    )
    {
        $this->setCurrentProductId($productId);
        if (!array_key_exists($productId, $this->_productMinSpecialPrice)) {
            $collection = $this->getCollection();

            $collection->getSelect()->where('(CURDATE() between rbvpw.special_from_date AND rbvpw.special_to_date)');
            if ($defaultVendor = $this->getProductDefaultVendor(null, $productId, $addQtyFilter)) {
                $vendorId = $defaultVendor->getVendorId();
            }
            if ($this->request->getParam('vid')) {
                $vendorId = $this->request->getParam('vid');
            }
            if ($this->request->getParam('v')) {
                $vendorId = $this->request->getParam('v');
            }
            if (isset($vendorId) && $vendorId != '') {
                $collection->addFieldToFilter('rv.vendor_id', $vendorId);
            }
            if (in_array($type, [Type::TYPE_SIMPLE, Type::TYPE_VIRTUAL])) {
                $collection->addFieldToFilter('marketplace_product_id', $productId);
            } elseif (in_array($type, [Configurable::TYPE_CODE])) {
                $collection->addFieldToFilter('parent_id', $productId);
            }
            $collection->processCollectionForFrontend($collection, $addQtyFilter);
            $collection = $this->getDefaultVendorPrice($collection, 'special_price', $productId);

            if ($this->request->getParam('v')) {
                /* Get vendor specific price only. Do not check for other vendors price selling this product. */
                $this->_productMinSpecialPrice[$productId] = (
                    $collection->getFirstItem() && $collection->getFirstItem()->getData('special_price')
                ) ? $collection->getFirstItem()->getData('special_price') : null;
            } else {
                $this->_productMinSpecialPrice[$productId] = (
                    $collection->getFirstItem() && $collection->getFirstItem()->getData('special_price')
                ) ? $collection->getFirstItem()
                    ->getData('special_price') : $collection->getMinSpecialPrice($productId, $vendorId);
            }
        }
        return $this->_productMinSpecialPrice[$productId];
    }

    /**
     * Obtain all marketplace product id of selected vendor
     * @param int $vendorId
     * @return \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection|null
     * @throws NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getVendorProductsById($vendorId = 0)
    {
        $collection = $this->getCollection();
        $collection->processCollectionForFrontend($collection);
        $collection->addFieldToFilter('main_table' . 'vendor_id', ['eq' => $vendorId]);
        return $collection;
    }

    /**
     * load vendor product by marketplace product id and vendor id
     * @param int $mainProdId
     * @param int $vendorId
     * @return $this
     * @throws NoSuchEntityException
     */
    public function getByOriginProductId($mainProdId, $vendorId)
    {
        $storeId = $this->getStoreId();
        $vendorProduct = $this->getResource()->getByOriginProductId($mainProdId, $vendorId, $storeId);
        if (!empty($vendorProduct)) {
            $this->setData($vendorProduct);
        }
        return $this;
    }

    /**
     * Check product is from active vendor, is product listed, non deleted etc.
     * @param int $productId
     * @return boolean
     * @throws NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function checkIsProductSalableFromVendor($productId)
    {
        if ($productId) {
            $collection = $this->getCollection()->addFieldToFilter('marketplace_product_id', $productId);
            $collection->processCollectionForFrontend($collection);
            if ($collection->count() > 0) {
                return true;
            }
            return false;
        }
        return false;
    }

    /**
     *
     * @param int $productId
     * @return boolean
     * @throws NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function hasMultiVendor($productId)
    {
        if (!array_key_exists($productId, $this->_availableVendors)) {
            $this->_availableVendors[$productId] = $this->getAvailableVendorsForProduct($productId);
        }
        if ($this->_availableVendors[$productId]->count() > 1) {
            return true;
        }
        return false;
    }

    /**
     *
     * @param type $productId
     * @return float Product Qty
     * @throws NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getTotalProductQty($productId)
    {
        if ($productId) {
            $collection = $this->getCollection()->addFieldToFilter('marketplace_product_id', $productId);
            $collection->processCollectionForFrontend($collection);
            return $collection->getTotalProductCount($productId);
        }
    }

    /**
     * Obtain all marketplace product id of selected vendor
     * @param bool $mainproductId
     * @param bool $vendorId
     * @param bool $addQtyFilter
     * @return bool|\Magento\Framework\DataObject
     * @throws NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getVendorDefaultProduct($mainproductId = false, $vendorId = false, $addQtyFilter = true)
    {
        if (!$this->_defaultVendorProduct) {
            if ($mainproductId) {
                $collection = $this->getCollection()->addFieldToFilter('main_table.parent_id', $mainproductId);
                $collection->addFieldToFilter('main_table.qty', ['gt' => 0]);
                /*$collection->addFieldToSelect(
                    ['marketplace_product_id', 'rbvpw.price', 'rbvpw.special_price', 'vendor_id']
                );*/

                if ($vendorId) {
                    $collection->addFieldToFilter('rv.vendor_id', ['eq' => $vendorId]);
                }
                /*
                 * If product type is configure than no need to filter it by qty
                */
                $collection->processCollectionForFrontend($collection, $addQtyFilter);
                if ($collection->count()) {
                    $item = $collection->getFirstItem();
                    $this->_defaultVendorProduct = $item;
                }
            }
        }
        return $this->_defaultVendorProduct;
    }

    /**
     * Obtain all marketplace product id of selected vendor
     * @param bool $mainproductId
     * @param bool $vendorId
     * @param bool $addQtyFilter
     * @return bool|\Magento\Framework\DataObject
     * @throws NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getVendorDefaultProductConfig($mainproductId = false, $vendorId = false, $addQtyFilter = true)
    {
        if ($mainproductId) {
            $collection = $this->getCollection()->addFieldToFilter('parent_id', $mainproductId);
            $collection->addFieldToFilter('main_table.qty', ['gt' => 0]);
            /*$collection->addFieldToSelect(
                ['marketplace_product_id', 'rbvpw.price', 'rbvpw.special_price', 'vendor_id']
            );*/

            if ($vendorId) {
                $collection->addFieldToFilter('rv.vendor_id', ['eq' => $vendorId]);
            }
            /*
             * If product type is configure than no need to filter it by qty
            */
            $collection->processCollectionForFrontend($collection, $addQtyFilter);
            if ($collection->count()) {
                $item = $collection->getFirstItem();
                return $item;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * @return array
     */
    public function getWarrantyTypes()
    {
        return [self::WARRANTY_MANUFACTURER => __('Manufacturer'), self::WARRANTY_SELLER => __('Vendor')];
    }

    /*public function updateGlobalData($data)
    {
        $vendorProduct = $this->getResource()->updateGlobalData($data);

        return $this;
    }*/

    /**
     * $obtain Vendor Product by VendorSku
     * @param String $vendorSku
     * @return Product
     * @throws NoSuchEntityException
     */
    public function getVendorProductsBySku($vendorSku)
    {
        $storeId = $this->getStoreId();
        $vendorProductId = $this->getResource()->getIdBySku($vendorSku, $storeId);
        if (!$vendorProductId) {
            throw new NoSuchEntityException(__('Requested product doesn\'t exist'));
        }
        return $this->load($vendorProductId);
    }

    /**
     * @param int $productId
     * @return array
     * @throws NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getProductVendorIds($productId = 0)
    {
        if (!array_key_exists($productId, $this->_availableVendors)) {
            $this->_availableVendors[$productId] = $this->getAvailableVendorsForProduct($productId);
        }
        $vendorIds = [];
        foreach ($this->_availableVendors[$productId] as $_item) {
            $vendorIds[] = $_item->getVendorId();
        }
        return $vendorIds;
    }

    /**
     * @param bool $productId
     * @return array
     * @throws NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getProductVendorData($productId = false)
    {
        $vendorData = [];
        $this->_availableVendors = [];
        if (!array_key_exists($productId, $this->_availableVendors)) {
            $this->_availableVendors[$productId] = $this->getAvailableVendorsForProduct($productId);
        }
        $vendors = $this->_availableVendors[$productId]->getData();
        foreach ($vendors as $vendor) {
            $data['vendor_id'] = $vendor['vendor_id'];
            $data['name'] = $vendor['vendor_name'];
            $data['label'] = $vendor['business_name'];
            $data['value'] = $vendor['vendor_id'];
            $data['email'] = $vendor['email'];
            $vendorData[] = $data;
        }
        return $vendorData;
    }

    /**
     * Set product store id
     *
     * @param int $storeId
     * @return $this
     */
    public function setStoreId($storeId)
    {
        return $this->setData(self::STORE_ID, $storeId);
    }

    /**
     * Retrieve Store Id
     *
     * @return int
     * @throws NoSuchEntityException
     */
    public function getStoreId()
    {
        if ($this->request->getParam('store') && $this->request->getParam('store') != 'default') {
            return $this->request->getParam('store');
        } else {
            if ($this->hasData(self::STORE_ID)) {
                return $this->getData(self::STORE_ID);
            }
            return $this->_storeManager->getStore()->getId();
        }
    }

    /**
     * @param $mpid
     * @param $vendorId
     * @return mixed
     */
    public function deleteVendorOffer($mpid, $vendorId)
    {
        return $this->getResource()->deleteVendorOffer($mpid, $vendorId);
    }

    /**
     * Calculate product price based on special price data
     *
     * @param float $basePrice
     * @param float $specialPrice
     * @param string $specialPriceFrom
     * @param string $specialPriceTo
     * @return  float
     */
    public function calculatePrice(
        $basePrice,
        $specialPrice,
        $specialPriceFrom,
        $specialPriceTo
    )
    {
        $sId = null;
        $finalPrice = $this->calculateSpecialPrice(
            $basePrice,
            $specialPrice,
            $specialPriceFrom,
            $specialPriceTo,
            $sId
        );
        return max($finalPrice, 0);
    }

    /**
     * Calculate and apply special price
     *
     * @param float $finalPrice
     * @param float $specialPrice
     * @param string $specialPriceFrom
     * @param string $specialPriceTo
     * @param int|string|Store $store
     * @return float
     */
    public function calculateSpecialPrice(
        $finalPrice,
        $specialPrice,
        $specialPriceFrom,
        $specialPriceTo,
        $store = null
    )
    {
        if ($specialPrice !== null && $specialPrice != false) {
            if ($this->_localeDate->isScopeDateInInterval($store, $specialPriceFrom, $specialPriceTo)) {
                $finalPrice = min($finalPrice, $specialPrice);
            }
        }
        return $finalPrice;
    }

    /**
     * @return mixed
     */
    public function getPrice()
    {
        return $this->getData('price');
    }

    /**
     * @return float
     */
    public function getFinalPrice()
    {
        return $this->calculateSpecialPrice(
            $this->getPrice(),
            $this->getSpecialPrice(),
            $this->getSpecialFromDate(),
            $this->getSpecialToDate()
        );
    }

    /**
     * @return string
     */
    public function getWebsiteDate()
    {
        return $this->_localeDate->date()->format('Y-m-d');
        //return $date = $this->datetime->gmtDate('Y-m-d');
    }

    /**
     * @param null $collection
     * @param string $priceType
     * @param $productId
     * @return mixed|void
     */
    public function getDefaultVendorPrice($collection = null, $priceType = 'price', $productId)
    {
        if (!$collection) {
            return;
        }
        if (!array_key_exists($productId, $this->_defaultVendorPrice) ||
            !array_key_exists($priceType, $this->_defaultVendorPrice[$productId])) {
            $collection->getSelect()->group('main_table.vendor_id')->limit(1);
            $this->_defaultVendorPrice[$productId][$priceType] = $collection;
        }
        return $this->_defaultVendorPrice[$productId][$priceType];
    }

    /**
     * @param $product
     * @param bool $excludeRating
     * @return bool|Type|string
     * @throws NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getMinimumPriceForProduct($product, $excludeRating = false)
    {
        $productId = $product->getId();
        $price = false;
        if ($excludeRating) {
            $minPrice = $this->getMinPriceExcludingRating($productId, $product->getTypeId(), false);
            $minSpecialPrice = $this->getMinSpecialPriceExcludingRating($productId, $product->getTypeId(), false);
        } else {
            $minPrice = $this->getMinPrice($productId, $product->getTypeId(), false, true);
            $minSpecialPrice = $this->getMinSpecialPrice($productId, $product->getTypeId(), false, true);
        }
        if ($minSpecialPrice && $minSpecialPrice > 0) {
            $price = $minSpecialPrice;
        } elseif ($minPrice && $minPrice > 0) {
            $price = $minPrice;
        }
        return $price;
    }

    /**
     * @param null $productId
     * @return Type|string|null
     */
    public function gerConfigurableProductPriceFromIndex($productId = null)
    {
        if (!$productId) {
            return null;
        }
        $configIndexPrice = null;
        $minPrice = $this->_defaultVendorIndexersFactory->getConfigProductPriceByProductId($productId);
        $minSpecialPrice = $this->_defaultVendorIndexersFactory->getConfigProductSpecialPriceByProductId($productId);

        if ($minSpecialPrice && $minSpecialPrice > 0) {
            $configIndexPrice = $minSpecialPrice;
        } elseif ($minPrice && $minPrice > 0) {
            $configIndexPrice = $minPrice;
        }
        return $configIndexPrice;
    }

    /**
     * Set current product id
     *
     * @param int $productId
     * @return $this
     */
    public function setCurrentProductId($productId)
    {
        return $this->setData(self::CURRENT_PRODUCT_ID, $productId);
    }

    /**
     * Retrieve Current Product Id
     *
     * @return int
     */
    public function getCurrentProductId()
    {
        return $this->getData(self::CURRENT_PRODUCT_ID);
    }

    /**
     * @param $collection
     * @param bool $addCustomSpecialPrice
     */
    public function _addRbVendorProductWebsiteData($collection, $addCustomSpecialPrice = false)
    {
        if ($addCustomSpecialPrice) {
            $fields = [
                'rbvpw.website_id',
                'rbvpw.status',
                'rbvpw.reorder_level',
                'rbvpw.warranty_type',
                'rbvpw.condition',
                'rbvpw.special_to_date',
                'rbvpw.special_from_date',
                'rbvpw.price'
            ];
        } else {
            $fields = [
                'rbvpw.website_id',
                'rbvpw.status',
                'rbvpw.reorder_level',
                'rbvpw.warranty_type',
                'rbvpw.condition',
                'rbvpw.special_to_date',
                'rbvpw.special_from_date',
                'rbvpw.special_price',
                'rbvpw.price',
                'rbvpw.cost_price_iqd',
                'rbvpw.cost_price_usd'
            ];
        }
        $collection->getSelect()->joinLeft(
            ['rbvpw' => $this->getResource()->getTable('md_vendor_product_website')],
            "main_table.vendor_product_id = rbvpw.vendor_product_id",
            $fields
        );
    }

    /**
     * @param $collection
     * @throws NoSuchEntityException
     */
    public function _addRbVendorProductStoreData($collection)
    {
        $stores = [0, $this->getStoreId()];
        $collection->getSelect()->joinLeft(
            ['rbvps' => $this->getResource()->getTable('md_vendor_product_store')],
            "main_table.vendor_product_id = rbvps.vendor_product_id AND
            rbvps.store_id IN (" . implode(',', $stores) . ")",
            ['store_id' => 'rbvps.store_id', 'rbvps.warranty_description', 'rbvps.condition_note']
        );
    }

    /**
     * @param $collection
     */
    public function _addVendorData($collection)
    {
        $collection->getSelect()->joinLeft(
            ['rv' => $this->getResource()->getTable('md_vendor')],
            "main_table.vendor_id = rv.vendor_id",
            ['email']
        )->joinLeft(
            ['rvwd' => $this->getResource()->getTable('md_vendor_website_data')],
            "rvwd.vendor_id = rv.vendor_id",
            ['vendor_name' => 'rvwd.name', 'rvwd.business_name', 'rvwd.logo']
        );
    }

    /**
     * @param $collection
     */
    public function excludeDeletedProducts($collection)
    {
        $collection->addFieldToFilter('main_table.is_deleted', ['eq' => 0]);
    }

    /**
     * @param $collection
     * @param $qty
     */
    public function addQtyFilter($collection, $qty)
    {
        $collection->addFieldToFilter('main_table.qty', ['gt' => $qty]);
    }

    /**
     *
     * @param type $collection
     * @throws NoSuchEntityException
     */
    public function addProductData($collection)
    {
        $storeId = $this->getStoreId();
        $stores = implode(",", [0, $storeId]);
        $collection->getSelect()->joinLeft(
            ['prod' => 'catalog_product_entity'],
            "main_table.marketplace_product_id = prod.entity_id",
            ['sku']
        )->joinLeft(
            ['cpevw' => 'catalog_product_entity_varchar'],
            'cpevw.row_id=prod.row_id AND cpevw.attribute_id=' . $this->getAttributeIdofProductUrl() . ' AND cpevw.store_id = ' . $storeId,
            ['product_url' => 'value']
        )->joinLeft(
            ['cpev' => 'catalog_product_entity_varchar'],
            'cpev.row_id = prod.row_id AND cpev.attribute_id=' . $this->getAttributeIdofProductName() . ' AND cpev.store_id in (' . $stores . ')',
            ['product_name' => 'value']
        );
    }

    /**
     * @return mixed
     * @throws NoSuchEntityException
     */
    private function getAttributeIdofProductName()
    {
        $productNameAttributeId = $this->_attributeRepository->get(
            'catalog_product',
            \Magento\Catalog\Model\ResourceModel\Eav\Attribute::CODE_NAME
        )->getId();
        return $productNameAttributeId;
    }

    /**
     * @return mixed
     * @throws NoSuchEntityException
     */
    private function getAttributeIdofProductUrl()
    {
        $productUrlAttributeId = $this->_attributeRepository->get(
            'catalog_product',
            \Magento\Catalog\Model\ResourceModel\Eav\Attribute::CODE_SEO_FIELD_URL_KEY
        )->getId();
        return $productUrlAttributeId;
    }

    /**
     * @param bool $addCustomSpecialPrice
     * @return \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection|null
     * @throws NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getCollection($addCustomSpecialPrice = false)
    {
        $collection = $this->getResourceCollection();
        $this->_addVendorData($collection);
        $this->_addRbVendorProductWebsiteData($collection, $addCustomSpecialPrice);

        $fullActionName = $this->request->getFullActionName();
        $requestStringName = $this->request->getRequestString();

        $notAllowedActions = ['catalog_category_view', "__", "catalogsearch_result_index"];

        if (!in_array($fullActionName, $notAllowedActions) && $requestStringName != "/graphql") {
            $this->_addRbVendorProductStoreData($collection);
        }
        // $this->_addRbVendorProductStoreData($collection);

        if (!in_array($fullActionName, $notAllowedActions) && $requestStringName != "/graphql") {
            $this->_addStoreIds($collection);
        }
//        $this->_addStoreIds($collection);
        $this->_addWebsiteIds($collection);

        if (stripos($this->request->getRequestString(), "graphql") == false) {
            $this->addProductData($collection);
        }
//        $this->addProductData($collection);
        $collection->addFilterToMap('product_name', 'cpev.value');
        $collection->addFilterToMap('vendor_id', 'main_table.vendor_id');
        $collection->addFilterToMap('store_id', 'rbvps.store_id');
        $collection->getSelect()->group('main_table.vendor_product_id');
        $this->_collectionData = $collection;


        return $this->_collectionData;
    }

    /**
     *
     * @param type $item
     */
    protected function setSpecialPriceForItem($item)
    {
        if (!($item->getSpecialPrice() === null)) {
            if ($item->getSpecialPrice() >= $item->getPrice()) {
                $item->setSpecialPrice(null);
            } elseif (($item->getSpecialFromDate() < $this->getWebsiteDate()) &&
                (!($item->getSpecialToDate() === null) && ($item->getSpecialToDate() < $this->getWebsiteDate()))) {
                $item->setSpecialPrice(null);
            } elseif (($item->getSpecialFromDate() > $this->getWebsiteDate()) &&
                ($item->getSpecialToDate() > $this->getWebsiteDate())) {
                $item->setSpecialPrice(null);
            } elseif (($item->getSpecialFromDate() > $this->getWebsiteDate()) &&
                (($item->getSpecialToDate() === null))) {
                $item->setSpecialPrice(null);
            } elseif (($item->getSpecialFromDate() == $this->getWebsiteDate()) &&
                (!($item->getSpecialToDate() === null) && $item->getSpecialToDate() < $this->getWebsiteDate())) {
                $item->setSpecialPrice(null);
            }
        }
    }

    /**
     *
     * @return \Magento\Framework\DB\Select|null
     */
    protected function getProductStores()
    {
        if (!$this->_productStores) {
            $connection = $this->getResource()->getConnection();
            $select = $connection->select()->from(
                ['rbvps' => $this->getResource()->getTable('md_vendor_product_store')],
                [
                    'GROUP_CONCAT(store_id)'
                ]
            );

            $select->where('`rbvps`.`vendor_product_id` = `main_table`.`vendor_product_id`');
            $this->_productStores = $select;
        }
        return $this->_productStores;
    }

    /**
     *
     * @param type $collection
     */
    public function _addStoreIds($collection)
    {
        $collection->getSelect()->columns(['stores' => $this->getProductStores()]);
    }

    /**
     *
     * @return \Magento\Framework\DB\Select|null
     */
    protected function getProductWebsites()
    {
        if (!$this->_productWebsites) {
            $connection = $this->getResource()->getConnection();
            $select = $connection->select()->from(
                ['rbvpw' => $this->getResource()->getTable('md_vendor_product_website')],
                [
                    'GROUP_CONCAT(website_id)'
                ]
            );

            $select->where('`rbvpw`.`vendor_product_id` = `main_table`.`vendor_product_id`');
            $this->_productWebsites = $select;
        }
        return $this->_productWebsites;
    }

    /**
     *
     * @param type $collection
     */
    public function _addWebsiteIds($collection)
    {
        $collection->getSelect()->columns(['websites' => $this->getProductWebsites()]);
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
     *
     * @param string $id
     * @return array
     */
    public function getParentIdsByChild($id = '')
    {
        $parentIdsByChild = [];
        if ($id) {
            $parentId = null;
            $parentIdsByChild = $this->_typeConfigurableFactory->create()->getParentIdsByChild($id);
        }
        return $parentIdsByChild;
    }

    /**
     * Get Vendor Product Id
     * @return int $vendorProductId
     */
    public function getVendorProductId()
    {
        return $this->getData(ProductVendorDataInterface::VENDOR_PRODUCT_ID);
    }

    /**
     * Set Vendor Product Id
     * @param int $vendorProductId
     * @return $this
     */
    public function setVendorProductId(int $vendorProductId)
    {
        return $this->setData(ProductVendorDataInterface::VENDOR_PRODUCT_ID, $vendorProductId);
    }

    /**
     * get Marketplace Product Id
     * @return int $marketPlaceProductId
     */
    public function getMarketplaceProductId()
    {
        return $this->getData(ProductVendorDataInterface::MARKETPLACE_PRODUCT_ID);
    }

    /**
     * set Marketplace Product Id
     * @param int $marketPlaceProductId
     * @return $this
     */
    public function setMarketplaceProductId($marketPlaceProductId)
    {
        return $this->setData(ProductVendorDataInterface::MARKETPLACE_PRODUCT_ID, $marketPlaceProductId);
    }

    /**
     * get Product Parent Id
     * @return int $parentId
     */
    public function getParentId()
    {
        return $this->getData(ProductVendorDataInterface::PARENT_ID);
    }

    /**
     * set Product Parent Id
     * @param int $parentId
     * @return $this
     */
    public function setParentId($parentId)
    {
        return $this->setData(ProductVendorDataInterface::PARENT_ID, $parentId);
    }

    /**
     * get Product Type Id
     * @return string $typeId
     */
    public function getTypeId()
    {
        return $this->getData(ProductVendorDataInterface::TYPE_ID);
    }

    /**
     * set Product Type Id
     * @param string $typeId
     * @return $this
     */
    public function setTypeId($typeId)
    {
        return $this->setData(ProductVendorDataInterface::TYPE_ID, $typeId);
    }

    /**
     * Get Vendor Id
     *
     * @return int
     */
    public function getVendorId()
    {
        return $this->getData(ProductVendorDataInterface::VENDOR_ID);
    }

    /**
     * Set Vendor Id
     * @param int $vendorId
     * @return $this
     */
    public function setVendorId(int $vendorId)
    {
        return $this->setData(ProductVendorDataInterface::VENDOR_ID, $vendorId);
    }

    /**
     * get External Id
     * @return int $externalId
     */
    public function getExternalId()
    {
        return $this->getData(ProductVendorDataInterface::EXTERNAL_ID);
    }

    /**
     * set External Id
     * @param int $externalId
     * @return $this
     */
    public function setExternalId($externalId)
    {
        return $this->setData(ProductVendorDataInterface::EXTERNAL_ID, $externalId);
    }

    /**
     * Get Is Deleted
     * @return int $isDeleted
     */
    public function getIsDeleted()
    {
        return $this->getData(ProductVendorDataInterface::IS_DELETED);
    }

    /**
     * set External Id
     * @param int $isDeleted
     * @return $this
     */
    public function setIsDeleted($isDeleted)
    {
        return $this->setData(ProductVendorDataInterface::IS_DELETED, $isDeleted);
    }

    /**
     * Get Vendor SKU
     * @return string $vendorSku
     */
    public function getVendorSku()
    {
        return $this->getData(ProductVendorDataInterface::VENDOR_SKU);
    }

    /**
     * set Vendor SKU
     * @param string $vendorSku
     * @return $this
     */
    public function setVendorSku($vendorSku)
    {
        return $this->setData(ProductVendorDataInterface::VENDOR_SKU, $vendorSku);
    }

    /**
     * Get Quantity
     * @return float $qty
     */
    public function getQty()
    {
        return $this->getData(ProductVendorDataInterface::QTY);
    }

    /**
     * set Quantity
     * @param float $qty
     * @return $this
     */
    public function setQty($qty)
    {
        return $this->setData(ProductVendorDataInterface::QTY, $qty);
    }

    /**
     * Get Is Offered
     * @return int $isOffered
     */
    public function getIsOffered()
    {
        return $this->getData(ProductVendorDataInterface::IS_OFFERED);
    }

    /**
     * set Quantity
     * @param int $isOffered
     * @return $this
     */
    public function setIsOffered($isOffered)
    {
        return $this->setData(ProductVendorDataInterface::IS_OFFERED, $isOffered);
    }

    /**
     * Get Approved At
     * @return string $approvedAt
     */
    public function getApprovedAt()
    {
        return $this->getData(ProductVendorDataInterface::APPROVED_AT);
    }

    /**
     * set Approved At
     * @param string $approvedAt
     * @return $this
     */
    public function setApprovedAt($approvedAt)
    {
        return $this->setData(ProductVendorDataInterface::APPROVED_AT, $approvedAt);
    }

    /**
     * @inheritdoc
     */
    public function setExtensionAttributes(
        \Magedelight\Catalog\Api\Data\ProductVendorDataExtensionInterface $extensionAttributes
    )
    {
        $this->extenstionAttributes = $extensionAttributes;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getExtensionAttributes()
    {
        return $this->extenstionAttributes;
    }

    /**
     * @param $productId
     * @return \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getVendorRawCollection($productId)
    {
        $collection = $this->getResourceCollection()->addFieldToFilter('marketplace_product_id', $productId);
        return $collection;
    }

    /**
     * @param $vendorId
     * @param $productId
     * @param bool $addQtyFilter
     * @return false|\Magento\Framework\DataObject
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getSpecialPriceOnRequest($vendorId, $productId, $addQtyFilter = true)
    {

        $collection = $this->getResourceCollection();
        $collection->getSelect()
            ->joinLeft(['mvpw' => 'md_vendor_product_website'],
                '(main_table.vendor_id = mvpw.vendor_id and main_table.vendor_product_id = mvpw.vendor_product_id)',
                ['special_price', ' price', 'special_to_date', 'special_from_date', `mvpw.vendor_id`]
            );
        $collection->addFieldToFilter('main_table.vendor_id', ['eq' => $vendorId])
            ->addFieldToFilter('main_table.marketplace_product_id', ['eq' => $productId])
            /*->addFieldToFilter('mvpw.special_from_date',[['gteq' => 'curdate()'], ['null' => true]])
            ->addFieldToFilter('mvpw.special_to_date',[['gteq' => 'curdate()'], ['null' => true]])*/
            ->addFieldToFilter('main_table.qty', ['gt' => 0])
            ->addFieldToFilter('mvpw.status', ['in' => [VendorStatus::VENDOR_STATUS_ACTIVE]]);

        if ($collection->count()) {
            $item = $collection->getFirstItem();
            if ($item) {
                $this->setSpecialPriceForItem($item);
            }
            return $item;
        } else {
            return false;
        }

    }

    /**
     * @param $vendorId
     * @param $productId
     * @return \Magento\Framework\DataObject
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getVendorQtyOnRequest($vendorId, $productId)
    {

        $collectionQty = $this->getResourceCollection();
        $collectionQty->getSelect()
            ->joinLeft(['mvpw' => 'md_vendor_product_website'],
                '(main_table.vendor_id = mvpw.vendor_id and main_table.vendor_product_id = mvpw.vendor_product_id)',
                ['special_price', ' price']
            );
        $collectionQty->addFieldToFilter('main_table.vendor_id', ['eq' => $vendorId])
            ->addFieldToFilter('main_table.marketplace_product_id', ['eq' => $productId])
            ->addFieldToFilter('main_table.qty', ['gt' => 0])
            ->addFieldToFilter('mvpw.status', ['in' => [VendorStatus::VENDOR_STATUS_ACTIVE]]);
        return $collectionQty->getFirstItem();
    }
}
