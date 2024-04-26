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
namespace Magedelight\Sales\Model\ResourceModel\Order\Handler;

use Magedelight\Sales\Model\Order as VendorOrder;
use Magento\Sales\Model\Order;

class State
{

    /**
     * @var \Magento\Framework\Module\Manager
     */
    private $moduleManager;
    protected $_eventManager;
    
    public function __construct(
        \Magento\Framework\Event\Manager $manager,
        \Magento\Framework\Module\Manager $moduleManager
    ) {
        $this->_eventManager = $manager;
        $this->moduleManager = $moduleManager;
    }

    /**
     * Check vendor order status before save
     *
     * @param VendorOrder $vendorOrder
     * @return $this
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function check(VendorOrder $vendorOrder)
    {
        if ($vendorOrder->getIsNewOrder()) {
            /* Avoid check when new order is placed as we are not getting payment information here in 2.3.2 version. */
            return $this;
        }
        
        $order = $vendorOrder->getOriginalOrder();
        
        if (!$vendorOrder->getId()) {
            return $vendorOrder;
        }
        if (!$vendorOrder->isCanceled() && !$vendorOrder->canInvoice() && !$vendorOrder->canShip()) {
            if (0 == $vendorOrder->getBaseGrandTotal() || $vendorOrder->canCreditmemo()) {
//                if ($order->getShippingMethod()) {
//                    $vendorOrder->setStatus(VendorOrder::STATUS_SHIPPED);
//                } else {
//                    $vendorOrder->setStatus(VendorOrder::STATUS_COMPLETE);
//                }
                /*
                 * Add temporery till shipping module work properly

                $vendorOrder->setStatus(VendorOrder::STATUS_COMPLETE);
                 *
                 */
                $cusName = $order['customer_firstname'];
                $email = $order['customer_email'];
                $eventParams = ['email' => $email,'customer_name' => $cusName,'order' => $vendorOrder];
                $this->_eventManager->dispatch('vendor_order_status_complete', $eventParams);
            } elseif (floatval($vendorOrder->getTotalRefunded())
                || !$vendorOrder->getTotalRefunded() && $vendorOrder->hasForcedCanCreditmemo()
            ) {
                $vendorOrder->setStatus(VendorOrder::STATUS_CLOSED);
            }
        }
        $grandTotal = $order->getGrandTotal();
        /**
         * hack for store credit when order placed using store credit.
         */
        if ($this->moduleManager->isEnabled('RB_StoreCredit')) {
            $grandTotal += $order->getCreditAmount();
        }
        if ($grandTotal == $order->getTotalCanceled()) {
            $order->setState(Order::STATE_CANCELED)
                ->setStatus(VendorOrder::STATUS_CANCELED)
                ->save()
                ;
        }
        return $this;
    }
}
