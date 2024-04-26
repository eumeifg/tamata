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
 * Description of BeforeAddProductToWishlist
 */
class BeforeAddProductToWishlist
{

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;
    
    /**
     * @var \Magedelight\Catalog\Helper\Data
     */
    protected $helper;
    
    /**
     *
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magedelight\Catalog\Helper\Data $helper
     */
    public function __construct(
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magedelight\Catalog\Helper\Data $helper
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->helper = $helper;
    }
    
    public function beforeExecute(\Magento\Wishlist\Controller\Index\Add $subject)
    {
        $vendorId = $subject->getRequest()->getParam('vendor_id');
        if (empty($vendorId)) {
            $productId = $subject->getRequest()->getParam('product');
            $vendorId = $this->helper->getDefaultVendorId($productId);
            $subject->getRequest()->setParams(['vendor_id'=>$vendorId]);
        }
        $this->checkoutSession->setVendorId($vendorId);
    }
}
