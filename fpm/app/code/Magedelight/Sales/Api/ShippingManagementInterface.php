<?php

namespace Magedelight\Sales\Api;

/**
 * @api
 */
interface ShippingManagementInterface
{

    /**
     * Get Packing Slip for Vendor
     *
     * @param int $vendorOrderId
     * @return \Magedelight\Sales\Api\Data\FileDownloadInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function generatePackingSlip($vendorOrderId);

    /**
     * Get Manifest Slip for Vendor
     *
     * @param int $vendorOrderId
     * @return \Magedelight\Sales\Api\Data\FileDownloadInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function generateManifest($vendorOrderId);
}
