<?php

namespace MDC\Sales\Observer\Sales;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\RequestInterface;

/**
 * VendorOrderConfirm class
 */
class VendorOrderConfirm implements ObserverInterface {
    
    /**
     * @var RequestInterface
     */
    protected $_request;

    /**
     * @param RequestInterface $request
     */
    public function __construct(
        RequestInterface $request
    ) {
        $this->_request = $request;
    }
    
    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        $orderGender  = $this->_request->getParam('order_gender');
        if($orderGender) {
            $order = $observer->getOrder();
            $order->setOrderGender($orderGender);
            $order->save();
        }
    }
}