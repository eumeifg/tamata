<?php


namespace CAT\VIP\Helper;

use Magento\Catalog\Model\Product\Type;
use Magento\CatalogInventory\Api\StockStatusRepositoryInterface;
use Magento\CatalogInventory\Model\Spi\StockRegistryProviderInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\MediaStorage\Model\File\Uploader;
use Magento\Search\Model\QueryFactory;
use Magento\Store\Model\ScopeInterface;

class UpdatesData extends \Magedelight\Catalog\Helper\Data
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
    protected $viphelper;

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
        \Magento\Framework\Escaper $escaper,
        \CAT\VIP\Helper\data $viphelper
    ) {
        $this->viphelper = $viphelper;
        parent::__construct(
        $vendorProductFactory,
        $priceHelper,
        $coreRegistry,
        $product,
        $stockRegistry,
        $stockStateInterface,
        $dataObjectHelper,
        $stockItemRepository,
        $productRepository,
        $urlContext,
        $taxCalculation,
        $stockRegistryProvider,
        $stockStatusRepository,
        $customerSession,
        $filesystem,
        $mediaConfig,
        $moduleReader,
        $jsonDecoder,
        $productCondition,
        $sellerUrl,
        $backendHelper,
        $escaper,
        );
    }

    public function getVendorFinalPrice($vendorId, $productId, $convert = true, $discount = true,$qty = 0,$customer = 0)
    {
        $rulePrice = $this->getCatalogRulePrice($productId, $vendorId);
       $checkPrice =  $this->getVendorSpecialPrice($vendorId, $productId);
        if ($checkPrice !== null) {
            $price = $checkPrice;
        } else {
            $price = $this->getVendorPrice($vendorId, $productId);
        }
        $price = ($rulePrice && $rulePrice < $price) ? $rulePrice : $price;
        if($discount){
            // Check Vip Price
            $price = $this->viphelper->applyvipProductDiscountPrice($productId,$price,$vendorId,$qty,$customer);
        }
        return ($convert) ? $this->_priceHelper->currency($price, false, true) : $price;
    }

}
