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
namespace Magedelight\Catalog\Plugin\Block\Product\View\Type;

class Configurable
{

    /**
     * @var \Magedelight\Catalog\Helper\Data
     */
    protected $_helper;

    /**
     * @var \Magento\Catalog\Helper\Product
     */
    protected $catalogProduct;

    /**
     * @var \Magedelight\Catalog\Model\ProductFactory
     */
    protected $vendorProductFactory;

    /**
     * @param \Magedelight\Catalog\Model\ProductFactory $vendorProductFactory
     * @param \Magento\Catalog\Helper\Product $catalogProduct
     * @param \Magedelight\Catalog\Helper\Data $helper
     */
    public function __construct(
        \Magedelight\Catalog\Model\ProductFactory $vendorProductFactory,
        \Magento\Catalog\Helper\Product $catalogProduct,
        \Magedelight\Catalog\Helper\Data $helper
    ) {
        $this->vendorProductFactory = $vendorProductFactory->create();
        $this->catalogProduct = $catalogProduct;
        $this->_helper = $helper;
    }

    /**
     *
     * @param \Magento\ConfigurableProduct\Block\Product\View\Type\Configurable $subject
     * @param type $result
     * @return type
     */
    public function afterGetAllowProducts(
        \Magento\ConfigurableProduct\Block\Product\View\Type\Configurable $subject,
        $result
    ) {
        if ($this->_helper->isEnabled()) {
            $products = [];
            $ids = [];
            $skipSaleableCheck = $this->catalogProduct->getSkipSaleableCheck();
            foreach ($result as $product) {
                if ($product->isSaleable() || $skipSaleableCheck) {
                    if ($this->vendorProductFactory->checkIsProductSalableFromVendor($product->getId())) {
                        $products[] = $product;
                        $ids[] = $product->getId();
                    }
                }
            }
            $result = $products;
        }
        return $result;
    }
}
