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

class OrderAdminMassConfirm implements ObserverInterface
{

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    private $dateTime;

    /**
     * @var \Magedelight\Sales\Model\Order
     */
    private $vendorOrder;

    public function __construct(
        \Magedelight\Sales\Model\OrderFactory $vendorOrder,
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime
    ) {
        $this->vendorOrder = $vendorOrder->create();
        $this->dateTime = $dateTime;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $orderIds = $observer->getOrderIds();
        if ($this->vendorOrder->isAutoConfirmEnabledForVendor()) {
            $vendorResource = $this->vendorOrder->getResource();
            $vendorResource->getConnection()->update(
                $vendorResource->getMainTable(),
                [
                    'is_confirmed' => 1,
                    'confirmed_at' => $this->dateTime->gmtDate(),
                    'status' => \Magedelight\Sales\Model\Order::STATUS_CONFIRMED
                ],
                [
                    'is_confirmed = ?' => 0,
                    'order_id in(?)' => $orderIds
                ]
            );
        }
    }
}
