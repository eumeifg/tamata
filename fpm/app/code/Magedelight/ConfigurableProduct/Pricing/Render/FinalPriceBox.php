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
namespace Magedelight\ConfigurableProduct\Pricing\Render;

use Magento\Catalog\Model\Product\Pricing\Renderer\SalableResolverInterface;
use Magento\Catalog\Pricing\Price\FinalPrice;
use Magento\Catalog\Pricing\Price\MinimalPriceCalculatorInterface;
use Magento\Catalog\Pricing\Price\RegularPrice;
use Magento\ConfigurableProduct\Pricing\Price\ConfigurableOptionsProviderInterface;
use Magento\ConfigurableProduct\Pricing\Price\LowestPriceOptionsProviderInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Pricing\Price\PriceInterface;
use Magento\Framework\Pricing\Render\RendererPool;
use Magento\Framework\Pricing\SaleableInterface;
use Magento\Framework\View\Element\Template\Context;

/**
 * Class FinalPrice
 */
class FinalPriceBox extends \Magento\ConfigurableProduct\Pricing\Render\FinalPriceBox
{
    /**
     * @var \Magedelight\Catalog\Helper\Data
     */
    private $rbHelper;

    /**
     * @var Magedelight\Catalog\Model\Product
     */
    private $vendorProduct;

    private $minPrice;

    private $minSpecialPrice;

    /**
     * @var \Magedelight\Catalog\Model\ResourceModel\Indexer\Vendor\DefaultVendorsFactory
     */
    protected $_defaultVendorIndexersFactory;

    protected $lowestPriceOptionsProvider;

    /**
     * FinalPriceBox constructor.
     * @param Context $context
     * @param SaleableInterface $saleableItem
     * @param PriceInterface $price
     * @param RendererPool $rendererPool
     * @param SalableResolverInterface $salableResolver
     * @param MinimalPriceCalculatorInterface $minimalPriceCalculator
     * @param ConfigurableOptionsProviderInterface $configurableOptionsProvider
     * @param \Magedelight\Catalog\Model\Product $vendorProduct
     * @param \Magedelight\Catalog\Model\ResourceModel\Indexer\Vendor\DefaultVendorsFactory $defaultVendorsFactory
     * @param array $data
     * @param LowestPriceOptionsProviderInterface|null $lowestPriceOptionsProvider
     */
    public function __construct(
        Context $context,
        SaleableInterface $saleableItem,
        PriceInterface $price,
        RendererPool $rendererPool,
        SalableResolverInterface $salableResolver,
        MinimalPriceCalculatorInterface $minimalPriceCalculator,
        ConfigurableOptionsProviderInterface $configurableOptionsProvider,
        \Magedelight\Catalog\Model\Product $vendorProduct,
        \Magedelight\Catalog\Model\ResourceModel\Indexer\Vendor\DefaultVendorsFactory $defaultVendorsFactory,
        array $data = [],
        LowestPriceOptionsProviderInterface $lowestPriceOptionsProvider = null
    ) {
        parent::__construct(
            $context,
            $saleableItem,
            $price,
            $rendererPool,
            $salableResolver,
            $minimalPriceCalculator,
            $configurableOptionsProvider
        );
        $this->lowestPriceOptionsProvider = $lowestPriceOptionsProvider ?:
            ObjectManager::getInstance()->get(LowestPriceOptionsProviderInterface::class);
        $this->vendorProduct = $vendorProduct;
        $this->_defaultVendorIndexersFactory = $defaultVendorsFactory->create();
    }

    /**
     *
     * @return boolean
     */
    public function hasSpecialPrice()
    {
        $product = $this->getSaleableItem();
        $minPrice = $this->_defaultVendorIndexersFactory->getConfigProductPriceByProductId($product->getId());
        $minSpecialPrice = $this->_defaultVendorIndexersFactory->getConfigProductSpecialPriceByProductId(
            $product->getId()
        );

        if (($minPrice && $minPrice > 0) && ($minSpecialPrice && $minSpecialPrice > 0)) {
            if ($minSpecialPrice < $minPrice) {
                $this->minSpecialPrice = $minSpecialPrice;
                $this->minPrice = $minPrice;
                return true;
            }
        }

        foreach ($this->lowestPriceOptionsProvider->getProducts($product) as $subProduct) {
            $defaultVendorProduct = $this->vendorProduct->getProductDefaultVendor(
                null ,
                $subProduct->getId(),
                true
            );
            $vendorRegularPrice = $defaultVendorProduct->getData('price');
            $vendorSpecialPrice = $defaultVendorProduct->getData('special_price');

            if (!$vendorSpecialPrice || $vendorSpecialPrice <= 0 || $vendorSpecialPrice >= $vendorRegularPrice) {
                    return false;
            }
            $regularPrice = ($vendorRegularPrice && $vendorRegularPrice > 0) ?
                $vendorRegularPrice : $subProduct->getPriceInfo()->getPrice(RegularPrice::PRICE_CODE)->getValue();
            $finalPrice = ($vendorSpecialPrice && $vendorSpecialPrice > 0) ?
                $vendorSpecialPrice : $subProduct->getPriceInfo()->getPrice(FinalPrice::PRICE_CODE)->getValue();
            if ($finalPrice < $regularPrice) {
                return true;
            }
        }
        return false;
    }
}
