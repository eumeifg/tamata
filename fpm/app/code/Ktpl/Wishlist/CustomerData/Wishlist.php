<?php

namespace Ktpl\Wishlist\CustomerData;

use Magento\Catalog\Helper\ImageFactory;
use Magento\Catalog\Model\Product\Configuration\Item\ItemResolverInterface;
use Magento\Framework\App\ViewInterface;
use Magento\Wishlist\Block\Customer\Sidebar;
use Magento\Wishlist\Helper\Data;

class Wishlist extends \Magento\Wishlist\CustomerData\Wishlist
{
    public function __construct(
        Data $wishlistHelper,
        Sidebar $block,
        ImageFactory $imageHelperFactory,
        ViewInterface $view,
        ItemResolverInterface $itemResolver = null)
    {
        parent::__construct($wishlistHelper, $block, $imageHelperFactory, $view, $itemResolver);
    }

    protected function createCounter($count)
    {
        if ($count > 0) {
            return __('%1', $count);
        }
        return null;
    }
}