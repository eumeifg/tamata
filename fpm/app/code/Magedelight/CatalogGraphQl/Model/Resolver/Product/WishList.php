<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magedelight\CatalogGraphQl\Model\Resolver\Product;

use Magento\Framework\DataObject;
use Magento\Framework\DataObjectFactory;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\CustomerGraphQl\Model\Customer\GetCustomer;


/**
 * Resolve data for product canonical URL
 */
class WishList implements ResolverInterface
{

   protected $_currentUserWishlistCollectionFactory ;
   protected $getCustomer ;


    public function __construct(
       \Magento\Wishlist\Model\ResourceModel\Item\CollectionFactory $currentUserWishlistCollectionFactory,
       GetCustomer $getCustomer
        
    ) {
        $this->_currentUserWishlistCollectionFactory = $currentUserWishlistCollectionFactory;
        $this->getCustomer = $getCustomer;
    }

    /**
     * @inheritdoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        if (!isset($value['model'])) {
            throw new LocalizedException(__('"model" value should be specified'));
        }
        
        /* @var $product Product */
        $product = $value['model'];
        $productId = $product->getId();
        $customerId = $context->getUserId();
                
        if ($customerId === 0) {
            $is_wishlist = false;
        } else
        {
            $collection = $this->_currentUserWishlistCollectionFactory->create()->addCustomerIdFilter($customerId)->addFieldToFilter('product_id',$productId);
            if($collection->count() > 0)
            {
                $is_wishlist = true;
            } else {
               $is_wishlist = false;
            }

        }
        return $is_wishlist;

    }

}
