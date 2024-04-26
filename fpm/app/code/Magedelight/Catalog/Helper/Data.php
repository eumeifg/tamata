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
namespace Magedelight\Catalog\Helper;

use Magento\Catalog\Model\Product\Type;
use Magento\CatalogInventory\Api\StockStatusRepositoryInterface;
use Magento\CatalogInventory\Model\Spi\StockRegistryProviderInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\MediaStorage\Model\File\Uploader;
use Magento\Search\Model\QueryFactory;
use Magento\Store\Model\ScopeInterface;

class Data extends AbstractHelper
{
    const PRICE_EXCLUDING_TAX = 1;
    const PRICE_INCLUDING_TAX = 2;
    const PRICE_BOTH_TAX = 3;
    const PRICE_TYPE_SPECIAL = 'special';
    const PRICE_TYPE_FINAL = 'final';
    const PRICE_TYPE_BASE = 'base';
    const XML_PATH_VALIDATION_VENDOR_SKU = 'vendor_product/validation/vendor_sku';
    const XML_PATH_VALIDATION_MANUFACTURER_SKU = 'vendor_product/validation/manufacturer_sku';
    const XML_PATH_IS_ALLOW_VARIANTS = 'vendor_product/validation/allow_variants';

    /**
     * @var \Magento\Catalog\Model\ProductRepository
     */
    protected $productRepository;

    /**
     * @var \Magento\CatalogInventory\Api\StockItemRepositoryInterface
     */
    protected $stockItemRepository;

    /**
     * @var \Magento\Framework\Api\DataObjectHelper
     */
    protected $dataObjectHelper;

    /**
     * @var \Magento\Catalog\Model\Product
     */
    protected $_product;

    /**
     * @var Magento\CatalogInventory\Api\StockStateInterface
     */
    protected $_stockStateInterface;

    /**
     * @var Magento\CatalogInventory\Api\StockRegistryInterface
     */
    protected $_stockRegistry;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_registry;

    /**
     * @var \Magedelight\Catalog\Helper\Pricing\Data
     */
    protected $_priceHelper;

    /**
     * @var \Magedelight\Vendor\Api\VendorRepositoryInterface
     */
    protected $vendorRepository;

    /**
     * @var \Magedelight\Catalog\Model\ProductFactory
     */
    protected $_vendorProductFactory;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Tax\Api\TaxCalculationInterface
     */
    protected $taxCalculation;

    /**
     * @var \Magento\Customer\Model\Session
     */
    public $customerSession;

    /**
     * @var \Magento\Framework\Filesystem
     */
    protected $filesystem;

    /**
     * @var \Magento\Catalog\Model\Product\Media\Config
     */
    protected $mediaConfig;

    /**
     * Media directory
     *
     * @var WriteInterface
     */
    protected $mediaDirectory;

    /**
     * Root directory
     *
     * @var WriteInterface
     */
    protected $rootDirectory;

    /**
     * @var \Magento\Framework\Module\Dir\Reader
     */
    protected $moduleReader;

    /**
     * @var \Magento\Framework\Json\Decoder
     */
    protected $jsonDecoder;

    /**
     * @var \Magento\Catalog\Helper\Image
     */
    protected $imageHelper;

    /**
     * @var \Magedelight\Catalog\Model\Source\Condition
     */
    protected $productCondition;

    /**
     * @var \Magedelight\Backend\Model\UrlInterface
     */
    protected $sellerUrl;

    /**
     * @var \Magedelight\Backend\Helper\Data
     */
    protected $backendHelper;

    /**
     * @var StockRegistryProviderInterface
     */
    protected $stockRegistryProvider;
    /**
     * @var StockStatusRepositoryInterface
     */
    protected $stockStatusRepository;

    protected $_getAvailableVendorsForProduct = null;
    /**
     * @var \Magento\Framework\Escaper
     */
    protected $escaper;

    /**
     * Data constructor.
     * @param \Magedelight\Catalog\Model\ProductFactory $vendorProductFactory
     * @param Pricing\Data $priceHelper
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Catalog\Model\Product $product
     * @param \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry
     * @param \Magento\CatalogInventory\Api\StockStateInterface $stockStateInterface
     * @param \Magento\Framework\Api\DataObjectHelper $dataObjectHelper
     * @param \Magento\CatalogInventory\Api\StockItemRepositoryInterface $stockItemRepository
     * @param \Magento\Catalog\Model\ProductRepository $productRepository
     * @param \Magento\Framework\App\Helper\Context $urlContext
     * @param \Magento\Tax\Api\TaxCalculationInterface $taxCalculation
     * @param StockRegistryProviderInterface $stockRegistryProvider
     * @param StockStatusRepositoryInterface $stockStatusRepository
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Catalog\Model\Product\Media\Config $mediaConfig
     * @param \Magento\Framework\Module\Dir\Reader $moduleReader
     * @param \Magento\Framework\Json\Decoder $jsonDecoder
     * @param \Magedelight\Catalog\Model\Source\Condition $productCondition
     * @param \Magedelight\Backend\Model\UrlInterface $sellerUrl
     * @param \Magedelight\Backend\Helper\Data $backendHelper
     * @param \Magento\Framework\Escaper $escaper
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function __construct(
        \Magedelight\Catalog\Model\ProductFactory $vendorProductFactory,
        \Magedelight\Catalog\Helper\Pricing\Data $priceHelper,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Catalog\Model\Product $product,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        \Magento\CatalogInventory\Api\StockStateInterface $stockStateInterface,
        \Magento\Framework\Api\DataObjectHelper $dataObjectHelper,
        \Magento\CatalogInventory\Api\StockItemRepositoryInterface $stockItemRepository,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        \Magento\Framework\App\Helper\Context $urlContext,
        \Magento\Tax\Api\TaxCalculationInterface $taxCalculation,
        StockRegistryProviderInterface $stockRegistryProvider,
        StockStatusRepositoryInterface $stockStatusRepository,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Catalog\Model\Product\Media\Config $mediaConfig,
        \Magento\Framework\Module\Dir\Reader $moduleReader,
        \Magento\Framework\Json\Decoder $jsonDecoder,
        \Magedelight\Catalog\Model\Source\Condition $productCondition,
        \Magedelight\Backend\Model\UrlInterface $sellerUrl,
        \Magedelight\Backend\Helper\Data $backendHelper,
        \Magento\Framework\Escaper $escaper
    ) {
        $this->_vendorProductFactory = $vendorProductFactory->create();
        $this->_priceHelper = $priceHelper;
        $this->_registry = $coreRegistry;
        $this->_product = $product;
        $this->_stockRegistry = $stockRegistry;
        $this->_stockStateInterface = $stockStateInterface;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->stockItemRepository = $stockItemRepository;
        $this->productRepository = $productRepository;
        $this->_urlBuilder = $urlContext->getUrlBuilder();
        $this->_request = $urlContext->getRequest();
        $this->urlEncoder = $urlContext->getUrlEncoder();
        $this->scopeConfig = $urlContext->getScopeConfig();
        $this->taxCalculation = $taxCalculation;
        $this->customerSession = $customerSession;
        $this->filesystem = $filesystem;
        $this->mediaDirectory = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $this->rootDirectory = $filesystem->getDirectoryWrite(DirectoryList::ROOT);
        $this->mediaConfig = $mediaConfig;
        $this->moduleReader = $moduleReader;
        $this->jsonDecoder = $jsonDecoder;
        $this->productCondition = $productCondition;
        $this->sellerUrl = $sellerUrl;
        $this->backendHelper = $backendHelper;
        $this->stockRegistryProvider = $stockRegistryProvider;
        $this->stockStatusRepository = $stockStatusRepository;
        $this->escaper = $escaper;
        parent::__construct($urlContext);
    }

    /**
     * @param $vendorId
     * @param $productId
     * @param $addQtyFilter
     * @return \Magedelight\Vendor\Model\Vendor
     * @throws \Exception
     */
    public function getVendorProduct($vendorId, $productId, $addQtyFilter = true)
    {
        $vendorProduct = $this->_vendorProductFactory->getVendorProduct($vendorId, $productId, $addQtyFilter);
        return $vendorProduct;
    }

    /**
     *
     * @return boolean true | false
     */
    public function checkVendorSkuValidation()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_VALIDATION_VENDOR_SKU,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     *
     * @return boolean true | false
     */
    public function checkManufacturerSkuValidation()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_VALIDATION_MANUFACTURER_SKU,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     *
     * @return boolean true | false
     */
    public function isAllowVariants()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_IS_ALLOW_VARIANTS,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     *
     * @param int $productId
     * @return vendorId | false
     */
    public function getDefaultVendorId($productId = null)
    {
        if ($productId) {
            $collection = $this->_vendorProductFactory->getProductDefaultVendor(false, $productId);
            if ($collection) {
                return $collection->getVendorId();
            }
        }

        return false;
    }

    /**
     * @param type $productId
     * @return boolean
     */
    public function getDefaultVendorName($productId = null)
    {
        if ($productId) {
            $vendor = $this->_vendorProductFactory->getProductDefaultVendor(false, $productId);
            if ($vendor) {
                return $vendor->getBusinessName();
            }
        }
        return false;
    }

    /**
     *
     * @param type $vendorId
     * @param type $productId
     * @return mixed qty|null
     * @throws \Exception
     */
    public function getVendorQty($vendorId, $productId)
    {

        $vendorProduct = $this->_vendorProductFactory->getVendorQtyOnRequest($vendorId, $productId);

        if ($vendorProduct) {
            return $vendorProduct->getQty();
        }
        return 0;
    }

    /**
     *
     * @param int $vendorId
     * @param int $productId
     * @return mixed Product's vendor Price | null
     * @throws \Exception
     */
    public function getVendorPrice($vendorId, $productId)
    {
        /* @var $vendorProduct \Magedelight\Catalog\Model\Product\ */
        $vendorProduct = $this->_vendorProductFactory->getSpecialPriceOnRequest($vendorId, $productId);
        if ($vendorProduct) {
            return $vendorProduct->getPrice();
        }
        return null;
    }

    /**
     *
     * @param type $vendorId
     * @param type $productId
     * @return special price|null
     * @throws \Exception
     */
    public function getVendorSpecialPrice($vendorId, $productId)
    {
        $vendorProduct = $this->_vendorProductFactory->getSpecialPriceOnRequest($vendorId, $productId);
        if ($vendorProduct) {
            return $vendorProduct->getSpecialPrice();
        }
        return null;
    }

    /**
     *
     * @param int $vendorId
     * @param int $productId
     * @param bool $convert
     * @return vendor Price | Special Price
     * @throws \Exception
     */
    public function getVendorFinalPrice($vendorId, $productId, $convert = true)
    {
        $rulePrice = $this->getCatalogRulePrice($productId, $vendorId);
        $priceNullCheck = $this->getVendorSpecialPrice($vendorId, $productId);
        if ($priceNullCheck !== null) {
            $price = $priceNullCheck;
        } else {
            $price = $this->getVendorPrice($vendorId, $productId);
        }
        $price = ($rulePrice && $rulePrice < $price) ? $rulePrice : $price;
        return ($convert) ? $this->_priceHelper->currency($price, false, true) : $price;
    }

    /**
     * Convert and format price value for current application store
     *
     * @param   float $value
     * @param   bool $format
     * @param   bool $includeContainer
     * @param   bool $convertAndRound
     * @param   string $currency
     * @return  float|string
     */
    public function currency(
        $value,
        $format = true,
        $includeContainer = true,
        $convertAndRound = false,
        $currency = null
    ) {
        return $this->_priceHelper->currency($value, $format, $includeContainer, $convertAndRound, $currency);
    }

    /**
     *
     * @param \Magento\ConfigurableProduct\Block\Product\View\Type\Configurable $currentProduct
     * @param \Magento\ConfigurableProduct\Block\Product\View\Type\Configurable $allowedProducts
     * @return array
     */
    public function getOptions($currentProduct, $allowedProducts)
    {
        $options = [];
        foreach ($allowedProducts as $product) {
            $productId = $product->getId();
            $defaultVendor = $this->getProductDefaultVendor($productId);
            $options['default_vendor'][$productId]['vendorId'] = ($defaultVendor) ? $defaultVendor->getVendorId() : '';
            $options['default_vendor'][$productId]['vendorName'] =
                ($defaultVendor) ? $defaultVendor->getBusinessName() : '';
            $options['default_vendor'][$productId]['vendorQty'] = $this->getTotalProductQty($productId);

            $options['default_vendor'][$productId]['vendorRatings'] = $this->getDefaultVendorRatingHtml($productId);
            $options['default_vendor'][$productId]['noOfVendors'] = $this->getProductNoOfVendors($productId);
            $options['default_vendor'][$productId]['condition_details'] =
                $this->getProductConditionDetails($defaultVendor);
            $options['default_vendor'][$productId]['warranty_details'] =
                $this->getProductWarrantyDetails($defaultVendor);
            /* Get lowest price for the main product excluding the default vendor concept.*/
            $options['default_vendor'][$productId]['finalPrice'] = $this->_priceHelper->currency(
                $this->_vendorProductFactory->getMinimumPriceForProduct($product, true),
                true,
                false
            );
            /*$this->_priceHelper->currency(
                $product->getPriceInfo()->getPrice('final_price')->getAmount()->getValue(),
                true,
                false
            );*/
            /* Get lowest price for the main product excluding the default vendor concept.*/
        }
        return $options;
    }

    /**
     *
     * @param \Magedelight\Swatches\Block\Product\Renderer\Listing\Configurable $currentProduct
     * @param \Magento\ConfigurableProduct\Block\Product\View\Type\Configurable $allowedProducts
     * @return array
     */
    public function getOptionsForListing($currentProduct, $allowedProducts)
    {
        $options = [];
        foreach ($allowedProducts as $product) {
            $productId = $product->getId();
            $defaultVendor = $this->getProductDefaultVendor($productId);
            $options['default_vendor'][$productId]['vendorId'] = ($defaultVendor) ? $defaultVendor->getVendorId() : '';
        }
        return $options;
    }

    /**
     * Get allowed attributes
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return array
     */
    public function getAllowAttributes($product)
    {
        return $product->getTypeInstance()->getConfigurableAttributes($product);
    }

    /**
     * @param $field
     * @param null $storeId
     * @return mixed
     */
    public function getConfigValue($field, $storeId = null)
    {
        return $this->scopeConfig->getValue(
            $field,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     *
     * @param type $query
     * @return string
     */
    public function getResultUrl($query = null)
    {
        return $this->sellerUrl->getUrl(
            'rbcatalog/product/index',
            ['_query' => [QueryFactory::QUERY_VAR_NAME => $query], '_secure' => $this->_request->isSecure()]
        );
    }

    /**
     *
     * @return string
     */
    public function searchText()
    {
        $search = $this->escaper->escapeHtml($this->_request->getParam('search'));
        if ($search) {
            return $search;
        } else {
            return '';
        }
    }

    /**
     *
     * @return string
     */
    public function getProductCreateUrl()
    {
        return $this->sellerUrl->getUrl('rbcatalog/product/create');
    }

    /**
     *
     * @return string
     */
    public function getQueryParamName()
    {
        return QueryFactory::QUERY_VAR_NAME;
    }

    /**
     *
     * @return string
     */
    public function getImageSize($unit = '')
    {
        if ($unit == 'bytes') {
            return $this->getConfigValue('vendor_product/validation/image_size') * 1024;
        }
        return $this->getConfigValue('vendor_product/validation/image_size');
    }

    /**
     *
     * @return string
     */
    public function getImageHeight()
    {
        return $this->getConfigValue('vendor_product/validation/image_height');
    }

    /**
     *
     * @return string
     */
    public function getImageWidth()
    {
        return $this->getConfigValue('vendor_product/validation/image_width');
    }

    /**
     * @param $productId
     * @param bool $clear
     * @param bool $addQtyFilter
     * @return \Magedelight\Catalog\Model\collection|null
     */
    public function getAvailableVendorsForProduct($productId, $clear = false, $addQtyFilter = false)
    {
        if (!$this->_getAvailableVendorsForProduct || $clear) {
            if ($productId) {
                $collection = $this->_vendorProductFactory->getAvailableVendorsForProduct(
                    $productId,
                    false,
                    $addQtyFilter
                );
                $this->_getAvailableVendorsForProduct = $collection;
            }
        }
        return $this->_getAvailableVendorsForProduct;
    }

    /**
     *
     * @param int $productId
     * @return number vendors for product
     */
    public function getProductNoOfVendors($productId)
    {
        return $this->getAvailableVendorsForProduct($productId, true)->count() - 1;
    }

    /**
     *
     * @param int $productId
     * @return number vendors for product
     */
    public function getProductNoOfVendorsForGraphQl($productId)
    {
        return $this->getAvailableVendorsForProductForGraphQl($productId, true)->count() - 1;
    }

    /**
     * @param $productId
     * @param bool $clear
     * @param bool $addQtyFilter
     * @return \Magedelight\Catalog\Model\collection|null
     */
    public function getAvailableVendorsForProductForGraphQl($productId, $clear = false, $addQtyFilter = false)
    {
        if (!$this->_getAvailableVendorsForProduct || $clear) {
            if ($productId) {
                $collection = $this->_vendorProductFactory->getAvailableVendorsForProductForGraphQl(
                    $productId,
                    false,
                    $addQtyFilter
                );
                $this->_getAvailableVendorsForProduct = $collection;
            }
        }
        return $this->_getAvailableVendorsForProduct;
    }
    /**
     * @param $product
     * @return array
     */
    public function getProductConditionDetails($product)
    {
        return ['condition'=> $this->getConditionOptionText($product->getCondition()),
            'conditionNote'=> $product->getConditionNote()
                ];
    }

    /**
     * @param $product
     * @return array
     */
    public function getProductWarrantyDetails($product)
    {
        return [
            'warranty_description'=> $product->getWarrantyDescription()
        ];
    }

    /**
     * Counts total vendors of live product
     * @param int $productId
     * @return number vendors for product
     */
    public function getTotalVendorCountByProduct($productId)
    {
        return $this->getAvailableVendorsForProduct($productId)->count();
    }

    /**
     *
     * @param int $productId
     * @return collection
     */
    public function getProductDefaultVendor($productId = null)
    {
        if ($productId) {
            $collection = $this->_vendorProductFactory->getProductDefaultVendor(false, $productId);
        } else {
            $collection = $this->_vendorProductFactory->getProductDefaultVendor(
                $this->getRequest()->getParam('v', false),
                $this->getCurrentProductId()
            );
        }
        return $collection;
    }

    /**
     *
     * @param object $data
     * @return mixed price HTML
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getPriceHtml($data, $productId = null)
    {
        $basePrice = $specialPrice = $data->getPrice();
        $rulePrice = false;
        if ($productId) {
            $product = $this->productRepository->getById($productId);
            $basePrice = $this->getVendorProductPrice($product, $data, self::PRICE_TYPE_BASE);
            $rulePrice = $this->getCatalogRulePrice($productId, $data->getVendorId());
        }
        $this->_vendorProductFactory->setSpecialPriceForItem($data);
        $specialPrice = $rulePrice;
        if (!($data->getSpecialPrice() === null) && $data->getSpecialPrice() < $data->getPrice() && $productId) {
            $specialPrice = $this->getVendorProductPrice($product, $data, self::PRICE_TYPE_SPECIAL);
            $specialPrice = ($rulePrice && $rulePrice < $specialPrice) ? $rulePrice : $specialPrice;
        }
        if($specialPrice)
        {
            $html = '<span class="special-price">';
            $html .= $this->_priceHelper->currency($specialPrice, true, false) . '</span>';
            $html .= '<span class="old-price">' . $this->_priceHelper->currency($basePrice, true, false) . '</span>';
            return $html;
        }
        return '<span class="price">' . $this->_priceHelper->currency($basePrice, true, false) . '</span>';
    }

    /**
     * @param $productId
     * @param $vendorId
     * @return bool|(float)
     */

    public function getCatalogRulePrice($productId, $vendorId)
    {
        return false;
    }

    /**
     *
     * @return registry variable
     */
    public function getCurrentProductUrl()
    {
        return $this->_registry->registry('current_product');
    }

    /**
     * Update stock for single product
     * @param type $event
     * @param type $action
     * @return \Magedelight\Catalog\Helper\Data
     */
    public function updateProductStock($event, $action = null)
    {
        if ($event->getVendorProduct()->getMarketplaceProductId()) {
            $productId = $event->getVendorProduct()->getMarketplaceProductId();
            $this->updateStockStatusForProduct($productId, $event->getVendorProduct()->getTypeId());
        }
        return $this;
    }

    /**
     * Update stock for single product. Used for deal module
     * @param type $event
     * @param type $action
     * @return \Magedelight\Catalog\Helper\Data
     */
    public function updateProductStockSimple($event, $action = null)
    {
        if ($event->getProductId()) {
            $productId = $event->getProductId();
            $stockItem = $this->_stockRegistry->getStockItem($productId);

            $qty = $this->getTotalProductQty($productId);

            if ($qty > 0) {
                $isInStock = true;
            } else {
                $isInStock = false;
            }

            $stockItemData = [
                'qty' => $qty,
                'is_in_stock' => $isInStock
            ];

            $this->dataObjectHelper->populateWithArray(
                $stockItem,
                $stockItemData,
                \Magento\CatalogInventory\Api\Data\StockItemInterface::class
            );
            $this->stockItemRepository->save($stockItem);
        }
        return $this;
    }

    /**
     *
     * @param type $event
     * @param type $action
     * @return \Magedelight\Catalog\Helper\Data
     */
    public function updateProductStockAdmin($event, $action = null)
    {
        if ($event->getVendorProduct()->getMarketplaceProductId()) {
            $productId = $event->getVendorProduct()->getMarketplaceProductId();

            $stockItem = $this->_stockRegistry->getStockItem($productId, 1);

            $qty = $this->getTotalProductQty($productId);

            if ($event->getVendorProduct()->getTypeId() == Type::DEFAULT_TYPE) {
                if ($qty > 0) {
                    $isInStock = true;
                } else {
                    $isInStock = false;
                }
            } else {
                $isInStock = true;
            }

            $stockItemData = [
                'qty' => $qty,
                'is_in_stock' => $isInStock
            ];

            $this->dataObjectHelper->populateWithArray(
                $stockItem,
                $stockItemData,
                \Magento\CatalogInventory\Api\Data\StockItemInterface::class
            );
            $this->stockItemRepository->save($stockItem);
        }
        return $this;
    }

    /**
     * Update stock for mass product
     * @param $event
     * @param null $action
     * @param array $productIds
     * @return Data
     */
    public function updateMultipleProductStock($event, $action = null, $productIds = [])
    {
        if (!empty($event->getVendorProducts())) {
            foreach ($event->getVendorProducts() as $data) {
                $productId = $data['marketplace_product_id'];
                $this->updateStockStatusForProduct($productId);
            }
        } elseif (!empty($productIds)) {
            foreach ($productIds as $productId) {
                $this->updateStockStatusForProduct($productId);
            }
        }

        return $this;
    }

    /**
     * @param integer $productId
     * @param string $productType
     */
    protected function updateStockStatusForProduct($productId, $productType = Type::DEFAULT_TYPE)
    {
        $stockItem = $this->_stockRegistry->getStockItem($productId, 1);
        $qty = $this->getTotalProductQty($productId);

        if ($productType && $productType == Type::DEFAULT_TYPE) {
            if ($qty > 0) {
                $isInStock = true;
            } else {
                $isInStock = false;
            }
        } else {
            $isInStock = true;
        }

        $stockItemData = [
            'qty' => $qty,
            'is_in_stock' => $isInStock
        ];

        $this->dataObjectHelper->populateWithArray(
            $stockItem,
            $stockItemData,
            \Magento\CatalogInventory\Api\Data\StockItemInterface::class
        );
        $this->stockItemRepository->save($stockItem);
        if ($stockStatus = $this->stockRegistryProvider->getStockStatus(
            $stockItem->getProductId(),
            $stockItem->getWebsiteId()
        )
        ) {
            $this->stockStatusRepository
                ->save($stockStatus)->setStockStatus($isInStock);
        }
    }

    /**
     *
     * @param type $productId
     * @return mixed Total Qty of product
     */
    public function getTotalProductQty($productId)
    {
        return $this->_vendorProductFactory->getTotalProductQty($productId);
    }

    /**
     *
     * @param type $productId
     * @return mixed Vendor Rating HTML
     */
    public function getDefaultVendorRatingHtml($productId = null, $vendorId = null)
    {
        if ($productId && $vendorId) {
            $vendor = $this->_vendorProductFactory->getProductDefaultVendor($vendorId, $productId);
        } else {
            $vendor = $this->getProductDefaultVendor($productId);
        }
        if ($vendor->getRatingAvg() == 0) {
            $html = '<div class="rating-summary">
                             <div class="rating-result" title="0%">
                                 <span style="width:0%">
                                     <span>
                                         <span itemprop="ratingValue">50</span>% of
                                         <span itemprop="bestRating">100</span>
                                     </span>
                                 </span>
                             </div>
                        </div>';
        } else {
            $html = ' <div class="rating-summary">
                             <div class="rating-result" title="' . $this->getRating($vendor->getRatingAvg()) . '%">
                                 <span style="width:' . $this->getRating($vendor->getRatingAvg()) . '%">
                                     <span>
                                         <span itemprop="ratingValue">' . $this->getRating($vendor->getRatingAvg()) . '
                                         </span>% of <span itemprop="bestRating">100</span>
                                     </span>
                                 </span>
                             </div>
                         </div>';
        }

        return $html;
    }

    /**
     * collect marketplace productId from md_vendor_product for given parent productId
     * sort it by asc order by price
     * @param bool $mainProductId
     * @param bool $vendorId
     * @param bool $addQtyFilter
     * @return array
     */
    public function getVendorDefaultProduct($mainProductId = false, $vendorId = false, $addQtyFilter = true)
    {
        return $this->_vendorProductFactory->getVendorDefaultProduct($mainProductId, $vendorId, $addQtyFilter);
    }

    /**
     * collect marketplace productId from md_vendor_product for given parent productId
     * sort it by asc order by price
     * @param bool $mainProductId
     * @param bool $vendorId
     * @param bool $addQtyFilter
     * @return array
     */
    public function getVendorDefaultProductConfig($mainProductId = false, $vendorId = false, $addQtyFilter = true)
    {
        return $this->_vendorProductFactory->getVendorDefaultProductConfig($mainProductId, $vendorId, $addQtyFilter);
    }

    /**
     *
     * @param $vendorSku
     * @return \Magedelight\Catalog\Model\Product|false
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function loadVendorProductBySku($vendorSku)
    {
        $vendorProduct = $this->_vendorProductFactory->getVendorProductsBySku($vendorSku);
        return $vendorProduct;
    }

    /**
     * @param type $productId
     * @return string
     */
    public function getAddToCartUrl($productId)
    {
        $routeParams = [
            \Magento\Framework\App\ActionInterface::PARAM_NAME_URL_ENCODED => $this->urlEncoder->encode(
                $this->_urlBuilder->getCurrentUrl()
            ),
            '_secure' => $this->_request->isSecure(),
            'product' => $productId,
        ];

        return $this->_urlBuilder->getUrl('checkout/cart/add/', $routeParams);
    }

    /**
     * @param type $productId
     * @return type
     */
    public function getRequestedVendor($productId)
    {
        $vendorId = $this->_request->getParam('v');
        if (isset($vendorId)) {
            return $vendorId;
        } else {
            $vendorId = $this->getDefaultVendorId($productId);
            return $vendorId;
        }
    }

    /**
     * @param type $product
     * @param type $defaultVendorProduct
     * @param string $priceType
     * @return type
     */
    public function getVendorProductPrice($product, $defaultVendorProduct, $priceType = self::PRICE_TYPE_BASE)
    {
        $productPrice = $price = $defaultVendorProduct->getPrice();
        if ($priceType == self::PRICE_TYPE_FINAL) {
            $productPrice = $price = $defaultVendorProduct->getFinalPrice();
        }
        if ($priceType == self::PRICE_TYPE_SPECIAL) {
            $productPrice = $price = $defaultVendorProduct->getSpecialPrice();
        }
        $productPriceWithTaxData = $this->getProductPriceWithTaxDetails($product, $price);
        if ($productPriceWithTaxData) {
            $productPrice = $productPriceWithTaxData;
        } else {
            $productPrice = $price;
        }
        return $productPrice;
    }

    /**
     * @param type $product
     * @param type $price
     * @return boolean
     */
    public function getProductPriceWithTaxDetails($product, $price)
    {
        if ($taxAttribute = $product->getCustomAttribute('tax_class_id')) {
            // First get base price (=price excluding tax)
            $productRateId = $taxAttribute->getValue();
            $rate = $this->taxCalculation->getCalculatedRate(
                $productRateId,
                $this->customerSession->getCustomer()->getId()
            );
            if ((int) $this->scopeConfig->getValue(
                'tax/calculation/price_includes_tax',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            ) === 1
            ) {
                // Product price in catalog is including tax.
                $priceExcludingTax = $price / (1 + ($rate / 100));
            } else {
                // Product price in catalog is excluding tax.
                $priceExcludingTax = $price;
            }

            $priceIncludingTax = $priceExcludingTax + ($priceExcludingTax * ($rate / 100));

            $prices = [
                'incl' => $priceIncludingTax,
                'excl' => $priceExcludingTax
            ];
            if ((int) $this->scopeConfig->getValue(
                'tax/display/type',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            ) === \Magedelight\Catalog\Helper\Data::PRICE_INCLUDING_TAX) {
                return $productPrice = $prices['incl'];
            } else {
                return $productPrice = $prices['excl'];
            }
        }
        return false;
    }

    /**
     * @param $shippingRates
     * @param $uniqueColumnsKey
     * @return array
     */
    public function checkCSVForDuplicateRows($shippingRates, $uniqueColumnsKey)
    {
        if (empty($shippingRates) || empty($uniqueColumnsKey)) {
            return [];
        }
        $uniqueColumns = $duplicateRows = [];
        $rowCount = 1;
        foreach ($shippingRates as $key => $val) {
            if ($key == 0) {
                continue;
            }
            foreach ($uniqueColumnsKey as $columnKey) {
                $uniqueColumns[$rowCount][] = trim(strtolower($val[$columnKey]));
            }
            $rowCount++;
        }
        $new_arr = array_map("unserialize", array_unique(array_map("serialize", $uniqueColumns)));
        $duplicateRows = array_diff(array_keys($uniqueColumns), array_keys($new_arr));
        return $duplicateRows;
    }

    /**
     * @param int $storeId
     * @return array
     */
    public function getVariantColumns($storeId = 0)
    {
        if ($storeId == 0) {
            return [
                'Name',
                'Image',
                'Vendor Sku',
                'Condition',
                'Condition Note',
                'Price',
                'Special Price',
                'Special From Date',
                'Special To Date',
                'Qty',
                'Weight',
                'Reorder Level',
                'Warranty Type',
                'Warranty Description'
            ];
        } else {
            return [
                'Name',
                'Vendor Sku',
                'Condition Note',
                'Warranty Description'
            ];
        }
    }

    /**
     *
     * @param string $image
     * @param boolean $skipFileCheck
     * @param boolean $isTempImage
     * @return string
     */
    public function getTmpFileIfExists($image = '', $skipFileCheck = false, $isTempImage = true)
    {
        if ($image) {
            if (strpos($image, '/') !== false) {
                $image_path = $image;
            } else {
                $image_path = Uploader::getDispretionPath($image) . '/' . strtolower($image);
            }
            if (!$skipFileCheck) {
                $catalogPath = ($isTempImage) ? 'tmp/' . $this->mediaConfig->getBaseMediaPath() :
                    $this->mediaConfig->getBaseMediaPath();
                $isFile = $this->mediaDirectory->isFile($catalogPath . $image_path);
                if ($isFile) {
                    return $image_path;
                }
                return null;
            }
            return $image_path;
        }
    }

    /**
     *
     * @param string $source_path
     * @param string $target_path
     * @return string
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function cloneTmpFile($source_path = '', $target_path = '')
    {
        if ($source_path && $target_path) {
            $isFile = $this->mediaDirectory->isFile($this->mediaConfig->getTmpMediaPath($source_path));
            if ($isFile) {
                $isCopied = $this->rootDirectory->copyFile(
                    $this->mediaDirectory->getAbsolutePath($this->mediaConfig->getTmpMediaPath($source_path)),
                    $this->mediaDirectory->getAbsolutePath($this->mediaConfig->getTmpMediaPath($target_path))
                );
                return $isCopied;
            }
        }
    }

    /**
     *
     * @param string $path
     * @return string
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function deleteTmpFile($path = '')
    {
        if ($path) {
            $this->rootDirectory->delete(
                $this->mediaDirectory->getAbsolutePath($this->mediaConfig->getTmpMediaPath($path))
            );
        }
    }

    /**
     * Return Image path while product is live and request for edit
     * @param $item
     * @return string
     */
    public function getBaseTmpImage($item)
    {
        $imagePath = null;
        if ($item->getBaseImage()) {
            $baseImage = $this->jsonDecoder->decode($item->getBaseImage());
            if (is_array($baseImage)) {
                $baseImage = $baseImage['file'];
            }
            $isTempImage = ($item->getMarketplaceProductId()) ? false : true;
            $file = $this->getTmpFileIfExists($baseImage, false, $isTempImage);
            if (!$file) {
                return $this->getImageHelper()->getDefaultPlaceholderUrl('thumbnail');
            }
            if ($isTempImage) {
                $imagePath= $this->_urlBuilder->getBaseUrl(
                    ['_type' => \Magento\Framework\UrlInterface::URL_TYPE_MEDIA]
                ) . $this->mediaConfig->getBaseTmpMediaPath() . $baseImage;
            } else {
                $imagePath = $this->_urlBuilder->getBaseUrl(
                    ['_type' => \Magento\Framework\UrlInterface::URL_TYPE_MEDIA]
                ) . $this->mediaConfig->getBaseMediaPath() . $baseImage;
            }
            return $imagePath;
        }
        return $this->getImageHelper()->getDefaultPlaceholderUrl('thumbnail');
    }

    /**
     * Create temporary images
     *
     * @param string $file
     * @return string
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function createTmpImageIfNotExists($file)
    {
        $file = $this->getFilenameFromTmp($file);
        $viewDir = $this->moduleReader->getModuleDir(
            \Magento\Framework\Module\Dir::MODULE_VIEW_DIR,
            'Magedelight_Catalog'
        );
        $destinationFile = \Magento\MediaStorage\Model\File\Uploader::getNewFileName($file);
        $isCopied = $this->rootDirectory->copyFile(
            $viewDir . '/base/web/images/product/placeholder/image.jpg',
            $this->mediaDirectory->getAbsolutePath($this->mediaConfig->getTmpMediaPath($file))
        );
    }

    /**
     * @return \Magento\Catalog\Helper\Image
     * @deprecated
     */
    protected function getImageHelper()
    {
        if ($this->imageHelper === null) {
            $this->imageHelper = \Magento\Framework\App\ObjectManager::getInstance()
                ->get(\Magento\Catalog\Helper\Image::class);
        }
        return $this->imageHelper;
    }

    /**
     *
     * @param type $file
     * @return type
     */
    protected function getFilenameFromTmp($file = '')
    {
        return strrpos($file, '.tmp') == strlen($file) - 4 ?
            substr($file, 0, strlen($file) - 4) : $file;
    }

    /**
     *
     * @param string $productId
     * @return \Magento\Catalog\Model\Product
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getCoreProduct($productId = '')
    {
        if ($productId) {
            $product = $this->productRepository->getById($productId);
            return $product;
        }
    }

    /**
     *
     * @return boolean
     */
    public function isStoreCreditEnabled()
    {
        return $this->isModuleOutputEnabled('RB_StoreCredit');
    }

    /**
     *
     * @return null|string
     */
    public function getStoreCreditProduct()
    {
        if ($this->isStoreCreditEnabled()) {
            return \RB\StoreCredit\Model\Config::TOPUP_PRODUCT_SKU;
        }
        return null;
    }

    /**
     * @param type $value
     * @return type
     */
    public function getConditionOptionText($value)
    {
        return $this->productCondition->getOptionText($value);
    }

    /**
     * @return string
     */
    public function getProductImageStyleGuide()
    {
        $html = '<h3><u>' . __('Product images style guideline') . '</u></h3>
        <p>' . __('Listings that are missing a main image will not appear in search or browse until you fix the listing.') . '</p>
        <p>' . __('Choose images that are clear, information-rich, and attractive.') . '</p>
        <p><strong>' . __('Images must meet the following requirements') . ':</strong></p>
        <ul>
          <li>' . __('Images must be of following dimensions %1.', '<b>' . $this->getImageWidth() . 'px x ' . $this->getImageHeight() . 'px</b>') . '</li>
          <li>' . __('Maximum allowed size %1.', '<b>' . $this->getImageSize() . 'KB</b>') . '</li>
          <li>' . __('Products must fill at least 85% of the image. Images must show only the product that is for sale, with few or no props and with no logos, watermarks, or inset images. Images may only contain text that is a part of the product.') . '</li>
          <li>' . __('Main images must have a pure white background, must be a photo (not a drawing), and must not contain excluded accessories.') . '</li>
          <li>' . __('JPEG is the preferred image format, JPG, JPEG and PNG are the acceptable image types') . '</li>
        </ul>';
        return $html;
    }

    /**
     *
     * @return string
     */
    public function isMultiWebsiteOptionEnabled()
    {
        return $this->scopeConfig->getValue(
            \Magedelight\Catalog\Model\Product::M_W_ENABLE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     *
     * @return boolean
     */
    public function isEnabled()
    {
        return true;
//        return $this->backendHelper->isEnabled();
    }

    /**
     * @param $rating
     * @return float|int
     */
    public function getRating($rating)
    {
        $totalRating = number_format($rating, 2, '.', '');
        return ($totalRating * 100)/5;
    }

    /**
     *
     * @return array
     */
    public function getAllowedExtensions()
    {
        return ['jpg', 'jpeg', 'gif', 'png'];
    }


    public function getCustomProductData($vendorId, $productId)
    {
        $vendorProduct = $this->_vendorProductFactory->getSpecialPriceOnRequest($vendorId, $productId);

        return $vendorProduct;
    }
}
