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
namespace Magedelight\Sales\Model\Order;

use Magedelight\Sales\Api\Data\CustomMessageInterface;
use Magento\Framework\DataObject;

/**
 * @method ShipmentLoader setOrderId($id)
 * @method ShipmentLoader setShipmentId($id)
 * @method ShipmentLoader setShipment($shipment)
 * @method ShipmentLoader setTracking($tracking)
 * @method int getOrderId()
 * @method int getShipmentId()
 * @method array getShipment()
 * @method array getTracking()
 */
class ShipmentLoader extends DataObject
{
    /**
     * @var \Magento\Sales\Api\ShipmentRepositoryInterface
     */
    protected $shipmentRepository;

    /**
     * @var \Magedelight\Sales\Model\Sales\Order\ShipmentFactory
     */
    protected $shipmentFactory;

    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @var CustomMessageInterface
     */
    protected $customMessageInterface;

    /**
     * ShipmentLoader constructor.
     * @param \Magento\Sales\Api\ShipmentRepositoryInterface $shipmentRepository
     * @param \Magedelight\Sales\Model\Sales\Order\ShipmentFactory $shipmentFactory
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     * @param CustomMessageInterface $customMessageInterface
     * @param array $data
     */
    public function __construct(
        \Magento\Sales\Api\ShipmentRepositoryInterface $shipmentRepository,
        \Magedelight\Sales\Model\Sales\Order\ShipmentFactory $shipmentFactory,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        CustomMessageInterface $customMessageInterface,
        array $data = []
    ) {
        $this->shipmentRepository = $shipmentRepository;
        $this->shipmentFactory = $shipmentFactory;
        $this->orderRepository = $orderRepository;
        $this->customMessageInterface = $customMessageInterface;
        parent::__construct($data);
    }

    /**
     * Initialize shipment items QTY
     *
     * @return array
     */
    protected function getItemQtys()
    {
        $data = $this->getShipment();
        return isset($data['items']) ? $data['items'] : [];
    }

    /**
     * Initialize shipment model instance
     *
     * @return bool|\Magento\Sales\Model\Order\Shipment
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function load($vendorOrderId)
    {
        $shipment = false;
        $orderId = $this->getOrderId();
        $shipmentId = $this->getShipmentId();
        if ($shipmentId) {
            $shipment = $this->shipmentRepository->get($shipmentId);
        } elseif ($orderId) {
            $order = $this->orderRepository->get($orderId);
            /**
             * Check order existing
             */
            if (!$order->getId()) {
                $this->customMessageInterface->setMessage(__('The order no longer exists.'));
                return $this->customMessageInterface->setStatus(false);
            }
            /**
             * Check shipment is available to create separate from invoice
             */
            if ($order->getForcedShipmentWithInvoice()) {
                $this->customMessageInterface->setMessage(
                    __('Cannot do shipment for the order separately from invoice.')
                );
                return $this->customMessageInterface->setStatus(false);
            }
            /**
             * Check shipment create availability
             */
            if (!$order->canShip()) {
                $this->customMessageInterface->setMessage(__('Cannot do shipment for the order.'));
                return $this->customMessageInterface->setStatus(false);
            }
            $shipment = $this->shipmentFactory->create(
                $order,
                $this->getItemQtys(),
                $this->getTracking(),
                $vendorOrderId
            );
        }
        return $shipment->setOrder($order);
    }
}
