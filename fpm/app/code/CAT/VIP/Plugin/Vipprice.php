<?php
 
namespace CAT\VIP\Plugin;
 
class Vipprice
{
    public function afterGet(\Ktpl\Productslider\Api\ProductRepositoryInterface $subject, \Ktpl\Productslider\Api\Data\ProductInterface $result)
    {
         $result->setVipPrice('0');
         return $result;
    }
    
}