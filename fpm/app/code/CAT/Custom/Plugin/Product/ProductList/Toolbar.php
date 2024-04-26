<?php
namespace CAT\Custom\Plugin\Product\ProductList;

class Toolbar
{
    public function aroundSetCollection(
        \Magento\Catalog\Block\Product\ProductList\Toolbar $subject,
        \Closure $proceed,
        $collection
    ) {
        $currentOrder = $subject->getCurrentOrder();
        if ($currentOrder == "random") {
            $dir = $subject->getCurrentDirection();
            $collection->getSelect()->orderRand();
        }
        return $proceed($collection);
    }
}