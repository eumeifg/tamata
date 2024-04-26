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
use \Magedelight\Sales\Model\Order as VendorOrder;

class OrderAutoConfirm implements ObserverInterface
{

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    private $eventManager;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    private $dateTime;

    public function __construct(
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime,
        \Magento\Framework\Event\ManagerInterface $eventManager
    ) {
        $this->dateTime = $dateTime;
        $this->eventManager = $eventManager;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /**
         * @var \Magedelight\Sales\Model\Order $vendorOrder
         */
        $vendorOrder = $observer->getVendorOrder();
        if ($vendorOrder->isAutoConfirmEnabledForVendor()) {
            $vendorOrder->setData("is_confirmed", 1)
                ->setData('confirmed_at', $this->dateTime->gmtDate())
                ->setData('status', VendorOrder::STATUS_PROCESSING)
                ->save();
            $this->eventManager->dispatch('vendor_orders_confirm_after', ['vendor_order' => $vendorOrder]);
        }
    }
}
