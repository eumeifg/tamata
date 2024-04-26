<?php

namespace Magedelight\Sales\Api\Data;

/**
 * Vendor Shipment interface.
 * @api
 */
interface ShipmentDataInterface
{

    /**
     * Gets the items for the shipment.
     *
     * @return \Magento\Sales\Api\Data\ShipmentItemInterface[] Array of items.
     */
    public function getShipmentItems();

    /**
     * Sets the items for the shipment.
     *
     * @param \Magento\Sales\Api\Data\ShipmentItemInterface[] $items
     * @return $this
     */
    public function setShipmentItems($items);

    /**
     * Gets order
     *
     * @return \Magedelight\Sales\Api\Data\VendorOrderInterface|NULL
     */
    public function getSubOrder();

    /**
     * Sets order
     *
     * @param \Magedelight\Sales\Api\Data\VendorOrderInterface|NULL $subOrder
     * @return $this
     */
    public function setSubOrder(\Magedelight\Sales\Api\Data\VendorOrderInterface $subOrder = null);
}
