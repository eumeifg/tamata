<?php

namespace MDC\Sales\Block\Adminhtml\Order;

/**
 * ModalBox class
 */
class ModalBox extends \Magento\Backend\Block\Template
{

    /**
     * Constructor
     *
     * @param \Magento\Backend\Block\Template\Context  $context
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    public function getFormUrl()
    {
        $orderId = false;
        if($this->hasData('order')){
            $orderId = $this->getOrder()->getId();
        }
        return $this->getUrl('rbsales/order/confirm',[
            'id' => $orderId
        ]);
    }
}