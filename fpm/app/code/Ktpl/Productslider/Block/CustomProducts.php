<?php
/**
 * Ktpl
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the KrishTechnolabs.com license that is
 * available through the world-wide-web at this URL:
 * https://https://www.KrishTechnolabs.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Ktpl
 * @package     Ktpl_Productslider
 * @copyright   Copyright (c) Ktpl (https://https://www.KrishTechnolabs.com//)
 * @license     https://https://www.KrishTechnolabs.com/LICENSE.txt
 */

namespace Ktpl\Productslider\Block;

/**
 * Class CustomProducts
 * @package Ktpl\Productslider\Block
 */
class CustomProducts extends AbstractSlider
{
    /**
     * @return $this|mixed
     */
    public function getProductCollection()
    {
        $productIds = $this->getSlider()->getProductIds();
        if (!is_array($productIds)) {
            $productIds = explode('&', $productIds);
        }

        $collection = $this->_productCollectionFactory->create()
            ->addIdFilter($productIds)
            ->setPageSize($this->getProductsCount());
        $this->_addProductAttributesAndPrices($collection);

        return $collection;
    }
}
