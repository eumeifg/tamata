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

use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

class SpecialPrice
{

    /**
     * @var Magedelight\Catalog\Model\ProductFactory
     */
    protected $_vendorProductFactory;

    /**
     * @var TimezoneInterface
     */
    protected $_localeDate;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * @var \Magedelight\Catalog\Helper\Data
     */
    protected $helper;

    /**
     * @param \Magedelight\Catalog\Model\ProductFactory $vendorProductFactory
     * @param \Magento\Framework\App\RequestInterface $request
     * @param TimezoneInterface $localeDate
     * @param \Magedelight\Catalog\Helper\Data $helper
     */
    public function __construct(
        \Magedelight\Catalog\Model\ProductFactory $vendorProductFactory,
        \Magento\Framework\App\RequestInterface $request,
        TimezoneInterface $localeDate,
        \Magedelight\Catalog\Helper\Data $helper
    ) {
        $this->_vendorProductFactory = $vendorProductFactory;
        $this->localeDate = $localeDate;
        $this->request = $request;
        $this->helper = $helper;
    }

    /**
     * @return float|null
     */
    public function aroundGetValue(\Magento\Catalog\Pricing\Price\SpecialPrice $subject, \Closure $proceed)
    {
        /*
         * Router check was set as to fetch data from indexed table only to boost performance.
         * Commenting router check as for some places the special price is not rendered from
         * catalog_product_price_index table and hence pricing is not displayed proper.
         * $routers = $this->getAllowedRouters();
        $currentRoute = [
            $this->request->getRouteName(),
            $this->request->getControllerName(),
            $this->request->getActionName()
        ];
        if (in_array($currentRoute, $routers)) { */
        $vendorProduct = $this->_vendorProductFactory->create()->getProductDefaultVendor(
            $this->request->getParam('vid') ? $this->request->getParam('vid') : $subject->getProduct()->getVendorId(),
            $subject->getProduct()->getId(),
            true
        );
        if ($vendorProduct) {
            if (count($vendorProduct->getData())) {
                return $this->helper->currency($this->getProcessedPrice($vendorProduct), false, false, true);
            }
        }
        /* } */
        return $proceed();
    }

    /**
     *
     * @param \Magedelight\Catalog\Model\Product $vendorProduct
     * @return float
     */
    protected function getProcessedPrice($vendorProduct)
    {
        $currentDate= date('Y-m-d', strtotime(date('Y-m-d')));
        $fromDate = $vendorProduct->getData('special_from_date');
        $toDate = $vendorProduct->getData('special_to_date');

        if (empty($fromDate) && empty($toDate)) {

            /* Return special price if set without date(s). */
            return ($vendorProduct->getData('special_price')) ?
                $vendorProduct->getData('special_price') : $vendorProduct->getData('price');
        }

        if (empty($fromDate) && !empty($toDate)) {
            $fromDate = date('Y-m-d');
        } elseif (!empty($fromDate) && empty($toDate)) {
            $toDate = date('Y-m-d');
        }

        // If current date is between from special date.
        if ($currentDate >= date('Y-m-d', strtotime($fromDate)) && $currentDate <= date('Y-m-d', strtotime($toDate))) {
            return $vendorProduct->getData('special_price');
        }
        return $vendorProduct->getData('price');
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
