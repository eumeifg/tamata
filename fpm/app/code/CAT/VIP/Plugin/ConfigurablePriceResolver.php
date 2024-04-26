<?php

namespace CAT\VIP\Plugin;

use Magento\ConfigurableProduct\Pricing\Price\FinalPriceResolver;
use Magento\Framework\App\ObjectManager;

class ConfigurablePriceResolver 
{

    /**
     * @var FinalPriceResolver
     */
    protected $priceResolver;

    /**
     * @var Magedelight\Catalog\Model\Product
     */
    protected $vendorProduct;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * @var \Magedelight\Catalog\Helper\Pricing\Data
     */
    protected $pricingHelper;
    protected $VIPhelper;

    /**
     * @param FinalPriceResolver $priceResolver
     * @param \Magedelight\Catalog\Model\Product $vendorProduct
     * @param \Magento\Framework\App\Request\Http $request
     * @param \Magedelight\Catalog\Helper\Pricing\Data $pricingHelper
     */
    public function __construct(
        FinalPriceResolver $priceResolver,
        \Magedelight\Catalog\Model\Product $vendorProduct,
        \Magento\Framework\App\Request\Http $request,
        \Magedelight\Catalog\Helper\Pricing\Data $pricingHelper,
        \CAT\VIP\Helper\Data $Viphelper
    ) {
        $this->vendorProduct = $vendorProduct;
        $this->VIPhelper = $Viphelper;
        $this->priceResolver = $priceResolver ?:
            ObjectManager::getInstance()->get(FinalPriceResolver::class);
        $this->request = $request;
        $this->pricingHelper = $pricingHelper;
    }

    /**
     * Loads configurable product price on every page load in listing and detail page.
     *
     * @param \Magento\ConfigurableProduct\Pricing\Price\ConfigurablePriceResolver $subject
     * @param \Closure $proceed
     * @param \Magento\Framework\Pricing\SaleableInterface $product
     * @return float|null
     */
    public function aroundResolvePrice(
        \Magento\ConfigurableProduct\Pricing\Price\ConfigurablePriceResolver $subject,
        \Closure $proceed,
        \Magento\Framework\Pricing\SaleableInterface $product
    ) {
        $price = $proceed($product);
        $prices = [];
        $price = null;
        foreach ($product->getTypeInstance()->getUsedProducts($product) as $subProduct) {
            $defaultVendorProduct = $this->vendorProduct->getProductDefaultVendor(
                null,
                $subProduct->getId(),
                true
            );

            $productPrice = $this->pricingHelper->currency(
                $this->getProcessedPrice($defaultVendorProduct),
                false,
                false,
                true
            );
            if (!$productPrice) {
                if (!$this->request->getParam('vid')) {
                    /* For vendor's microsite listing, avoid taking price of core product. */
                    $productPrice = $this->priceResolver->resolvePrice($subProduct);
                }
            }
            if ($this->request->getParam('vid')) {
                /* For vendor's microsite listing. */
                if ($productPrice) {
                    $prices[$this->request->getParam('vid')][] = $productPrice;
                }
            } else {
                $price = $price ? min($price, $productPrice) : $productPrice;
            }
        }
        /* For vendor's microsite listing starts. */
        if (!empty($prices)) {
            $price = min($prices[$this->request->getParam('vid')]);
        }
        /* For vendor's microsite listing ends. */
        return $price === null ? null : (float) $price;
    }

    /**
     * @param $vendorProduct
     * @return null|(float)
     */
    public function getProcessedPrice($vendorProduct)
    {   
        $currentDate= date('Y-m-d', strtotime(date('Y-m-d')));
        $fromDate = $vendorProduct->getData('special_from_date');
        $toDate = $vendorProduct->getData('special_to_date');
        $price = $vendorProduct->getData('price');
        $specialPrice = $vendorProduct->getData('special_price');
        if (empty($fromDate) && empty($toDate)) {

            /* Return special price if set without date(s). */
            $specialPrice = ($specialPrice) ? $specialPrice : null;
        }

        if (empty($fromDate) && !empty($toDate)) {
            $fromDate = date('Y-m-d');
        } elseif (!empty($fromDate) && empty($toDate)) {
            $toDate = $fromDate;
        }

        // If current date is between from special date.
        if ($currentDate >= date('Y-m-d', strtotime($fromDate))
            && $currentDate <= date('Y-m-d', strtotime($toDate))) {
            $specialPrice = ($specialPrice) ? $specialPrice : null;
        }
        if($specialPrice && $specialPrice < $price){
            $finalPrice = $specialPrice;
        }else{
            $finalPrice = $price;
        }

        return $finalPrice;
        // Check VIP Price
        //return $this->VIPhelper->getvipProductDiscountPrice($vendorProduct->getData('marketplace_product_id'),$finalPrice,$vendorProduct->getData('vendor_id'));

    }
}
