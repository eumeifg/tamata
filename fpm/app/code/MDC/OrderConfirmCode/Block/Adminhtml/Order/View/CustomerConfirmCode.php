<?php

namespace MDC\OrderConfirmCode\Block\Adminhtml\Order\View;
/**
 * 
 */
class CustomerConfirmCode extends \Magento\Backend\Block\Template
{
	
	protected $orderRepository;

	public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Sales\Model\OrderRepository $orderRepository,
        array $data = []
    ) {
        $this->orderRepository = $orderRepository; 
        $this->scopeConfig = $context->getScopeConfig();      
        parent::__construct($context,$data);
    }

 	public function getOrderConfirmationCode(){

 		$order_id = $this->getRequest()->getParam('order_id');
		$order =  $this->orderRepository->get($order_id);

    	// $currentOrderConfirmCode = $order->getData('order_confirm_code');
    	$currentOrderConfirmCode = $order->getOrderConfirmCode();

    	return $currentOrderConfirmCode;

    }

}