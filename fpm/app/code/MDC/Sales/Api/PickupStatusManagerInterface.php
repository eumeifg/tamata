<?php
namespace MDC\Sales\Api;

/**
 * @api
 */
interface PickupStatusManagerInterface
{
    /**
     * @param int $vendorOrderId
     * @param int $pickupStatus
     * @param string $barcodeNumber
     * @return \Magedelight\Sales\Api\Data\CustomMessageInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function updateStatus($vendorOrderId, $pickupStatus,$barcodeNumber = null);

    /**
     * @param string $vendorSku
     * @return \Magedelight\Catalog\Api\Data\ProductRequestInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function displayVendorProductDetail($vendorSku);

}
