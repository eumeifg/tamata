<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Wishlist
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Wishlist\Block\Plugin\Customer\Wishlist\Item\Column;

class Cart
{
    /**
     * Return product for current item
     *
     * @return \Magento\Catalog\Model\Product
     */
    public function afterGetProductItem(
        \Magento\Wishlist\Block\Customer\Wishlist\Item\Column\Cart $subject,
        $result
    ) {
        if ($result->getTypeId() == 'configurable') {
            $result->setIsSalable(true);
        }
        return $result;
    }
}
