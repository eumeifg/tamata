<?php

namespace Magedelight\Sales\Api;

/**
 * @api
 */
interface ShippingBuilderInterface
{

    /**
     * Prepare shipment form data for shipment generation in vendor app.
     * @param int $orderId
     * @param int $vendorOrderId
     * @return \Magedelight\Sales\Api\Data\ShipmentDataInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function createShipmentFormData($orderId, $vendorOrderId);
}
