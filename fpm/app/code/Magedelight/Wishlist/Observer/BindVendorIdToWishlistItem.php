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
namespace Magedelight\Wishlist\Observer;

use Magento\Framework\Event\ObserverInterface;

/**
 * Description of BindVendorIdToWishlistItem
 *
 */
class BindVendorIdToWishlistItem implements ObserverInterface
{

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    public function __construct(
        \Magento\Checkout\Model\Session $checkoutSession
    ) {
        
        $this->checkoutSession = $checkoutSession;
    }
    
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
         $event = $observer->getEvent();
         $items = $event->getItems();
         $vendorId = $this->checkoutSession->getVendorId();
        foreach ($items as $item) {
            $item->setVendorId($vendorId)->save();
        }
         $this->checkoutSession->unsVendorId();
    }
}
