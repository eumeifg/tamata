<?php

declare(strict_types=1);

namespace MDC\OrderConfirmCode\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;

class AddConfirmationCodeInOrder implements ObserverInterface
{
	
    public function __construct(
        \Magento\Framework\Math\Random $mathRandom
    ) {
        $this->mathRandom = $mathRandom;       
    }

	/**
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();

        $confirmationCode = $this->mathRandom->getRandomString(12);
        
        $order->setData('order_confirm_code', $confirmationCode);
         
    }
}