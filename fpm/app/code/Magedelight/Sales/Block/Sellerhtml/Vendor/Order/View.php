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
namespace Magedelight\Sales\Block\Sellerhtml\Vendor\Order;

use Magedelight\Sales\Model\Order as VendorOrder;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template\Context;
use Magento\Payment\Helper\Data as PaymentHelper;
use Magento\Sales\Model\Order\Address\Renderer as AddressRenderer;
use Magento\Store\Model\ScopeInterface;

class View extends \Magento\Sales\Block\Order\Info
{

    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $_orderFactory;

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
     *
     * @param Context $context
     * @param Registry $registry
     * @param PaymentHelper $paymentHelper
     * @param AddressRenderer $addressRenderer
     * @param \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory
     * @param \Magedelight\Backend\Model\Auth\Session $authSession
     * @param \Magento\Sales\Model\Order\Config $salesConfig
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
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
     * @return void
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        $this->pageConfig->getTitle()->set(__('Order # %1', $this->getOrderData()->getIncrementId()));
    }

    /**
     * Retrieve current order model instance
     *
     * @return \Magento\Sales\Model\Order
     */
    public function getOrder()
    {
        $orderId = $this->coreRegistry->registry('vendor_order')->getOrderId();

        $order = $this->_orderFactory->create()->load($orderId);

        return $order;
    }

    /**
     *
     * @return \Magedelight\Sales\Model\Order
     */
    public function getOrderData()
    {
        if ($this->getRequest()->getParam('id', false)) {
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
        return $this->getUrl('rbsales/order/save', ['order_id' => $this->getOrderData()->getVendorOrderId()]);
    }

    /**
     *
     * @return string
     */
    public function getBackUrl()
    {
        $vendorOrder = $this->getOrderData();
        switch ($vendorOrder->getStatus()) {
            case VendorOrder::STATUS_COMPLETE:
                return $this->getUrl('rbsales/order/complete', ['tab' => $this->_getTab(), 'sfrm' => 'complete']);
            case VendorOrder::STATUS_CLOSED:
                return $this->getUrl('rbsales/order/complete', ['tab' => $this->_getTab(), 'sfrm' => 'closed']);
            case VendorOrder::STATUS_PENDING:
                return $this->getUrl('rbsales/order/index', ['tab' => $this->_getTab(), 'sfrm' => 'new']);
            case VendorOrder::STATUS_PROCESSING:
                return $this->getUrl('rbsales/order/index', ['tab' => $this->_getTab(), 'sfrm' => 'confirmed']);
            case VendorOrder::STATUS_CONFIRMED:
                return $this->getUrl('rbsales/order/index', ['tab' => $this->_getTab(), 'sfrm' => 'confirmed']);
            case VendorOrder::STATUS_PACKED:
                return $this->getUrl('rbsales/order/index', ['tab' => $this->_getTab(), 'sfrm' => 'packed']);
            case VendorOrder::STATUS_SHIPPED:
                return $this->getUrl('rbsales/order/index', ['tab' => $this->_getTab(), 'sfrm' => 'handover']);
            case VendorOrder::STATUS_IN_TRANSIT:
                return $this->getUrl('rbsales/order/index', ['tab' => $this->_getTab(), 'sfrm' => 'intransit']);
            default:
                $tab = $this->_getTab();
                if (!empty($tab)) {
                    $tabArr = explode(',', $tab);
                    if (array_key_exists(1, $tabArr)) {
                        $tabArr[1] = $tabArr[1] + 1;
                    }
                    $tab = implode(',', $tabArr);
                }
                return $this->getUrl('rbsales/order/cancelgrid', ['tab' => $tab]);
        }
    }

    protected function _getTab()
    {
        return $this->getRequest()->getParam('tab');
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
        if ($orderId = $this->getOrderData()->getOrderId()) {
            return $this->getUrl(
                'rbsales/order_shipment/new',
                [
                    'vendor_order_id' => $this->getOrderData()->getVendorOrderId(),
                    'order_id' => $orderId,
                    'tab'=> $this->_getTab()
                ]
            );
        }
    }

    /**
     *
     * @return string
     */
    public function getInvoiceUrl()
    {
        if ($orderId = $this->getOrderData()->getOrderId()) {
            return $this->getUrl(
                'rbsales/order_invoice/new',
                [
                    'vendor_order_id' => $this->getOrderData()->getVendorOrderId(),
                    'order_id' => $orderId,
                    'tab'=> $this->_getTab()
                ]
            );
        }
    }

    public function getPackingSlipUrl()
    {
        if ($orderId = $this->getOrderData()->getOrderId()) {
            return $this->getUrl(
                'rbsales/order/pdfshipments',
                [
                    'vendor_order_id' => $this->getOrderData()->getVendorOrderId(),
                    'order_id' => $orderId
                ]
            );
        }
    }

    public function getOrderCancelUrl()
    {
        if ($orderId = $this->getOrderData()->getOrderId()) {
            return $this->getUrl('rbsales/order/cancel', [
                'vendor_order_id' => $this->getOrderData()->getVendorOrderId(),
                'order_id' => $orderId,
                'tab'=> $this->_getTab()
            ]);
        }
    }

    /**
     * @return bool
     */
    public function isManualShipmentAllowed()
    {
        return $this->_scopeConfig->getValue(
            \Magedelight\Sales\Model\Order::IS_MANUAL_SHIPMENT_ALLOWED,
            ScopeInterface::SCOPE_WEBSITE
        );
    }

    public function getOrderConfirmUrl()
    {
        if ($id = $this->getRequest()->getParam('id', false)) {
            return $this->getUrl('rbsales/order/confirm', ['id' => $id, 'tab' => $this->_getTab()]);
        }
    }

    /**
     * @param object $order
     * @return string
     */
    public function getPrintUrl($orderId)
    {
        return $this->getUrl(
            'rbsales/order/print',
            ['id' => $orderId]
        );
    }
}
