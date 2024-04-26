<?php

namespace CAT\VIP\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Customer\Model\ResourceModel\Group\Collection as CustomerGroup;
class Data extends AbstractHelper
{
    protected $vipProductRepository;
    protected $customerSession;
    protected $vipOrdersFactory;
    protected $cart;
    protected $request;
    protected $groupRepository;
    protected $customerGroup;
    
    /**
     * @param \Magento\Framework\App\Helper\Context $context
     */

    public function __construct(\Magento\Framework\App\Helper\Context $context,\CAT\VIP\Model\VIPProductsFactory $vipProductRepository,\Magento\Customer\Model\Session $customerSession,\CAT\VIP\Model\VipOrdersFactory $vipOrdersFactory,\Magento\Checkout\Model\Session $cart,\Magento\Framework\App\RequestInterface $request, \Magento\Customer\Api\GroupRepositoryInterface $groupRepository,CustomerGroup $customerGroup) {
        $this->vipProductRepository = $vipProductRepository;
        $this->vipOrdersFactory = $vipOrdersFactory;
        $this->customerSession = $customerSession;
        $this->request = $request;
        $this->groupRepository = $groupRepository;
        $this->cart = $cart;
        $this->customerGroup = $customerGroup;
        parent::__construct($context);
    }

    Public function isVipCustomer($groupid){
        $groupEntity = $this->groupRepository->getById($groupid);
        return  ($groupEntity->getCode() == 'VIP Customer') ? true : false;
    }

    public function getVipGroupId(){
        $groups = $this->customerGroup->toOptionArray();
        foreach($groups as $group){
            if($group['label'] == 'VIP Customer'){
                return $group['value'];
            }
        }
    }


    /**
     * @return bool
     */
    public function isAllowed($product_id)
    {
        return true;
    }

    public function getConfig($config_path)
    {
        return $this->scopeConfig->getValue($config_path,\Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function isvipProduct($product_id,$Vid,$finalverify = true){
        $vipProduct = $this->vipProductRepository->create()
                           ->getCollection()
                           ->addFieldToFilter('product_id',$product_id)
                           ->addFieldToFilter('vendor_id',$Vid)->getFirstItem();
        if ($vipProduct && $vipProduct->getId()) {
           if($finalverify){
                $customer_id = $this->customerSession->getCustomer()->getId();
                $total_cutomers = $this->vipOrdersFactory->create()
                           ->getCollection()
                           ->addFieldToFilter('product_id',$product_id)
                           ->addFieldToFilter('vendor_id',$Vid);
                $totalSum = 0;
                foreach($total_cutomers as $total_cutomer ){
                    $totalSum = $totalSum + (int)$total_cutomer->getQty();
                }

                if ($totalSum >= $vipProduct->getGlobalQty()){
                    return false;
                }
                $totalbuys = $this->vipOrdersFactory->create()
                               ->getCollection()
                               ->addFieldToFilter('product_id',$product_id)
                               ->addFieldToFilter('customer_id',$customer_id)
                               ->addFieldToFilter('vendor_id',$Vid);

                $ItotalSum = 0;
                foreach($totalbuys as $totalbuy ){
                    $ItotalSum = $ItotalSum + (int)$totalbuy->getQty();
                }

                if ($ItotalSum >= $vipProduct->getIndQty()){
                    return false;
                }

                return $vipProduct;
           }
           return $vipProduct;
        }
        return false;
    }

    public function getvipProductDiscountPrice($product_id,$Pprice,$Vid, $returnstatus = false){
        $ProductPrice = (float) $Pprice;
        $customer_group = $this->customerSession->getCustomer()->getGroupId();
        $vipProduct = $this->vipProductRepository->create()
                           ->getCollection()
                           ->addFieldToFilter('product_id',$product_id)
                           ->addFieldToFilter('vendor_id',$Vid)->getFirstItem();
        if ($vipProduct && $vipProduct->getId()) {
            $customer_id = $this->customerSession->getCustomer()->getId();
            $total_cutomers = $this->vipOrdersFactory->create()
                       ->getCollection()
                       ->addFieldToFilter('product_id',$product_id)
                       ->addFieldToFilter('vendor_id',$Vid);
            $totalSum = 0;
            foreach($total_cutomers as $total_cutomer ){
                $totalSum = $totalSum + (int)$total_cutomer->getQty();
            }

            if ($totalSum >= $vipProduct->getGlobalQty()){
                return $ProductPrice;
            }
            $totalbuys = $this->vipOrdersFactory->create()
                           ->getCollection()
                           ->addFieldToFilter('product_id',$product_id)
                           ->addFieldToFilter('customer_id',$customer_id)
                           ->addFieldToFilter('vendor_id',$Vid);

            $ItotalSum = 0;
            foreach($totalbuys as $totalbuy ){
                $ItotalSum = $ItotalSum + (int)$totalbuy->getQty();
            }

            if ($ItotalSum >= $vipProduct->getIndQty()){
                return $ProductPrice;
            }
            // Fixed discount
            if($vipProduct->getDiscountType() == 'Fixed'){
                return ( $ProductPrice - (float)$vipProduct->getDiscount());
            }
            else{
                $discount = (float)$vipProduct->getDiscount();
                $discountPrice = (($ProductPrice / 100) * $discount);
                return ( $ProductPrice - $discountPrice);
            }
        }

        if($returnstatus){
            return false;
        }
        return $ProductPrice;
    }

    public function applyvipProductDiscountPrice($product_id,$Pprice,$Vid,$cartqty = 0,$customer = 0){

        $ProductPrice = (float) $Pprice;
        $customer_group = $this->customerSession->getCustomer()->getGroupId();
        $customer_id = $this->customerSession->getCustomer()->getId();
        if($customer && $customer->getCustomer()->getId()){
            $customer_group = $customer->getCustomer()->getGroupId();
            $customer_id = $customer->getCustomer()->getId();
        }
        $vipProduct = $this->vipProductRepository->create()
                           ->getCollection()
                           ->addFieldToFilter('product_id',$product_id)
                           ->addFieldToFilter('vendor_id',$Vid)->getFirstItem();

        if ($vipProduct && $vipProduct->getId()) {
            $groups = explode(",",$vipProduct->getCustomerGroup());
        
            if (in_array($customer_group,$groups)) {

                if( !in_array($this->request->getFullActionName(), ['checkout_cart_index','checkout_index_index','__'])){
                    if($customer && $customer->getAllVisibleItems()){
                        $cartitems =  $customer->getAllVisibleItems();
                    }else{
                        $cartitems =  $this->cart->getQuote()->getAllVisibleItems();
                    }
                    foreach($cartitems as $cartitem){
                        if($cartitem->getProductId() == $product_id ){
                           $cartqty =  $cartitem->getQty();
                        }
                    }
                }

                $total_cutomers = $this->vipOrdersFactory->create()
                               ->getCollection()
                               ->addFieldToFilter('product_id',$product_id)
                               ->addFieldToFilter('vendor_id',$Vid);
                $totalSum = (int)$cartqty;
                foreach($total_cutomers as $total_cutomer ){
                    $totalSum = $totalSum + (int)$total_cutomer->getQty();
                }

                if ($totalSum <= $vipProduct->getGlobalQty()){
                    $totalbuys = $this->vipOrdersFactory->create()
                                   ->getCollection()
                                   ->addFieldToFilter('product_id',$product_id)
                                   ->addFieldToFilter('customer_id',$customer_id)
                                   ->addFieldToFilter('vendor_id',$Vid);

                    $ItotalSum = (int)$cartqty;
                    foreach($totalbuys as $totalbuy ){
                        $ItotalSum = $ItotalSum + (int)$totalbuy->getQty();
                    }
                     if ($ItotalSum <= $vipProduct->getIndQty()){

                        // Fixed discount
                        if($vipProduct->getDiscountType() == 'Fixed'){
                            return ( $ProductPrice - (float)$vipProduct->getDiscount());
                        }
                        else{
                            $discount = (float)$vipProduct->getDiscount();
                            $discountPrice = (($ProductPrice / 100) * $discount);
                            return ( $ProductPrice - $discountPrice);
                        }
                    }
                }
            }
        }

        return $ProductPrice;
    }
}