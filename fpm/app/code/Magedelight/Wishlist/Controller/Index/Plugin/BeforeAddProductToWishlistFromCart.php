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
namespace Magedelight\Wishlist\Controller\Index\Plugin;

/**
 * Description of BeforeAddProductToWishlistFromCart
 */
class BeforeAddProductToWishlistFromCart
{

    /**
     * @var \Magento\Checkout\Model\Cart
     */
    protected $cart;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;
    
    /**
     *
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Checkout\Model\Cart $cart
     */
    public function __construct(
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Checkout\Model\Cart $cart
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->cart = $cart;
    }
    
    public function beforeExecute(\Magento\Wishlist\Controller\Index\Fromcart $subject)
    {
        $itemId = $subject->getRequest()->getParam('item');
        $item = $this->cart->getQuote()->getItemById($itemId);
        if ($item) {
            if ($item->getVendorId()) {
                $this->checkoutSession->setVendorId($item->getVendorId());
            }
        }
    }
}
