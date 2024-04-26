<?php

namespace Magedelight\Sales\Api\Data;

/**
 * Vendor Order interface.
 * @api
 */
interface OrderDataInterface
{
    
    const VENDOR_ID = 'vendor_id';
    
    const VENDOR_ORDER_ID = 'vendor_order_id';
    
    const VENDOR_NAME = 'vendor_name';

    const VAT = 'vat';
    
    const ADDRESS = 'address';
   
     /**
      * Get Entity Id
      *
      * @return int
      */
    public function getVendorId();

    /**
     * Set Vendor Id
     * @param int $vendorId
     * @return $this
     */
    public function setVendorId(int $vendorId);

    /**
     * Get Vendor Name
     *
     * @return string
     */
    public function getVendorName();

    /**
     * Set Vendor Order Id
     * @param string $vendorName
     * @return $this
     */
    public function setVendorName(string $vendorOrderId);

    /**
     * Set Vendor Order Id
     * @param int|NULL $vendorOrderId
     * @return $this
     */
    public function setVendorOrderId($vendorOrderId);

     /**
      * Get Entity Id
      *
      * @return int|NULL
      */
    public function getVendorOrderId();

    /**
     * @return string|null
     */
    public function getVat();

    /**
     * @param string $vat
     * @return $this
     */
    public function setVat($vat);
    
    /**
     * @return string|null
     */
    public function getAddress();

    /**
     * @param string $address
     * @return $this
     */
    public function setAddress($address);
}
