<?php

namespace Magedelight\Sales\Model;

use Magedelight\Sales\Api\Data\ShipmentDataInterface;
use Magedelight\Sales\Api\Data\VendorOrderInterface;
use Magento\Sales\Api\Data\OrderInterface;

class ShipmentData extends \Magento\Framework\DataObject implements ShipmentDataInterface
{

    /**
     * {@inheritDoc}
     */
    public function getShipmentItems()
    {
        return $this->getData('shipment_items');
    }

    /**
     * {@inheritDoc}
     */
    public function setShipmentItems($items)
    {
        return $this->setData('shipment_items', $items);
    }

    /**
     * @inheritdoc
     */
    public function getSubOrder()
    {
        return $this->getData('sub_order');
    }

    /**
     * @inheritdoc
     */
    public function setSubOrder(\Magedelight\Sales\Api\Data\VendorOrderInterface $subOrder = null)
    {
        return $this->setData('sub_order', $subOrder);
    }
}
