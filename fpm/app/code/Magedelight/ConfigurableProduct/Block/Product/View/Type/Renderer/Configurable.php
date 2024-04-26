<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_ConfigurableProduct
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\ConfigurableProduct\Block\Product\View\Type\Renderer;

use Magento\CatalogRule\Pricing\Price\CatalogRulePrice;

class Configurable extends \Magento\Swatches\Block\Product\Renderer\Configurable
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
     *
     * @param \Magento\Catalog\Block\Product\Context $context
     * @param \Magento\Framework\Stdlib\ArrayUtils $arrayUtils
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param \Magento\ConfigurableProduct\Helper\Data $helper
     * @param \Magento\Catalog\Helper\Product $catalogProduct
     * @param \Magento\Customer\Helper\Session\CurrentCustomer $currentCustomer
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
     * @param \Magedelight\Catalog\Model\ConfigurableAttributeData $configurableAttributeData
     * @param \Magento\Swatches\Helper\Data $swatchHelper
     * @param \Magento\Swatches\Helper\Media $swatchMediaHelper
     * @param \Magedelight\Catalog\Helper\Data $catalogHelper
     * @param array $data
     * @param \Magento\Swatches\Model\SwatchAttributesProvider $swatchAttributesProvider
     * @param \Magedelight\Vendor\Model\Microsite
     * @param \Magedelight\Vendor\Helper\Microsite\Data $micrositeHelper
     */
    public function __construct(
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
        \Magedelight\Vendor\Helper\Microsite\Data $micrositeHelper
    ) {
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
            $data,
            $swatchAttributesProvider
        );
        
        $this->configurableAttributeData = $configurableAttributeData;
        $this->catalogHelper = $catalogHelper;
        $this->microsite = $microsite;
        $this->productCondition = $productCondition;
        $this->localeFormat = $localeFormat;
        $this->_micrositeHelper = $micrositeHelper;
    }

    /**
     * Composes configuration for js
     *
     * @return string
     */
    public function getJsonConfig()
    {
        parent::getJsonConfig();
        $store = $this->getCurrentStore();
        $currentProduct = $this->getProduct();
        $regularPrice = $currentProduct->getPriceInfo()->getPrice('regular_price');
        $finalPrice = $currentProduct->getPriceInfo()->getPrice('final_price');

        $options = $this->helper->getOptions($currentProduct, $this->getAllowProducts());
        $vendorOptions = $this->catalogHelper->getOptions($currentProduct, $this->getAllowProducts());
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
                ];
        }
        
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
            'preDefaultVendor' => ($preDefaultVendor) ? : ''

        ];

        if ($currentProduct->hasPreconfiguredValues() && !empty($attributesData['defaultValues'])) {
            $config['defaultValues'] = $attributesData['defaultValues'];
        }

        $config = array_merge($config, $this->_getAdditionalConfig());

        return $this->jsonEncoder->encode($config);
    }

    /**
     * Convert and format price value for current application store
     *
     * @param   float $value
     * @return  float|string
     */
    protected function currency($value = null)
    {
        return $this->catalogHelper->currency(
            $value,
            false,
            false,
            true
        );
    }

    /**
     * @return return microsite url
     */
    public function getMUrl($defaultVendorProduct)
    {
        if ($defaultVendorProduct) {
            return $this->_micrositeHelper->getVendorMicrositeUrl($defaultVendorProduct->getVendorId());
        }
    }

    /**
     * @return array
     */
    public function getOptionPrices()
    {
        $prices = [];
        foreach ($this->getAllowProducts() as $product) {
            $tierPrices = [];
            $priceInfo = $product->getPriceInfo();
            $tierPriceModel =  $priceInfo->getPrice('tier_price');
            $tierPricesList = $tierPriceModel->getTierPriceList();
            foreach ($tierPricesList as $tierPrice) {
                $tierPrices[] = [
                    'qty' => $this->localeFormat->getNumber($tierPrice['price_qty']),
                    'price' => $this->localeFormat->getNumber($tierPrice['price']->getValue()),
                    'percentage' => $this->localeFormat->getNumber(
                        $tierPriceModel->getSavePercent($tierPrice['price'])
                    ),
                ];
            }
            $defaultVendorProduct = $this->catalogHelper->getProductDefaultVendor($product->getId());
            if ($defaultVendorProduct) {
                $product->setVendorId($defaultVendorProduct->getVendorId());
                $productBasePrice = $this->catalogHelper->getVendorProductPrice(
                    $product,
                    $defaultVendorProduct,
                    \Magedelight\Catalog\Helper\Data::PRICE_TYPE_FINAL
                );
                $rulePrice = false;
                if ($product->hasData(CatalogRulePrice::PRICE_CODE)) {
                    $rulePrice = (float)$product->getData(CatalogRulePrice::PRICE_CODE);
                }
                $prices[$product->getId()] =
                [
                    'oldPrice' => [
                        'amount' =>
                            ($defaultVendorProduct) ?
                            $this->currency($defaultVendorProduct->getPrice()) :
                            $this->currency($priceInfo->getPrice('regular_price')->getAmount()->getValue())
                    ],
                    'basePrice' => [
                        'amount' =>
                            ($defaultVendorProduct) ?
                            $this->currency($productBasePrice) :
                            $this->currency($priceInfo->getPrice('final_price')->getAmount()->getBaseAmount())
                    ],
                    'finalPrice' => [
                        'amount' =>
                            (!$rulePrice) ?
                            $this->currency($defaultVendorProduct->getFinalPrice()) :
                            $this->currency($priceInfo->getPrice('final_price')->getAmount()->getValue())
                    ],
                    'tierPrices' => $tierPrices,
                    'msrpPrice' => [
                        'amount' => $this->localeFormat->getNumber(
                            $product->getMsrp()
                        ),
                    ],
                ];
            }
        }
        return $prices;
    }

    /**
     * @codeCoverageIgnore
     * @return string
     */
    protected function getRendererTemplate()
    {
        return $this->isProductHasSwatchAttribute() ?
            self::SWATCH_RENDERER_TEMPLATE : self::CONFIGURABLE_RENDERER_TEMPLATE;
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
     * Get block cache life time
     *
     * @return int
     */
    protected function getCacheLifetime()
    {
        return 0;
    }
}
