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
namespace Magedelight\Sales\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magedelight\Sales\Model\Order as VendorOrder;
use Psr\Log\LoggerInterface;
use Magedelight\Sales\Api\OrderRepositoryInterface;

class UpdateVendorOrderStatusOnShipment implements ObserverInterface
{

    /**
     * @var OrderRepositoryInterface
     */
    protected $vendorOrderRepository;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * UpdateVendorOrderStatusOnShipment constructor.
     * @param OrderRepositoryInterface $vendorOrderRepository
     * @param LoggerInterface $logger
     */
    public function __construct(
        OrderRepositoryInterface $vendorOrderRepository,
        LoggerInterface $logger
    ) {
        $this->vendorOrderRepository = $vendorOrderRepository;
        $this->logger = $logger;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $shipment = $observer->getEvent()->getShipment();
        if($shipment->getVendorOrderId())
        {
            try{
                $vendorOrder = $this->vendorOrderRepository->getById($shipment->getVendorOrderId());

                foreach ($vendorOrder->getItems() as $item) {
                    if($item->getParentItem()){
                        continue;
                    }
                    if ($item->getQtyToShip() == 0) {
                        /* Check shipment is generate for all item quanties and not partially done. */
                        $vendorOrder->setStatus(VendorOrder::STATUS_SHIPPED);
                        $this->vendorOrderRepository->save($vendorOrder);
                    }
                }
            }catch (\Exception $exception){
                $this->logger->critical($exception);
            }
        }
    }
}
