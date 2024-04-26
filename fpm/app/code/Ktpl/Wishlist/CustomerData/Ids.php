<?php
/**
 * Copyright © Krish TechnoLabs, All rights reserved.
 */

namespace Ktpl\Wishlist\CustomerData;

use Magento\Customer\Model\Session;
use Magento\Wishlist\Model\WishlistFactory;

class Ids implements \Magento\Customer\CustomerData\SectionSourceInterface
{
    /**
     * @var customerSession
     */
    protected $customerSession;

    /**
     * @var wishlistFactory
     */
    protected $wishlistFactory;

    /**
     * @param Session $customerSession
     * @param WishlistFactory $wishlistFactory
     */
    public function __construct(
        Session $customerSession,
        WishlistFactory $wishlistFactory
    ) {
        $this->customerSession = $customerSession;
        $this->wishlistFactory = $wishlistFactory;
    }


    public function getSectionData()
    {
        if ($customerId = $this->customerSession->getCustomerId()) {
            $items = $this->wishlistFactory->create()
                ->loadByCustomerId($customerId)
                ->getItemCollection();

            $itemIds = [];
            foreach($items as $item)
                $itemIds[] = ['product_id' => $item->getProductId()];

            return ['ids' => $itemIds];
        }
        return ['ids' => null];
    }
}
