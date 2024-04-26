<?php

namespace Ktpl\Quickview\Helper;

/**
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{

    /**
     * @var array
     */
    private $quickViewConfigOptions;

    /**
     * Data constructor.
     * @param \Magento\Framework\App\Helper\Context $context
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context
    ) {
        parent::__construct($context);
        $this->quickViewConfigOptions = $this->scopeConfig->getValue(
            'ktpl_quickview',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return trim($this->quickViewConfigOptions['general']['enable_product_listing']);
    }

    /**
     * @return string
     */
    public function getSkuTemplate()
    {
        $displaySku = $this->quickViewConfigOptions['general']['display_sku'];
        if ($displaySku) {
            return 'Magento_Catalog::product/view/attribute.phtml';
        }

        return '';
    }

    /**
     * @return string
     */
    public function getCustomCSS()
    {
        return trim($this->quickViewConfigOptions['general']['custom_css']);
    }

    /**
     * @return int
     */
    public function getCloseSeconds()
    {
        return trim($this->quickViewConfigOptions['general']['close_quickview']);
    }

    /**
     * @return boolean
     */
    public function getScrollAndOpenMiniCart()
    {
        return $this->quickViewConfigOptions['general']['scroll_to_top'];
    }

    /**
     * @return boolean
     */
    public function getShoppingCheckoutButtons()
    {
        return $this->quickViewConfigOptions['general']['enable_shopping_checkout_product_buttons'];
    }

    /**
     * @return string
     */
    public function getZoomType()
    {
        $zoomEventType = false;
        if($this->quickViewConfigOptions['general']['enable_zoom']){
            $zoomEventType = $this->quickViewConfigOptions['general']['zoom_eventtype'];
        }
        return $zoomEventType;
    }
}
