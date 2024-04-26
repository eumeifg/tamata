<?php

namespace CAT\VIP\Block\Product\View\Type\Renderer;

use Magento\CatalogRule\Pricing\Price\CatalogRulePrice;

class Configurable extends \MDC\Microsite\Block\Product\View\Type\Renderer\Configurable
{

    const SWATCH_RENDERER_TEMPLATE = 'Magedelight_ConfigurableProduct::product/view/renderer.phtml';

     /**
     * @var \Magedelight\Catalog\Helper\Data
     */
    protected $catalogHelper;

    /**
     * @var ConfigurableAttributeData
     */
    protected $configurableAttributeData;

    /**
     * @var Microsite
     */
    protected $microsite;
    
    /**
     * @var \Magedelight\Catalog\Model\Source\Condition
     */
    protected $productCondition;
    
    /**
     * @var \Magento\Framework\Locale\Format
     */
    protected $localeFormat;

    /**
     * @var \Magedelight\Vendor\Helper\Microsite\Data
     */
    protected $_micrositeHelper;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $_urlBuilder;

    protected $_vipHelper;

    function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Framework\Stdlib\ArrayUtils $arrayUtils,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\ConfigurableProduct\Helper\Data $helper,
        \Magento\Catalog\Helper\Product $catalogProduct,
        \Magento\Customer\Helper\Session\CurrentCustomer $currentCustomer,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        \Magedelight\Catalog\Model\ConfigurableAttributeData $configurableAttributeData,
        \Magento\Swatches\Helper\Data $swatchHelper,
        \Magento\Swatches\Helper\Media $swatchMediaHelper,
        \Magedelight\Catalog\Helper\Data $catalogHelper,
        \Magedelight\Catalog\Model\Source\Condition $productCondition,
        array $data = [],
        \Magento\Framework\Locale\Format $localeFormat,
        \Magedelight\Vendor\Model\Microsite $microsite,
        \Magento\Swatches\Model\SwatchAttributesProvider $swatchAttributesProvider = null,
        \Magedelight\Vendor\Helper\Microsite\Data $micrositeHelper,
        \CAT\VIP\Helper\Data $vipHelper
    )
    {
        parent::__construct(
            $context,
            $arrayUtils,
            $jsonEncoder,
            $helper,
            $catalogProduct,
            $currentCustomer,
            $priceCurrency,
            $configurableAttributeData,
            $swatchHelper,
            $swatchMediaHelper,
            $catalogHelper,
            $productCondition,
            $data,
            $localeFormat,
            $microsite,
            $swatchAttributesProvider,
            $micrositeHelper
        );
        $this->configurableAttributeData = $configurableAttributeData;
        $this->catalogHelper = $catalogHelper;
        $this->microsite = $microsite;
        $this->productCondition = $productCondition;
        $this->localeFormat = $localeFormat;
        $this->_micrositeHelper = $micrositeHelper;
        $this->_urlBuilder = $context->getUrlBuilder();
        $this->_vipHelper = $vipHelper;

   
    }

    public function getJsonConfig()
    {
        parent::getJsonConfig();
        $store = $this->getCurrentStore();
        $currentProduct = $this->getProduct();
        $regularPrice = $currentProduct->getPriceInfo()->getPrice('regular_price');
        $finalPrice = $currentProduct->getPriceInfo()->getPrice('final_price');

        $options = $this->helper->getOptions($currentProduct, $this->getAllowProducts());
        $vendorOptions = $this->catalogHelper->getOptions($currentProduct, $this->getAllowProducts());

        // Featch VIP
        foreach($vendorOptions['default_vendor'] as $productID => $vendorinfo){
            $sellprice = $this->catalogHelper->getVendorFinalPrice($vendorinfo['vendorId'],$productID,true,false);
            $vendorinfo['vipdiscount'] = $this->_vipHelper->getvipProductDiscountPrice($productID,$sellprice,$vendorinfo['vendorId'],true);
            $vendorinfo['vipfinalprice'] = $this->_vipHelper->applyvipProductDiscountPrice($productID,$sellprice,$vendorinfo['vendorId']);
            $vendorinfo['vipoff'] = ($vendorinfo['vipdiscount']) ? round(($sellprice - $vendorinfo['vipdiscount']) * 100 / $sellprice) : false;
            $vendorOptions['default_vendor'][$productID] = $vendorinfo;
        }
        $attributesData = $this->configurableAttributeData->getAttributesData($currentProduct, $options);
       
        $defaultVendorProduct = $this->catalogHelper->getVendorDefaultProduct($currentProduct->getId(), false, false);
        $preDefaultVendor = [];

        if ($defaultVendorProduct) {
            $preDefaultVendor = [
                'vendorId' => $defaultVendorProduct->getVendorId(),
                'vendorName' => $defaultVendorProduct->getBusinessName(),
                'condition' => $this->getConditionOptionText($defaultVendorProduct->getCondition()),
                'conditionNote' => $defaultVendorProduct->getConditionNote(),
                'vendorRatings' => $this->catalogHelper->getDefaultVendorRatingHtml(
                    $defaultVendorProduct->getMarketplaceProductId(),
                    $defaultVendorProduct->getVendorId()
                ),
                'noOfVendors' => '',
                'productId' => $defaultVendorProduct->getMarketplaceProductId(),
                'price' => $this->currency($defaultVendorProduct->getPrice()),
                'no_microsite_products_url' => $this->_urlBuilder->getUrl('microsite/products/vendor/vid/'.$defaultVendorProduct->getVendorId() )
                ];
        }
        
        $defaultVendorProductIdForMicrosite = isset($preDefaultVendor) ? $preDefaultVendor['vendorId'] : "";
        $config = [
            'attributes' => $attributesData['attributes'],
            'template' => str_replace('%s', '<%- data.price %>', $store->getCurrentCurrency()->getOutputFormat()),
            'currencyFormat' => $store->getCurrentCurrency()->getOutputFormat(),
            'optionPrices' => $this->getOptionPrices(),
            'priceFormat' => $this->localeFormat->getPriceFormat(),
            'prices' => [
                'oldPrice' => [
                        'amount' => $this->_registerJsPrice(($defaultVendorProduct) ?
                            $this->currency($defaultVendorProduct->getPrice()):
                            $this->currency($regularPrice->getAmount()->getValue())),
                ],
                'basePrice' => [
                    'amount' => $this->_registerJsPrice(
                        ($defaultVendorProduct) ?
                            $this->currency($finalPrice->getAmount()->getBaseAmount()) :
                            $this->currency($finalPrice->getAmount()->getBaseAmount())
                    ),
                ],
                'finalPrice' => [
                        'amount' => $this->_registerJsPrice(($defaultVendorProduct) ?
                            $this->currency($defaultVendorProduct->getFinalPrice()) :
                            $this->currency($finalPrice->getAmount()->getValue())),
                ],
            ],
            'productId' => $currentProduct->getId(),
            'chooseText' => __('Choose an Option...'),
            'images' => isset($options['images']) ? $options['images'] : [],
            'index' => isset($options['index']) ? $options['index'] : [],
            'defaultVendor' => isset($vendorOptions['default_vendor']) ? $vendorOptions['default_vendor'] : [],
            'vendorUrl' => $this->getMUrl($defaultVendorProduct),
            'preDefaultVendor' => ($preDefaultVendor) ? : '',
            'no_microsite_products_url' => $this->_urlBuilder->getUrl('microsite/products/vendor/vid/'.$defaultVendorProductIdForMicrosite)

        ];

        if ($currentProduct->hasPreconfiguredValues() && !empty($attributesData['defaultValues'])) {
            $config['defaultValues'] = $attributesData['defaultValues'];
        }

        $config = array_merge($config, $this->_getAdditionalConfig());

        return $this->jsonEncoder->encode($config);
    }
    
     
}