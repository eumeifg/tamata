<?php
/*
 * Copyright © 2016 Rocket Bazaar. All rights reserved.
 * See COPYING.txt for license details
 */
namespace Magedelight\Wishlist\Model\Item\Plugin;

/**
 * Description of Option
 *
 * @author Rocket Bazaar Core Team
 */
class Option
{
    

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    /**
     * @var \Magedelight\Catalog\Helper\Data
     */
    protected $_helper;

    public function __construct(
        \Magedelight\Catalog\Helper\Data $helper,
        \Magento\Checkout\Model\Session $checkoutSession
    ) {
    
        $this->_helper = $helper;
        $this->_checkoutSession = $checkoutSession;
    }
    /**
     * Retrieve item product instance
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return \Magento\Catalog\Model\Product
     */
    public function afterGetProduct(
        \Magento\Wishlist\Model\Item\Option $subject,
        $result
    ) {
        $vendorId = $subject->getItem()->getVendorId();
        if ($vendorId) {
            $productId = $result->getId();
            $price = $this->_helper->getVendorFinalPrice($vendorId, $productId);
            $result->setPrice($price);
        }
        return $result;
    }
}
