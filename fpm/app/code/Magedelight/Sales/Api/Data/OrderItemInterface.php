<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magedelight\Sales\Api\Data;

interface OrderItemInterface extends \Magento\Sales\Api\Data\OrderItemInterface
{
    /**#@+
     * Constants for keys of data array. Identical to the name of the getter in snake case
     */
    const VENDOR_ID = 'vendor_id';

    /**
     * Gets Vendor Id
     *
     * @return int|null
     */
    public function getVendorId();

    /**
     * Set Vendor Id
     *
     * @return int|null
     */
    public function setVendorId();

    /**
     * Gets Vendor Product SKU
     *
     * @return string|NULL
     */
    public function getVendorSku();

    /**
     * Set Vendor Product SKU
     *
     * @return string|NULL
     */
    public function setVendorSku();

    /**
     * Get Status
     *
     * @return string
     */
    public function getStatus();

    /**
     * Set Status
     * @param string $status
     * @return $this
     */
    public function setStatus($status);
}
