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
namespace Magedelight\Sales\Model;

use Magedelight\Sales\Api\ShippingBuilderInterface;
use Magedelight\Sales\Api\Data\ShipmentDataInterfaceFactory;
use Magedelight\Sales\Api\Data\CustomMessageInterface;
use Magedelight\Sales\Model\Order\ShipmentLoader;
use Psr\Log\LoggerInterface;
use Magedelight\Sales\Api\OrderRepositoryInterface;

class ShippingBuilder implements ShippingBuilderInterface
{
    /**
     * @var CustomMessageInterface
     */
    protected $customMessageInterface;

    /**
     * @var ShipmentDataInterfaceFactory
     */
    protected $shipmentDataInterfaceFactory;

    /**
     * @var ShipmentLoader
     */
    protected $shipmentLoader;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var OrderRepositoryInterface
     */
    protected $vendorOrderRepository;

    /**
     * ShippingBuilder constructor.
     * @param CustomMessageInterface $customMessageInterface
     * @param ShipmentDataInterfaceFactory $shipmentDataInterfaceFactory
     * @param ShipmentLoader $shipmentLoader
     * @param LoggerInterface $logger
     * @param OrderRepositoryInterface $vendorOrderRepository
     */
    public function __construct(
        CustomMessageInterface $customMessageInterface,
        ShipmentDataInterfaceFactory $shipmentDataInterfaceFactory,
        ShipmentLoader $shipmentLoader,
        LoggerInterface $logger,
        OrderRepositoryInterface $vendorOrderRepository
    ) {
        $this->customMessageInterface = $customMessageInterface;
        $this->shipmentDataInterfaceFactory = $shipmentDataInterfaceFactory;
        $this->shipmentLoader = $shipmentLoader;
        $this->logger = $logger;
        $this->vendorOrderRepository = $vendorOrderRepository;
    }
    
    /**
     * {@inheritdoc}
     */
    public function createShipmentFormData($orderId, $vendorOrderId)
    {
        try {
            $this->shipmentLoader->setOrderId($orderId);
            $shipment = $this->shipmentLoader->load($vendorOrderId);

            if ($shipment instanceof CustomMessageInterface) {
                $this->customMessageInterface->setMessage($shipment->getMessage());
                return $this->customMessageInterface->setStatus($shipment->getStatus());
            } else {
                $shipmentData = $this->shipmentDataInterfaceFactory->create();
                $subOrder = $this->vendorOrderRepository->getById(
                    $vendorOrderId
                );
                if ($subOrder) {
                    $shipmentData->setSubOrder($subOrder);
                }
                $shipmentData->setShipmentItems($shipment->getItems());
                return $shipmentData;
            }
        } catch (\Exception $e) {
            $this->logger->critical($e);
            $this->customMessageInterface->setMessage(__('Shipment cannot be generated.'));
            return $this->customMessageInterface->setStatus(false);
        }
    }
}
