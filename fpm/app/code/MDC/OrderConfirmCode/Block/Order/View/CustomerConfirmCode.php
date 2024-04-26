<?php

namespace MDC\OrderConfirmCode\Block\Order\View;

use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template\Context as TemplateContext;


class CustomerConfirmCode extends \Magento\Framework\View\Element\Template
{
	
	public function __construct(
        TemplateContext $context, 
        Registry $registry,
        array $data = []
    ) { 
    	$this->coreRegistry = $registry;   
    	$this->scopeConfig = $context->getScopeConfig();      
        parent::__construct($context, $data);
    }

    public function getOrderConfirmationCode(){

    	$currentOrderConfirmCode = $this->getOrder()->getData('order_confirm_code');

    	return $currentOrderConfirmCode;

    }

    public function getOrder()
    {
        return $this->coreRegistry->registry('current_order');
    }

     
}	