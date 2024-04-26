<?php
/**
 * Copyright © Krish TechnoLabs, All rights reserved.
 */
declare(strict_types=1);

namespace Ktpl\GoogleMapAddress\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;

class SetPickupLocationInOrder implements ObserverInterface
{
    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();
        /** @var $order \Magento\Sales\Model\Order **/

        $pickuStreetLocation = $order->getShippingAddress()->getData('street');

        $isPickAddress = $order->getShippingAddress()->getData('is_pickup_address');

        if($isPickAddress){
            $order->setData('pickup_name',$pickuStreetLocation);
        }
        
    }
}
