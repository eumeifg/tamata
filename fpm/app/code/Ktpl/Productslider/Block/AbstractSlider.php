<?php
/**
 * Ktpl
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the KrishTechnolabs.com license that is
 * available through the world-wide-web at this URL:
 * https://https://www.KrishTechnolabs.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Ktpl
 * @package     Ktpl_Productslider
 * @copyright   Copyright (c) Ktpl (https://https://www.KrishTechnolabs.com//)
 * @license     https://https://www.KrishTechnolabs.com/LICENSE.txt
 */

namespace Ktpl\Productslider\Block;

use Exception;
use Magento\Catalog\Block\Product\AbstractProduct;
use Magento\Catalog\Block\Product\Context;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\App\Http\Context as HttpContext;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Ktpl\Productslider\Helper\Data;
use Ktpl\Productslider\Model\Config\Source\Additional;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Catalog\Pricing\Price\FinalPrice;
use Magento\Framework\Pricing\Render;

/**
 * Class AbstractSlider
 * @package Ktpl\Productslider\Block
 */
abstract class AbstractSlider extends AbstractProduct
{
    /**
     * @var DateTime
     */
    protected $_date;

    /**
     * @var Data
     */
    protected $_helperData;

    /**
     * @var CollectionFactory
     */
    protected $_productCollectionFactory;

    /**
     * @var Visibility
     */
    protected $_catalogProductVisibility;

    /**
     * @var HttpContext
     */
    protected $httpContext;

    /**
     * @var UrlHelper
     */
    protected $urlHelper;
    
    /**
     * @var PriceCurrencyInterface
     */
    protected $priceCurrency;

    /**
     * AbstractSlider constructor.
     * @param Context $context
     * @param CollectionFactory $productCollectionFactory
     * @param Visibility $catalogProductVisibility
     * @param DateTime $dateTime
     * @param Data $helperData
     * @param HttpContext $httpContext
     * @param array $data
     */
    public function __construct(
        Context $context,
        CollectionFactory $productCollectionFactory,
        Visibility $catalogProductVisibility,
        DateTime $dateTime,
        Data $helperData,
        HttpContext $httpContext,
        array $data = []
    ) {
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->_catalogProductVisibility = $catalogProductVisibility;
        $this->_date = $dateTime;
        $this->_helperData = $helperData;
        $this->httpContext = $httpContext;
        $this->urlHelper = \Magento\Framework\App\ObjectManager::getInstance()
        ->get(\Magento\Framework\Url\Helper\Data::class);
        parent::__construct($context, $data);
    }

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        parent::_construct();

        $this->addData([
            'cache_lifetime' => $this->getSlider() ? $this->getSlider()->getTimeCache() : 86400,
            'cache_tags'     => [Product::CACHE_TAG]
        ]);

        $this->setTemplate('Ktpl_Productslider::productslider.phtml');
    }

    /**
     * @return mixed
     */
    abstract public function getProductCollection();

    /**
     * Get Key pieces for caching block content
     *
     * @return array
     * @throws NoSuchEntityException
     */
    public function getCacheKeyInfo()
    {
        return [
            'KTPL_PRODUCT_SLIDER',
            $this->getPriceCurrency()->getCurrency()->getCode(),
            $this->_storeManager->getStore()->getId(),
            $this->httpContext->getValue(\Magento\Customer\Model\Context::CONTEXT_GROUP),
            $this->getSliderId()
        ];
    }

    /**
     * @return array|mixed
     */
    public function getDisplayAdditional()
    {
        if ($this->getSlider()) {
            $display = $this->getSlider()->getDisplayAdditional();
        } else {
            $display = [];
        }

        if (!is_array($display)) {
            $display = explode(',', $display);
        }

        return $display;
    }

    /**
     * @return bool
     */
    public function canShowPrice()
    {
        return in_array(Additional::SHOW_PRICE, $this->getDisplayAdditional(), true);
    }

    /**
     * @return bool
     */
    public function canShowReview()
    {
        return in_array(Additional::SHOW_REVIEW, $this->getDisplayAdditional(), true);
    }

    /**
     * @return bool
     */
    public function canShowAddToCart()
    {
        return in_array(Additional::SHOW_CART, $this->getDisplayAdditional(), true);
    }

    /**
     * @return bool
     */
    public function canShowWishlist()
    {
        return in_array(Additional::SHOW_WISHLIST, $this->getDisplayAdditional(), true);
    }

    /**
     * @return bool
     */
    public function canShowCompare()
    {
        return in_array(Additional::SHOW_COMPARE, $this->getDisplayAdditional(), true);
    }

    /**
     * @return bool
     */
    public function canShowSwatch()
    {
        return in_array(Additional::SHOW_SWATCH, $this->getDisplayAdditional(), true);
    }

    /**
     * Get Slider Id
     * @return string
     */
    public function getSliderId()
    {
        if ($this->getSlider()) {
            return $this->getSlider()->getSliderId();
        }

        return time();
    }

    /**
     * Get Slider Title
     *
     * @return mixed|string
     */
    public function getTitle()
    {
        if ($title = $this->hasData('title')) {
            return $this->getData('title');
        }

        if ($this->getSlider()) {
            return $this->getSlider()->getTitle();
        }

        return '';
    }

    /**
     * Get Slider Description
     *
     * @return mixed|string
     */
    public function getDescription()
    {
        if ($this->hasData('description')) {
            return $this->getData('description');
        }

        if ($this->getSlider()) {
            return $this->getSlider()->getDescription();
        }

        return '';
    }

    /**
     * @return string
     */
    public function getAllOptions($productCount=null)
    {
        $sliderOptions = '';
        $allConfig = $this->_helperData->getModuleConfig('slider_design');
        $sliderData = $this->getSlider()->getData();
        foreach ($sliderData as $key => & $value) {
            if ($key === 'responsive_items') {
                if (empty($this->getSlider())) {
                    $sliderOptions .= $this->_helperData->getResponseValue()?
                    $this->_helperData->getResponseValue(). ',':'';
                } else {
                    $sliderOptions .= $this->getResponsiveConfig() ? $this->getResponsiveConfig(). ',' : '';
                }
            } elseif ($key !== 'is_responsive') {
                if (in_array($key, ['loop', 'nav', 'dots', 'lazyLoad', 'autoplay', 'autoplayHoverPause'])) {
                    $value = $value ? 'true' : 'false';

                    if ($value) {
                        $sliderOptions = $sliderOptions . $key . ':' . $value . ',';

                        if ($key == 'nav') {
                            $sliderOptions.='navText: [
                                "<span class=\'left-arrow\'></span>",
                                "<span class=\'right-arrow\'></span>"
                            ],';
                        }
                    }
                } elseif (in_array($key, [ 'margin', 'autoplayTimeout'])) {
                    if ($value) {
                        $sliderOptions = $sliderOptions . $key . ':' . $value . ',';
                    }
                }
            }
            
            if ($productCount <= 4 && $key ==='loop' ) {
                $value = 'false';
                $sliderOptions = $sliderOptions . $key . ':' . $value . ',';
            }
        }
        /*...Check for local and add rtl:true if store view is arabic...*/
        $currentLocal = $this->_helperData->getCurrentLocale();
        if ($currentLocal == "ar") {
            $sliderOptions = $sliderOptions . 'rtl:true,';
        }
        /*...End check for local and add rtl:true if store view is arabic...*/
        $sliderOptions = rtrim($sliderOptions, ',');
        return '{' . $sliderOptions . '}';
    }

    /**
     * @return string
     */
    public function getResponsiveConfig()
    {
        $slider = $this->getSlider();
        if ($slider && $slider->getIsResponsive()) {
            try {
                if ($slider->getIsResponsive() === '2') {
                    return $this->_helperData->getResponseValue();
                } else {
                    $responsiveConfig = $slider->getResponsiveItems() ?
                    $this->_helperData->unserialize($slider->getResponsiveItems()) : [];
                }
            } catch (Exception $e) {
                $responsiveConfig = [];
            }

            $responsiveOptions = '';
            foreach ($responsiveConfig as $config) {
                if ($config['size'] && $config['items']) {
                    if ($config['size'] <= 800) {
                        $responsiveOptions = $responsiveOptions . $config['size'] . ':{items:' . $config['items'].',autoWidth:true'. '},';
                    } else {
                        $responsiveOptions = $responsiveOptions . $config['size'] . ':{items:' . $config['items'] . '},';
                    }
                }
            }
            $responsiveOptions = rtrim($responsiveOptions, ',');

            return 'responsive:{' . $responsiveOptions . '}';
        }

        return '';
    }

    /**
     * Get End of Day Date
     *
     * @return string
     */
    public function getEndOfDayDate()
    {
        return $this->_date->date(null, '23:59:59');
    }

    /**
     * Get Start of Day Date
     *
     * @return string
     */
    public function getStartOfDayDate()
    {
        return $this->_date->date(null, '0:0:0');
    }

    /**
     * Get Store Id
     *
     * @return int
     */
    public function getStoreId()
    {
        return $this->_storeManager->getStore()->getId();
    }

    /**
     * Get Product Count is displayed
     *
     * @return mixed
     */
    public function getProductsCount()
    {
        if ($this->hasData('products_count')) {
            return $this->getData('products_count');
        }

        if ($this->getSlider()) {
            return $this->getSlider()->getLimitNumber() ?: 5;
        }

        return 5;
    }

    /**
     * Get post parameters
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return string
     */
    public function getAddToCartPostParams(\Magento\Catalog\Model\Product $product)
    {
        $url = $this->getAddToCartUrl($product);
        return [
            'action' => $url,
            'data' => [
                'product' => $product->getEntityId(),
                \Magento\Framework\App\ActionInterface::PARAM_NAME_URL_ENCODED =>
                    $this->urlHelper->getEncodedUrl($url),
            ]
        ];
    }
    
    /**
     * Get currency of product
     *
     * @return PriceCurrencyInterface
     * @deprecated 100.2.0
     */
    protected function getPriceCurrency()
    {
        if ($this->priceCurrency === null) {
            $this->priceCurrency = \Magento\Framework\App\ObjectManager::getInstance()
                ->get(PriceCurrencyInterface::class);
        }
        return $this->priceCurrency;
    }

    /**
     * Get product price.
     *
     * @param Product $product
     * @return string
     */
    public function getProductPrice(Product $product)
    {
        $priceRender = $this->getPriceRender();

        $price = '';
        if ($priceRender) {
            $price = $priceRender->render(
                FinalPrice::PRICE_CODE,
                $product,
                [
                    'include_container' => true,
                    'display_minimal_price' => true,
                    'zone' => Render::ZONE_ITEM_LIST,
                    'list_category_page' => true
                ]
            );
        }

        return $price;
    }

    /**
     * Specifies that price rendering should be done for the list of products.
     * (rendering happens in the scope of product list, but not single product)
     *
     * @return Render
     */
    protected function getPriceRender()
    {
        return $this->getLayout()->getBlock('product.price.render.default')
            ->setData('is_product_list', true);
    }


    /**
     * Get Slider view all link
     *
     * @return mixed|string
     */
    public function getSliderViewAllLink()
    {
        if ($title = $this->hasData('slider_view_all_link')) {
            return $this->getData('slider_view_all_link');
        }

        if ($this->getSlider()) {
            return $this->getSlider()->getSliderViewAllLink();
        }

        return '';
    }
}
