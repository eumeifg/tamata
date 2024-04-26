<?php

namespace Magedelight\Sales\Model;

use \Magedelight\Sales\Api\Data\OrderDataInterface;

class OrderData extends \Magento\Framework\DataObject implements OrderDataInterface
{
     /**
      * {@inheritDoc}
      */
    public function getVendorId()
    {
        return $this->getData(OrderDataInterface::VENDOR_ID);
    }

    /**
     * {@inheritDoc}
     */
    public function setVendorId(int $vendorId)
    {
        return $this->setData(OrderDataInterface::VENDOR_ID, $vendorId);
    }

    /**
     * {@inheritDoc}
     */
    public function getVendorName()
    {
        return $this->getData(OrderDataInterface::VENDOR_NAME);
    }

    /**
     * {@inheritDoc}
     */
    public function setVendorName(string $vendorName)
    {
        return $this->setData(OrderDataInterface::VENDOR_NAME, $vendorName);
    }

    /**
      * {@inheritDoc}
      */
    public function getVendorOrderId()
    {
        return $this->getData(OrderDataInterface::VENDOR_ORDER_ID);
    }

    /**
     * {@inheritDoc}
     */
    public function setVendorOrderId($vendorOrderId)
    {
        return $this->setData(OrderDataInterface::VENDOR_ORDER_ID, $vendorOrderId);
    }

    /**
     * {@inheritDoc}
     */
    public function getVat()
    {
        return $this->getData(OrderDataInterface::VAT);
    }

    /**
     * {@inheritDoc}
     */
    public function setVat($vat)
    {
        return $this->setData(OrderDataInterface::VAT, $vat);
    }
    
    /**
     * {@inheritDoc}
     */
    public function getAddress()
    {
        return $this->getData(OrderDataInterface::ADDRESS);
    }

    /**
     * {@inheritDoc}
     */
    public function setAddress($address)
    {
        return $this->setData(OrderDataInterface::ADDRESS, $address);
    }
}
