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

class OrderAdminConfirm implements ObserverInterface
{

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    private $dateTime;

    /**
     * @var \Magedelight\Sales\Model\Order
     */
    private $vendorOrder;
    
    protected $orderLogFactory;
    
     protected $vendorOrderRepository;


    public function __construct(
        \Magedelight\Sales\Model\OrderFactory $vendorOrder,
        \MDC\Sales\Model\OrderLogFactory $orderLogFactory,
        \Magedelight\Sales\Api\OrderRepositoryInterface $vendorOrderRepository,
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime
    ) {
        $this->vendorOrder = $vendorOrder->create();
        $this->dateTime = $dateTime;
        $this->orderLogFactory = $orderLogFactory;
        $this->vendorOrderRepository = $vendorOrderRepository;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /**
         * @var \Magento\Sales\Model\Order $order
         */
        $order = $observer->getOrder();
        $orderWebsiteId = $observer->getWebsiteId();
        if ($this->vendorOrder->isAutoConfirmEnabledForVendor($orderWebsiteId)) {
            $vendorResource = $this->vendorOrder->getResource();
            $vendorResource->getConnection()->update(
                $vendorResource->getMainTable(),
                [
                    'is_confirmed' => 1,
                    'confirmed_at' => $this->dateTime->gmtDate(),
                    'status' => \Magedelight\Sales\Model\Order::STATUS_CONFIRMED
                ],
                [
                    "order_id=?" => $order->getId(),
                    'status=?' => \Magedelight\Sales\Model\Order::STATUS_PENDING
                ]
            );
        }
        else {
			$vendorResource = $this->vendorOrder->getResource();
            $vendorResource->getConnection()->update(
                $vendorResource->getMainTable(),
                [
                    'status' => \Magedelight\Sales\Model\Order::STATUS_CONFIRMED
                ],
                [
                    "order_id=?" => $order->getId(),
                    'status=?' => \Magedelight\Sales\Model\Order::STATUS_PENDING
                ]
            );
		}
		
		foreach ($order->getItemsCollection() as $item) {
			if ($item->getParentItem()) {
				continue;
			} 
			$orderVendor = $this->vendorOrderRepository->getById(
                $item->getVendorOrderId()
            );
			
			$mainOrderId = explode("-", $orderVendor->getIncrementId());
			$collection = $this->orderLogFactory->create();
			$collection->setOrderId($orderVendor->getOrderId());
			$collection->setIncOrderId($mainOrderId[0]);
			$collection->setVendorOrderId($orderVendor->getVendorOrderId());
			$collection->setIncVendorOrderId($orderVendor->getIncrementId());
			$collection->setVendorId($orderVendor->getVendorId());
			$collection->setCurrentStatus(VendorOrder::STATUS_PENDING);
			$collection->setStatusChangeTo(VendorOrder::STATUS_CONFIRMED);
			$collection->save();
		}
    }
}
