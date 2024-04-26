<?php

namespace CAT\VIP\Model;

class PriceProcessor extends \MDC\Catalog\Model\PriceProcessor
{

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
                $price = $this->helper->getVendorFinalPrice($item->getVendorId(), $item->getProductId(),true,true,$item->getQty(),$quote);
            }
            $price = $this->helper->currency($price, false, false, true);
            if ($price && $item->getCustomPrice() && $price != $item->getCustomPrice()) {
                $item->setCustomPrice($price);
                $item->setOriginalCustomPrice($price);
                $item->save();
                $expiredSaleProducts[] = $item->getId();
            }
        }
        if (!empty($expiredSaleProducts)) {
            $quote->collectTotals()->save();
        }
        return $expiredSaleProducts;
    }


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
                    $simpleProduct->getProductId(),true,true,$item->getQty(),$quote
                );
            }
        }
        return null;
    }

}
