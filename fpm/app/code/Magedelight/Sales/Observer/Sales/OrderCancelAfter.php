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

use Magedelight\Sales\Model\Config\Source\Order\CancelledBy;
use Magento\Customer\Model\Session;
use Magento\Framework\Event\ObserverInterface;

class OrderCancelAfter implements ObserverInterface
{
    /**
     * @var \Magedelight\Sales\Model\ResourceModel\Order\CollectionFactory
     */
    protected $vendorOrderCollectionFactory;

    /**
     * @var Session
     */
    protected $session;

    /**
     * OrderCancelAfter constructor.
     * @param \Magedelight\Sales\Model\ResourceModel\Order\CollectionFactory $vendorOrderCollectionFactory
     * @param Session $customerSession
     */
    public function __construct(
        \Magedelight\Sales\Model\ResourceModel\Order\CollectionFactory $vendorOrderCollectionFactory,
        Session $customerSession
    ) {
        $this->session = $customerSession;
        $this->vendorOrderCollectionFactory = $vendorOrderCollectionFactory;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /**
         * @var \Magento\Sales\Model\Order $order
         */
        $order = $observer->getEvent()->getOrder();
        $connection = $order->getResource()->getConnection();

        $vendorOrders = $this->vendorOrderCollectionFactory->create()
                ->addFieldToFilter("order_id", $order->getId());
        if (!empty($vendorOrders->getAllIds())) {
            foreach ($vendorOrders as $vendorOrder) {
                if ($this->session->isLoggedIn()) {
                    $vendorOrder->setData('cancelled_by', CancelledBy::BUYER);
                }
                else {
					$vendorOrder->setData('cancelled_by', CancelledBy::MERCHANT);
				}
                $vendorOrder
                        ->setData('is_confirmed', 0)
                        ->setData('po_generated', 0)
                        ->registerCancel($order, true)
                        ->save();
            }
            $tableName = $connection->getTableName(
                \Magedelight\Commissions\Model\Commission\Payment::VENDOR_PAYMENTS_TABLE
            );
            $sql = "Delete FROM " . $tableName . " WHERE vendor_order_id IN ("
                . implode(',', $vendorOrders->getAllIds()) . ")";
            $connection->query($sql);
        }
    }
}
