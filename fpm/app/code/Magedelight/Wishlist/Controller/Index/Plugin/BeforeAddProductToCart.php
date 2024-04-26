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
 * Description of BeforeAddProductToCart
 */
class BeforeAddProductToCart
{
    
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     *
     * @param \Magento\Checkout\Model\Session $checkoutSession
     */
    public function __construct(
        \Magento\Checkout\Model\Session $checkoutSession
    ) {
        $this->checkoutSession = $checkoutSession;
    }
    
    public function beforeExecute(\Magento\Wishlist\Controller\Index\Cart $subject)
    {
        $qtyToAdd = $subject->getRequest()->getParam('qty');
        if ($qtyToAdd == "") {
            $qtyToAdd = 1;
        }
        $this->checkoutSession->setQtyToAdd($qtyToAdd);
    }
}
