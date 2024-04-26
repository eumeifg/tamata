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
namespace Magedelight\Sales\Block\Sellerhtml\Vendor\Order\View;

use Magento\Payment\Helper\Data as PaymentHelper;
use Magento\Sales\Model\Order\Address\Renderer as AddressRenderer;

class Info extends \Magento\Sales\Block\Order\Info
{

    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    private $_orderFactory;

    /**
     * @var \Magento\Sales\Model\Order\Config
     */
    protected $salesConfig;

    /**
     * @var string
     */
    protected $_template = 'Magedelight_Sales::vendor/order/new/view.phtml';

    /**
     * @var \Magedelight\Backend\Model\Auth\Session
     */
    protected $authSession;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\CollectionFactory
     */
    protected $_orderCollectionFactory;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Collection
     */
    protected $orders;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param PaymentHelper $paymentHelper
     * @param AddressRenderer $addressRenderer
     * @param \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory
     * @param \Magedelight\Backend\Model\Auth\Session $authSession
     * @param \Magento\Sales\Model\Order\Config $salesConfig
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        PaymentHelper $paymentHelper,
        AddressRenderer $addressRenderer,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
        \Magedelight\Backend\Model\Auth\Session $authSession,
        \Magento\Sales\Model\Order\Config $salesConfig,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        array $data = []
    ) {
        $this->_orderCollectionFactory = $orderCollectionFactory;
        $this->salesConfig = $salesConfig;
        $this->_orderFactory = $orderFactory;
        $this->authSession = $authSession;
        parent::__construct($context, $registry, $paymentHelper, $addressRenderer, $data);
    }

    /**
     * Retrieve current order model instance
     *
     * @return \Magedelight\Sales\Model\Order
     */
    public function getOrder()
    {
        $orderId = $this->coreRegistry->registry('vendor_order')->getOrderId();

        $order = $this->_orderFactory->create()->load($orderId);

        return $order;
    }

    public function getOrderData()
    {
        if ($this->getRequest()->getParam('order_id', false)) {
            $order = $this->coreRegistry->registry('vendor_order');
            return $order;
        } else {
            return;
        }
    }

    /**
     *
     * @return int
     */
    public function getVendorId()
    {
        return $this->authSession->getUser()->getVendorId();
    }

    /**
     *
     * @return string
     */
    public function getSubmitUrl()
    {
        return $this->getUrl('rbsales/order/save');
    }

    /**
     *
     * @param string $statusCode
     * @return string Description
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getOrderStatusLabel($statusCode)
    {
        return $this->salesConfig->getStatusLabel($statusCode);
    }

    /**
     *
     * @return string
     */
    public function getShipmentUrl()
    {
        if ($orderId = $this->getRequest()->getParam('order_id', false)) {
            return $this->getUrl('rbsales/order_shipment/new', ['tab'=> '2,0','order_id' => $orderId]);
        }
    }

    /**
     * @return string
     */
    public function getItemsHtml()
    {
        return $this->getChildHtml('order_items');
    }
}
