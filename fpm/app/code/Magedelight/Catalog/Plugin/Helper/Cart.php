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
namespace Magedelight\Catalog\Plugin\Helper;

class Cart
{

    /**
     * Retrieve url for add product to cart
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param array $additional
     * @return  string
     */
    public function afterGetAddUrl(\Magento\Checkout\Helper\Cart $subject, $url, $product, $additional = [])
    {
        return ($product->getVendorId())?$url.'vendor_id/'.$product->getVendorId():$url;
    }
}
