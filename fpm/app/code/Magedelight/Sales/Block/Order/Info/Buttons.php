<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Block of links in Order view page
 */
namespace Magedelight\Sales\Block\Order\Info;

use Magento\Customer\Model\Context;

/**
 * @api
 * @since 100.0.2
 */
class Buttons extends \Magento\Sales\Block\Order\Info\Buttons
{
    /**
     * @var string
     */
    protected $_template = 'Magedelight_Sales::order_detail_page/order/info/buttons.phtml';

    /**
     * @var \Magento\Framework\App\Http\Context
     */
    protected $httpContext;

    protected $_mdSalesHelper;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Http\Context $httpContext,
        \Magedelight\Sales\Helper\Data $mdSalesHelper,
        array $data = []
    ) {
        $this->_mdSalesHelper = $mdSalesHelper;
        parent::__construct($context, $registry, $httpContext, $data);
    }
    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\App\Http\Context $httpContext
     * @param array $data
     */
//    public function __construct(
//        \Magento\Framework\View\Element\Template\Context $context,
//        \Magento\Framework\Registry $registry,
//        \Magento\Framework\App\Http\Context $httpContext,
//        \Magedelight\Sales\Helper\Data $mdSalesHelper,
//        array $data = []
//    ) {
//        $this->_coreRegistry = $registry;
//        $this->httpContext = $httpContext;
//        parent::__construct($context, $data);
//        $this->_mdSalesHelper = $mdSalesHelper;
//    }

//    /**
//     * Retrieve current order model instance
//     *
//     * @return \Magento\Sales\Model\Order
//     */
//    public function getOrder()
//    {
//        return $this->_coreRegistry->registry('current_order');
//    }
//
//    /**
//     * Get url for printing order
//     *
//     * @param \Magento\Sales\Model\Order $order
//     * @return string
//     */
//    public function getPrintUrl($order)
//    {
//        if (!$this->httpContext->getValue(Context::CONTEXT_AUTH)) {
//            return $this->getUrl('sales/guest/print', ['order_id' => $order->getId()]);
//        }
//        return $this->getUrl('sales/order/print', ['order_id' => $order->getId()]);
//    }
//
//    /**
//     * Get url for reorder action
//     *
//     * @param \Magento\Sales\Model\Order $order
//     * @return string
//     */
//    public function getReorderUrl($order)
//    {
//        if (!$this->httpContext->getValue(Context::CONTEXT_AUTH)) {
//            return $this->getUrl('sales/guest/reorder', ['order_id' => $order->getId()]);
//        }
//        return $this->getUrl('sales/order/reorder', ['order_id' => $order->getId()]);
//    }

    /**
     * Get url for canceling order
     *
     * @param  \Magento\Sales\Model\Order $order
     * @return string
     */
    public function getCancelFormActionUrl($order)
    {
        if (!$this->httpContext->getValue(Context::CONTEXT_AUTH)) {
            return '';
        }
        return $this->getUrl('rbsales/*/customerCancelOrder', ['order_id' => $order->getId(), '_secure' => true]);
    }

    /*
     * Return module status
     */
    public function isEnabledCustomerCancelOrder()
    {
        return $this->_mdSalesHelper->isEnabledCustomerCancelOrder();
    }

    public function getCustomerCancelOrderReason()
    {
        return $this->_mdSalesHelper->getCustomerCancelOrderReason();
    }
}
