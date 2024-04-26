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
namespace Magedelight\Sales\Model\Sales\Order;

use Magento\Framework\Serialize\Serializer\Json;
use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Sales\Api\Data\ShipmentItemCreationInterface;
use Magento\Sales\Model\Order\Item;

class ShipmentFactory extends \Magento\Sales\Model\Order\ShipmentFactory
{
    /**
     * @var Json
     */
    protected $serializer;

    public function __construct(
        \Magento\Sales\Model\Convert\OrderFactory $convertOrderFactory,
        \Magento\Sales\Model\Order\Shipment\TrackFactory $trackFactory,
        Json $serializer
    ) {
        $this->serializer = $serializer;
        parent::__construct($convertOrderFactory, $trackFactory);
    }

    public function create(\Magento\Sales\Model\Order $order, array $items = [], $tracks = null, $vendorOrderId = null)
    {
        $shipmentItems = empty($items)
            ? $this->getQuantitiesFromOrderItems($order->getItems())
            : $this->getQuantitiesFromShipmentItems($items);
        $shipment = $this->prepareItems($this->converter->toShipment($order), $order, $shipmentItems, $vendorOrderId);

        if ($tracks) {
            $shipment = $this->prepareTracks($shipment, $tracks);
        }

        return $shipment;
    }

    /**
     * Adds items to the shipment.
     *
     * @param \Magento\Sales\Api\Data\ShipmentInterface $shipment
     * @param \Magento\Sales\Model\Order $order
     * @param array $items
     * @param int $vendorOrderId
     * @return \Magento\Sales\Api\Data\ShipmentInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function prepareItems(
        \Magento\Sales\Api\Data\ShipmentInterface $shipment,
        \Magento\Sales\Model\Order $order,
        array $items = [],
        $vendorOrderId = null
    ) {
        $shipmentItems = [];
        foreach ($order->getAllItems() as $orderItem) {
            if ($this->validateItem($orderItem, $items) === false ||
                $orderItem->getData('vendor_order_id') != $vendorOrderId) {
                continue;
            }

            /** @var \Magento\Sales\Model\Order\Shipment\Item $item */

            $item = $this->converter->itemToShipmentItem($orderItem);
            if ($orderItem->getIsVirtual() || ($orderItem->getParentItemId() && !$orderItem->isShipSeparately())) {
                $item->isDeleted(true);
            }
            if ($orderItem->isDummy(true)) {
                $qty = 0;

                if (isset($items[$orderItem->getParentItemId()])) {
                    $productOptions = $orderItem->getProductOptions();

                    if (isset($productOptions['bundle_selection_attributes'])) {
                        $bundleSelectionAttributes = $this->serializer->unserialize(
                            $productOptions['bundle_selection_attributes']
                        );

                        if ($bundleSelectionAttributes) {
                            $qty = $bundleSelectionAttributes['qty'] * $items[$orderItem->getParentItemId()];
                            $qty = min($qty, $orderItem->getSimpleQtyToShip());

                            $item->setQty($this->castQty($orderItem, $qty));
                            $shipmentItems[] = $item;
                            continue;
                        } else {
                            $qty = 1;
                        }
                    }
                } else {
                    $qty = 1;
                }
            } else {
                if (isset($items[$orderItem->getId()])) {
                    $qty = min($items[$orderItem->getId()], $orderItem->getQtyToShip());
                } elseif (!count($items)) {
                    $qty = $orderItem->getQtyToShip();
                } else {
                    continue;
                }
            }

            $item->setQty($this->castQty($orderItem, $qty));
            $shipmentItems[] = $item;
        }

        return $this->setItemsToShipment($shipment, $shipmentItems);
    }

    /**
     * Set prepared items to shipment document
     *
     * @param \Magento\Sales\Api\Data\ShipmentInterface $shipment
     * @param array $shipmentItems
     * @return \Magento\Sales\Api\Data\ShipmentInterface
     */
    private function setItemsToShipment(\Magento\Sales\Api\Data\ShipmentInterface $shipment, $shipmentItems)
    {
        $totalQty = 0;

        /**
         * Verify that composite products shipped separately has children, if not -> remove from collection
         */
        /** @var \Magento\Sales\Model\Order\Shipment\Item $shipmentItem */
        foreach ($shipmentItems as $key => $shipmentItem) {
            if ($shipmentItem->getOrderItem()->getHasChildren()
                && $shipmentItem->getOrderItem()->isShipSeparately()
            ) {
                $containerId = $shipmentItem->getOrderItem()->getId();
                $childItems = array_filter($shipmentItems, function ($item) use ($containerId) {
                    return $containerId == $item->getOrderItem()->getParentItemId();
                });

                if (count($childItems) <= 0) {
                    unset($shipmentItems[$key]);
                    continue;
                }
            }
            $totalQty += $shipmentItem->getQty();
            $shipment->addItem($shipmentItem);
        }
        return $shipment->setTotalQty($totalQty);
    }

    /**
     * Validate order item before shipment
     *
     * @param Item $orderItem
     * @param array $items
     * @return bool
     */
    private function validateItem(\Magento\Sales\Model\Order\Item $orderItem, array $items)
    {
        if (!$this->canShipItem($orderItem, $items)) {
            return false;
        }

        // Remove from shipment items without qty or with qty=0
        if (!$orderItem->isDummy(true)
            && (!isset($items[$orderItem->getId()]) || $items[$orderItem->getId()] <= 0)
        ) {
            return false;
        }
        return true;
    }

    /**
     * @param Item $item
     * @param string|int|float $qty
     * @return float|int
     */
    private function castQty(\Magento\Sales\Model\Order\Item $item, $qty)
    {
        if ($item->getIsQtyDecimal()) {
            $qty = (double)$qty;
        } else {
            $qty = (int)$qty;
        }

        return $qty > 0 ? $qty : 0;
    }

    /**
     * Translate OrderItemInterface array to product id => product quantity array.
     *
     * @param OrderItemInterface[] $items
     * @return int[]
     */
    private function getQuantitiesFromOrderItems(array $items)
    {
        $shipmentItems = [];
        foreach ($items as $item) {
            if (!$item->getIsVirtual() && (!$item->getParentItem() || $item->isShipSeparately())) {
                $shipmentItems[$item->getItemId()] = $item->getQtyOrdered();
            }
        }
        return $shipmentItems;
    }

    /**
     * Translate ShipmentItemCreationInterface array to product id => product quantity array.
     *
     * @param ShipmentItemCreationInterface[] $items
     * @return int[]
     */
    private function getQuantitiesFromShipmentItems(array $items)
    {
        $shipmentItems = [];
        foreach ($items as $item) {
            $shipmentItems[$item->getOrderItemId()] = $item->getQty();
        }
        return $shipmentItems;
    }
}
