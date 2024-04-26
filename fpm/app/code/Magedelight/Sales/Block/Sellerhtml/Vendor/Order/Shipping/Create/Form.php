<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Sales
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Sales\Block\Sellerhtml\Vendor\Order\Shipping\Create;

class Form extends \Magento\Framework\View\Element\Template
{

    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    private $_orderFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    private $_coreRegistry;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        parent::__construct($context, $data);

        $this->_orderFactory = $orderFactory;
    }

    /**
     * Retrieve invoice order
     *
     * @return \Magento\Sales\Model\Order
     */
    public function getOrder()
    {
        return $this->getShipment()->getOrder();
    }

    /**
     * Retrieve source
     *
     * @return \Magento\Sales\Model\Order\Shipment
     */
    public function getSource()
    {
        return $this->getShipment();
    }

    /**
     * Retrieve shipment model instance
     *
     * @return \Magento\Sales\Model\Order\Shipment
     */
    public function getShipment()
    {
        return $this->_coreRegistry->registry('current_shipment');
    }

    /**
     * @return \Magento\Framework\View\Element\AbstractBlock
     */
    protected function _prepareLayout()
    {
        $this->addChild(
            'items',
            \Magedelight\Sales\Block\Sellerhtml\Vendor\Order\Shipping\Create\Items::class
        );
        return parent::_prepareLayout();
    }

    /**
     * @return string
     */
    public function getPaymentHtml()
    {
        return $this->getChildHtml('order_payment');
    }

    /**
     * @return string
     */
    public function getItemsHtml()
    {
        return $this->getChildHtml('order_items');
    }

    /**
     * @return string
     */
    public function getSaveUrl()
    {
        return $this->getUrl('rbsales/order_shipment/save', ['order_id' => $this->getShipment()->getOrderId()]);
    }

    public function getVendorOrderId()
    {
        return $this->_coreRegistry->registry('vendor_order')->getVendorOrderId();
    }

    public function getVendorOrderIncrementId()
    {
        return $this->_coreRegistry->registry('vendor_order')->getIncrementId();
    }
    /**
     *
     * @return
     */
    public function getOrderData()
    {
        if ($this->getRequest()->getParam('order_id', false)) {
            $orderId = $this->_coreRegistry->registry('vendor_order')->getOrderId();

            $order = $this->_orderFactory->create()->load($orderId);
            return $order;
        } else {
            return;
        }
    }
}
