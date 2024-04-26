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
namespace Magedelight\Catalog\Plugin;

class RegularPrice
{

    /**
     * @var Magedelight\Catalog\Model\Product
     */
    protected $vendorProduct;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * @var \Magedelight\Catalog\Helper\Data
     */
    protected $helper;

    /**
     * @param \Magedelight\Catalog\Model\ProductFactory $vendorProduct
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magedelight\Catalog\Helper\Data $helper
     */
    public function __construct(
        \Magedelight\Catalog\Model\ProductFactory $vendorProduct,
        \Magento\Framework\App\RequestInterface $request,
        \Magedelight\Catalog\Helper\Data $helper
    ) {
        $this->vendorProduct = $vendorProduct->create();
        $this->request = $request;
        $this->helper = $helper;
    }

    /**
     * @param \Magento\Catalog\Pricing\Price\RegularPrice $subject
     * @param \Closure $proceed
     * @return float|null
     */
    public function aroundGetValue(\Magento\Catalog\Pricing\Price\RegularPrice $subject, \Closure $proceed)
    {
        /*
         * Router check was set as to fetch data from indexed table only to boost performance.
         * Commenting router check as for some places the price is not rendered from
         * catalog_product_price_index table and hence pricing is not displayed proper.
        $routers = $this->getAllowedRouters();
        $currentRoute = [
            $this->request->getRouteName(),
            $this->request->getControllerName(),
            $this->request->getActionName()
        ];
        if (in_array($currentRoute, $routers) &&
            count($this->vendorProduct->getParentIdsByChild($subject->getProduct()->getId())) < 1) {
        }
        */

        /*
         * Not excluding child/associated products of configurable products to reflect the regular price throughout the application.
        if (count($this->vendorProduct->getParentIdsByChild($subject->getProduct()->getId())) < 1) { */
            $price = $proceed();
            $vendorMinPrice = $this->vendorProduct->getMinPrice(
                $subject->getProduct()->getId(),
                $subject->getProduct()->getTypeId(),
                true
            );
            $productPrice = ($vendorMinPrice) ? $vendorMinPrice : $price;
            return $this->helper->currency($productPrice, false, false, true);
        /* } else {
            return $proceed();
        } */
    }

    /**
     *
     * @return array
     */
    protected function getAllowedRouters()
    {
        return
        [
            [
                'catalog',
                'product',
                'view'
            ],
            [
                'rbvendor',
                'microsite_vendor',
                'product'
            ],
            [
                'rbvendor',
                'microsite_vendor',
                'index'
            ]
        ];
    }
}
