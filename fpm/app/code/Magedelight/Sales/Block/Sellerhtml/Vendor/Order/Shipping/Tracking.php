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
namespace Magedelight\Sales\Block\Sellerhtml\Vendor\Order\Shipping;

class Tracking extends \Magento\Framework\View\Element\Template
{

    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $_orderFactory;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Item\CollectionFactory
     */
    protected $_orderItemCollectionFactory;

    /**
     * @var \Magedelight\Backend\Model\Auth\Session
     */
    protected $authSession;

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var \Magento\Shipping\Model\Config
     */
    protected $_shippingConfig;

    /**
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Shipping\Model\Config $shippingConfig
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Sales\Model\ResourceModel\Order\Item\CollectionFactory $orderItemCollectionFactory
     * @param \Magedelight\Backend\Model\Auth\Session $authSession
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Shipping\Model\Config $shippingConfig,
        \Magento\Framework\Registry $registry,
        \Magento\Sales\Model\ResourceModel\Order\Item\CollectionFactory $orderItemCollectionFactory,
        \Magedelight\Backend\Model\Auth\Session $authSession,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        array $data = []
    ) {
        $this->_shippingConfig = $shippingConfig;
        $this->_coreRegistry = $registry;
        $this->authSession = $authSession;
        parent::__construct($context, $data);
        $this->_orderItemCollectionFactory = $orderItemCollectionFactory;
        $this->_orderFactory = $orderFactory;
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
     * Retrieve carriers
     *
     * @return array
     */
    public function getCarriers()
    {
        $carriers = [];
        $carrierInstances = $this->_getCarriersInstances();
        $carriers['custom'] = __('Custom Value');
        foreach ($carrierInstances as $code => $carrier) {
            if ($carrier->isTrackingAvailable()) {
                $carriers[$code] = $carrier->getConfigData('title');
            }
        }
        return $carriers;
    }

    /**
     * @return array
     */
    protected function _getCarriersInstances()
    {
        $tStoreId = 0;
        return $this->_shippingConfig->getAllCarriers($tStoreId);
    }

    public function getOrderId()
    {
        return $this->getOrderData()->getEntityId();
    }

    public function getQtyOrdered()
    {
        try {
            $qty = 0;
            $collection = $this->_orderItemCollectionFactory->create()
                    ->setOrderFilter($this->getOrderData());
            $collection->addFieldToFilter('vendor_id', $this->getVendorId());
            foreach ($collection as $item) {
                $qty += $item->getQtyOrdered();
            }
        } catch (\Exception $e) {
            throw new \Exception($e->getTraceAsString());
        }

        return $qty;
    }

    public function getVendorOrderId()
    {
        return $this->getOrderData()->getVendorOrderId();
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

    public function getVendorId()
    {
        if (!($vendorId = $this->authSession->getUser()->getVendorId())) {
            return false;
        }
        return $vendorId; // = 8;
    }

    /**
     *
     * @return string
     */
    public function getBackUrl()
    {
        return $this->getUrl('rbsales/order/index', ['tab' => '2,0', 'sfrm' => 'confirmed']);
    }
}
