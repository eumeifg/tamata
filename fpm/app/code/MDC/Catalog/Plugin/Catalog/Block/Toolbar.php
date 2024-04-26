<?php
namespace MDC\Catalog\Plugin\Catalog\Block;

use Magento\Framework\DB\Select;

class Toolbar
{
	
	/**
    * Plugin
    *
    * @param \Magento\Catalog\Block\Product\ProductList\Toolbar $subject
    * @param \Closure $proceed
    * @param \Magento\Framework\Data\Collection $collection
    * @return \Magento\Catalog\Block\Product\ProductList\Toolbar
    */
	public function aroundSetCollection(
	\Magento\Catalog\Block\Product\ProductList\Toolbar $subject,
	\Closure $proceed,
	$collection
	) {
		$currentOrder = $subject->getCurrentOrder();
		$result = $proceed($collection);

		if ($currentOrder) {
			if ($currentOrder == 'high_to_low') {
				$subject->getCollection()->getSelect()->reset(Select::ORDER)->order('price_index.min_price DESC');
			} elseif ($currentOrder == 'low_to_high') {
				$subject->getCollection()->getSelect()->reset(Select::ORDER)->order('price_index.min_price ASC');
			}
		}
		
		return $result;
	}
	
}