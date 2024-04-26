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
namespace Magedelight\Sales\Observer\Sales;

use Magento\Framework\Event\ObserverInterface;
use Magedelight\Sales\Model\Order as VendorOrder;

class OrderShipmentTrackSaveAfter implements ObserverInterface
{

    protected $_vendorOrder;

    /**
     *
     * @param \Magedelight\Sales\Model\OrderFactory $vendorOrder
     */
    public function __construct(
        \Magedelight\Sales\Model\OrderFactory $vendorOrder
    ) {
        $this->_vendorOrder = $vendorOrder->create();
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $track = $observer->getEvent()->getTrack();
        $shipment = $track->getShipment();
        $shippingMethod = $shipment->getOrder()->getShippingMethod();
        if (!$shippingMethod || !$track->getNumber()) {
            return;
        }

        try {
            $vendorOrder = $this->_vendorOrder->getByOriginOrderId($shipment->getOrderId(), $shipment->getVendorId());
            $_vendorOrder = $this->_vendorOrder->load($vendorOrder->getId());
            $_vendorOrder->setStatus(VendorOrder::STATUS_SHIPPED)->save();
        } catch (\Exception $ex) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('We can\'t save the invoice right now.')
            );
        }
        return;
    }
}
