<?php

namespace Ktpl\Quickview\Plugin;

class BlockProductView
{
    const XML_PATH_QUICKVIEW_REMOVE_QTY = 'ktpl_quickview/general/display_qty_selector';
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var \Magento\Catalog\Model\ProductTypes\ConfigInterface
     */
    private $productTypeConfig;

    /**
     *
     * @var  \Magento\Framework\App\Request\Http
     */
    private $request;

    /**
     * BlockProductView constructor.
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Catalog\Model\ProductTypes\ConfigInterface $productTypeConfig
     * @param \Magento\Framework\App\Request\Http $request
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Catalog\Model\ProductTypes\ConfigInterface $productTypeConfig,
        \Magento\Framework\App\Request\Http $request
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->productTypeConfig = $productTypeConfig;
        $this->request = $request;
    }

   /**
    *
    * @param \Magento\Catalog\Block\Product\View $subject
    * @param bool $result
    * @return bool
    */
    public function afterShouldRenderQuantity(
        \Magento\Catalog\Block\Product\View $subject,
        $result
    ) {
        $productType = $subject->getProduct()->getTypeId();
        $isProductSet = $this->productTypeConfig->isProductSet($productType);

        if (!$isProductSet && $this->request->getFullActionName() == 'ktpl_quickview_catalog_product_view') {
            $removeQtySelector = $this->scopeConfig->getValue(
                self::XML_PATH_QUICKVIEW_REMOVE_QTY,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
            return $removeQtySelector;
        }

        return $result;
    }
}
