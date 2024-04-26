<?php
namespace CAT\VIP\Observer;
use Magento\Framework\Event\ObserverInterface;
 
class ProcessFinalPriceObserver implements ObserverInterface
{
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
 
        return $this;
    }
}