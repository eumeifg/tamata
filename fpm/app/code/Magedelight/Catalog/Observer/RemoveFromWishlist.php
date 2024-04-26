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
namespace Magedelight\Catalog\Observer;

use Magento\Framework\Event\ObserverInterface;

class RemoveFromWishlist implements ObserverInterface
{

    /**
     * @var \Magedelight\Catalog\Helper\Data
     */
    protected $helper;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Wishlist\Model\WishlistFactory $wishlist
     * @param \Magento\Wishlist\Model\ResourceModel\Item\CollectionFactory $wishlistCollectionFactory
     * @param \Magedelight\Catalog\Helper\Data $helper
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Wishlist\Model\WishlistFactory $wishlist,
        \Magento\Wishlist\Model\ResourceModel\Item\CollectionFactory $wishlistCollectionFactory,
        \Magedelight\Catalog\Helper\Data $helper
    ) {
        $this->context = $context;
        $this->wishlist = $wishlist;
        $this->helper = $helper;
    }

    /**
     * Set Cart Item data for promocode condition for vendor specific
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if ($this->helper->isEnabled()) {
            $ids = $observer->getId();
            $vendorIds = $observer->getVendorIds();
            $wishlistModel = $this->wishlist->create();
            $collection = $wishlistModel->getCollection();

            foreach ($collection as $wishlist) {
                $items = $wishlist->getItemCollection();
                foreach ($items as $item) {
                    if (in_array($item->getProductId(), $ids) && in_array($item->getVendorId(), $vendorIds)) {
                        $item->delete();
                    }
                }
                $wishlist->save();
            }
        }
    }
}
