<?php

namespace CAT\Cart\Plugin;

use Magento\Checkout\Model\Cart;

class PreventAddToCart
{

    protected $scopeConfig;

    public function __construct(\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig)
   {
      $this->scopeConfig = $scopeConfig;
   }

    public function beforeAddProduct(Cart $subject, $productInfo, $requestInfo = null)
    {
        // check module is enable 
        $ModuleStaus = $this->scopeConfig->getValue('cart_limit/general/enable',\Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $ModuleLimit = (int)$this->scopeConfig->getValue('cart_limit/general/limit',\Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        if ($ModuleStaus) {
            // check its already in cart
            $incart = false;
            $cartitems =  $subject->getQuote()->getAllVisibleItems();
            foreach($cartitems as $cartitem){
                if($cartitem->getProductId() == $productInfo->getID() ){
                   $incart = true;
                }
            }
            if(!$incart){
                if(count($cartitems) >= $ModuleLimit){
                    throw new \Magento\Framework\Exception\LocalizedException(__("Sorry, you’ve reached the limit of your cart, please complete this order and start a new one"));
                }
            }
        }
        return [$productInfo,$requestInfo];
    }
}