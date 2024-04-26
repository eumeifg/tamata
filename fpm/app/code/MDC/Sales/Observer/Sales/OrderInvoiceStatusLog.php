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
namespace MDC\Sales\Observer\Sales;

use Magento\Framework\Event\ObserverInterface;
use \Magedelight\Sales\Model\Order as VendorOrder;

class OrderInvoiceStatusLog implements ObserverInterface
{

    protected $_vendorOrder;

    protected $orderLogFactory;

    /**
     *
     * @param \Magedelight\Sales\Model\OrderFactory $vendorOrder
     */
    public function __construct(
        \Magedelight\Sales\Model\OrderFactory $vendorOrder,
        \MDC\Sales\Model\OrderLogFactory $orderLogFactory
    ) {
        $this->_vendorOrder = $vendorOrder;
        $this->orderLogFactory = $orderLogFactory;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     *
     *
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {

        $order = $observer->getEvent()->getOrder();
        $mainOrderId = explode("-", $order->getIncrementId());

        $collection = $this->orderLogFactory->create();
        $collection->setOrderId($order->getOrderId());
        $collection->setIncOrderId($mainOrderId[0]);
        $collection->setVendorOrderId($order->getVendorOrderId());
        $collection->setIncVendorOrderId($order->getIncrementId());
        $collection->setVendorId($order->getVendorId());
        $collection->setCurrentStatus(VendorOrder::STATUS_PROCESSING);
        $collection->setStatusChangeTo(VendorOrder::STATUS_PACKED);
        $collection->save();
    }

}
