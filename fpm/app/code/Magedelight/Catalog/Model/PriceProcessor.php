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
namespace Magedelight\Catalog\Model;

class PriceProcessor extends \Magento\Framework\Model\AbstractModel
{

    /**
     * @var \Magedelight\Catalog\Model\Product
     */
    protected $vendorProduct;

    /**
     * @var \Magedelight\Catalog\Helper\Data
     */
    protected $helper;

    /**
     * @param \Magedelight\Catalog\Model\Product $vendorProduct
     * @param \Magedelight\Catalog\Helper\Data $helper
     */
    public function __construct(
        \Magedelight\Catalog\Model\Product $vendorProduct,
        \Magedelight\Catalog\Helper\Data $helper
    ) {
        $this->vendorProduct = $vendorProduct;
        $this->helper = $helper;
    }

    /**
     * @return array
     */
    public function getExpiredSaleProducts($quote)
    {
        $expiredSaleProducts = [];
        if (!$quote->getId()) {
            return $expiredSaleProducts;
        }
        $parentId = null;
        $parentPrice = null;
        foreach ($quote->getAllItems() as $item) {
            if ($this->helper->isStoreCreditEnabled() && $this->helper->getStoreCreditProduct() == $item->getSku()) {
                continue;
            }
            if ($item->getAppliedDealId()) {
                continue;
            }
            if ($item->getParentItemId()) {
                continue;
            }

            if ($this->skipProductCheck($item)) {
                /* Added extra check in order to skip check for customizations. */
                continue;
            }

            if ($item->getProductType() == \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE) {
                $price = $this->getPriceForConfigurableProduct($quote, $item);
            } else {
                $price = $this->helper->getVendorFinalPrice($item->getVendorId(), $item->getProductId());
            }
            $price = $this->helper->currency($price, false, false, true);
            if ($price && $item->getCustomPrice() && $price != $item->getCustomPrice()) {
                $item->setCustomPrice($price);
                $item->setOriginalCustomPrice($price);
                $expiredSaleProducts[] = $item->getId();
            }
        }
        if (!empty($expiredSaleProducts)) {
            $quote->collectTotals()->save();
        }
        return $expiredSaleProducts;
    }

    /*
     * Get Product Price for quote item
     */
    protected function getPriceForConfigurableProduct($quote, $item)
    {
        $simpleProduct = null;
        if ($item) {
            foreach ($quote->getAllItems() as $newItem) {
                if ($newItem->getParentItemId() == $item->getId()) {
                    $simpleProduct = $newItem;
                }
            }
            if ($simpleProduct) {
                return $price = $this->helper->getVendorFinalPrice(
                    $item->getVendorId(),
                    $simpleProduct->getProductId()
                );
            }
        }
        return null;
    }

    /**
     *
     * @param type $item
     * @return boolean
     */
    public function skipProductCheck($item)
    {
        return false;
    }
}
