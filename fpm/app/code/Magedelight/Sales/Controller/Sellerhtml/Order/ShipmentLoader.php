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
namespace Magedelight\Sales\Controller\Sellerhtml\Order;

use Magento\Framework\DataObject;
use Magento\Sales\Api\Data\ShipmentItemCreationInterface;
use Magento\Sales\Api\Data\ShipmentItemCreationInterfaceFactory;

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
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Sales\Api\ShipmentRepositoryInterface
     */
    protected $shipmentRepository;

    /**
     * @var \Magedelight\Sales\Model\Sales\Order\ShipmentFactory
     */
    protected $shipmentFactory;

    /**
     * @var \Magento\Sales\Model\Order\Shipment\TrackFactory
     */
    protected $trackFactory;

    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    protected $orderRepository;
    
    /**
     * @var \Magedelight\Backend\Model\Auth
     */
    protected $_auth;
    /**
     * @var ShipmentItemCreationInterfaceFactory
     */
    private $itemFactory;

    /**
     *
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Sales\Api\ShipmentRepositoryInterface $shipmentRepository
     * @param \Magedelight\Sales\Model\Sales\Order\ShipmentFactory $shipmentFactory
     * @param \Magento\Sales\Model\Order\Shipment\TrackFactory $trackFactory
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     * @param \Magedelight\Backend\App\Action\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Registry $registry,
        \Magento\Sales\Api\ShipmentRepositoryInterface $shipmentRepository,
        \Magedelight\Sales\Model\Sales\Order\ShipmentFactory $shipmentFactory,
        \Magento\Sales\Model\Order\Shipment\TrackFactory $trackFactory,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magedelight\Backend\App\Action\Context $context,
        ShipmentItemCreationInterfaceFactory $itemFactory,
        array $data = []
    ) {
        $this->messageManager = $context->getMessageManager();
        $this->registry = $registry;
        $this->shipmentRepository = $shipmentRepository;
        $this->shipmentFactory = $shipmentFactory;
        $this->trackFactory = $trackFactory;
        $this->orderRepository = $orderRepository;
        $this->_auth = $context->getAuth();
        $this->itemFactory = $itemFactory;
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
    public function load($vendorOrderId = null)
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
                $this->messageManager->addErrorMessage(__('The order no longer exists.'));
                return false;
            }
            /**
             * Check shipment is available to create separate from invoice
             */
            if ($order->getForcedShipmentWithInvoice()) {
                $this->messageManager->addErrorMessage(__('Cannot do shipment for the order separately from invoice.'));
                return false;
            }
            /**
             * Check shipment create availability
             */
            if (!$order->canShip()) {
                $this->messageManager->addErrorMessage(__('Cannot do shipment for the order.'));
                return false;
            }

            $shipmentItems = $this->getShipmentItems($this->getShipment());

            $shipment = $this->shipmentFactory->create(
                $order,
                $shipmentItems,
                $this->getTracking(),
                $vendorOrderId
            );
        }

        $this->registry->register('current_shipment', $shipment);
        return $shipment;
    }

    /**
     * Extract product id => product quantity array from shipment data.
     *
     * @param array $shipmentData
     * @return int[]
     */
    private function getShipmentItems(array $shipmentData)
    {
        $shipmentItems = [];
        $itemQty = isset($shipmentData['items']) ? $shipmentData['items'] : [];
        foreach ($itemQty as $itemId => $quantity) {
            /** @var ShipmentItemCreationInterface $item */
            $item = $this->itemFactory->create();
            $item->setOrderItemId($itemId);
            $item->setQty($quantity);
            $shipmentItems[] = $item;
        }
        return $shipmentItems;
    }
}
