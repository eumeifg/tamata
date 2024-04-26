<?php
/**
 * A Magento 2 module that functions for Warehouse management
 * Copyright (C) 2019
 *
 * This file included in Ktpl/Warehousemanagement is licensed under OSL 3.0
 *
 * http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * Please see LICENSE.txt for the full text of the OSL 3.0 license
 */

namespace Ktpl\Warehousemanagement\Model\Data;

use Ktpl\Warehousemanagement\Api\Data\WarehousemanagementInterface;
use Magento\Framework\Api\AbstractExtensibleObject;

class Warehousemanagement extends AbstractExtensibleObject implements WarehousemanagementInterface
{

    /**
     * Get warehousemanagement_id
     * @return string|null
     */
    public function getWarehousemanagementId()
    {
        return $this->_get(self::WAREHOUSEMANAGEMENT_ID);
    }

    /**
     * Set warehousemanagement_id
     * @param string $warehousemanagementId
     * @return \Ktpl\Warehousemanagement\Api\Data\WarehousemanagementInterface
     */
    public function setWarehousemanagementId($warehousemanagementId)
    {
        return $this->setData(self::WAREHOUSEMANAGEMENT_ID, $warehousemanagementId);
    }

    /**
     * Get main_order_id
     * @return string|null
     */
    public function getMainOrderId()
    {
        return $this->_get(self::MAIN_ORDER_ID);
    }

    /**
     * Set main_order_id
     * @param string $mainOrderId
     * @return \Ktpl\Warehousemanagement\Api\Data\WarehousemanagementInterface
     */
    public function setMainOrderId($mainOrderId)
    {
        return $this->setData(self::MAIN_ORDER_ID, $mainOrderId);
    }

    /**
     * Retrieve existing extension attributes object or create a new one.
     * @return \Ktpl\Warehousemanagement\Api\Data\WarehousemanagementExtensionInterface|null
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * Set an extension attributes object.
     * @param \Ktpl\Warehousemanagement\Api\Data\WarehousemanagementExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Ktpl\Warehousemanagement\Api\Data\WarehousemanagementExtensionInterface $extensionAttributes
    ) {
        return $this->_setExtensionAttributes($extensionAttributes);
    }

    /**
     * Get sub_order_id
     * @return string|null
     */
    public function getSubOrderId()
    {
        return $this->_get(self::SUB_ORDER_ID);
    }

    /**
     * Set sub_order_id
     * @param string $subOrderId
     * @return \Ktpl\Warehousemanagement\Api\Data\WarehousemanagementInterface
     */
    public function setSubOrderId($subOrderId)
    {
        return $this->setData(self::SUB_ORDER_ID, $subOrderId);
    }

    /**
     * Get product_name
     * @return string|null
     */
    public function getProductName()
    {
        return $this->_get(self::PRODUCT_NAME);
    }

    /**
     * Set product_name
     * @param string $productName
     * @return \Ktpl\Warehousemanagement\Api\Data\WarehousemanagementInterface
     */
    public function setProductName($productName)
    {
        return $this->setData(self::PRODUCT_NAME, $productName);
    }

    /**
     * Get price
     * @return string|null
     */
    public function getPrice()
    {
        return $this->_get(self::PRICE);
    }

    /**
     * Set price
     * @param string $price
     * @return \Ktpl\Warehousemanagement\Api\Data\WarehousemanagementInterface
     */
    public function setPrice($price)
    {
        return $this->setData(self::PRICE, $price);
    }

    /**
     * Get qty
     * @return string|null
     */
    public function getQty()
    {
        return $this->_get(self::QTY);
    }

    /**
     * Set qty
     * @param string $qty
     * @return \Ktpl\Warehousemanagement\Api\Data\WarehousemanagementInterface
     */
    public function setQty($qty)
    {
        return $this->setData(self::QTY, $qty);
    }

    /**
     * Get main_order_status
     * @return string|null
     */
    public function getMainOrderStatus()
    {
        return $this->_get(self::MAIN_ORDER_STATUS);
    }

    /**
     * Set main_order_status
     * @param string $mainOrderStatus
     * @return \Ktpl\Warehousemanagement\Api\Data\WarehousemanagementInterface
     */
    public function setMainOrderStatus($mainOrderStatus)
    {
        return $this->setData(self::MAIN_ORDER_STATUS, $mainOrderStatus);
    }

    /**
     * Get sub_order_status
     * @return string|null
     */
    public function getSubOrderStatus()
    {
        return $this->_get(self::SUB_ORDER_STATUS);
    }

    /**
     * Set sub_order_status
     * @param string $subOrderStatus
     * @return \Ktpl\Warehousemanagement\Api\Data\WarehousemanagementInterface
     */
    public function setSubOrderStatus($subOrderStatus)
    {
        return $this->setData(self::SUB_ORDER_STATUS, $subOrderStatus);
    }

    /**
     * Get created_at
     * @return string|null
     */
    public function getCreatedAt()
    {
        return $this->_get(self::CREATED_AT);
    }

    /**
     * Set created_at
     * @param string $createdAt
     * @return \Ktpl\Warehousemanagement\Api\Data\WarehousemanagementInterface
     */
    public function setCreatedAt($createdAt)
    {
        return $this->setData(self::CREATED_AT, $createdAt);
    }

    /**
     * Get updated_at
     * @return string|null
     */
    public function getUpdatedAt()
    {
        return $this->_get(self::UPDATED_AT);
    }

    /**
     * Set updated_at
     * @param string $updatedAt
     * @return \Ktpl\Warehousemanagement\Api\Data\WarehousemanagementInterface
     */
    public function setUpdatedAt($updatedAt)
    {
        return $this->setData(self::UPDATED_AT, $updatedAt);
    }

    /**
     * Get user_id
     * @return string|null
     */
    public function getUserId()
    {
        return $this->_get(self::USER_ID);
    }

    /**
     * Set user_id
     * @param string $userId
     * @return \Ktpl\Warehousemanagement\Api\Data\WarehousemanagementInterface
     */
    public function setUserId($userId)
    {
        return $this->setData(self::USER_ID, $userId);
    }

    /**
     * Get ip_address
     * @return string|null
     */
    public function getIpAddress()
    {
        return $this->_get(self::IP_ADDRESS);
    }

    /**
     * Set ip_address
     * @param string $ipAddress
     * @return \Ktpl\Warehousemanagement\Api\Data\WarehousemanagementInterface
     */
    public function setIpAddress($ipAddress)
    {
        return $this->setData(self::IP_ADDRESS, $ipAddress);
    }

    /**
     * Get barcode_number
     * @return string|null
     */
    public function getBarcodeNumber()
    {
        return $this->_get(self::BARCODE_NUMBER);
    }

    /**
     * Set barcode_number
     * @param string $barcodeNumber
     * @return \Ktpl\Warehousemanagement\Api\Data\WarehousemanagementInterface
     */
    public function setBarcodeNumber($barcodeNumber)
    {
        return $this->setData(self::BARCODE_NUMBER, $barcodeNumber);
    }

    /**
     * Get product_location
     * @return string|null
     */
    public function getProductLocation()
    {
        return $this->_get(self::PRODUCT_LOCATION);
    }

    /**
     * Set product_location
     * @param string $productLocation
     * @return \Ktpl\Warehousemanagement\Api\Data\WarehousemanagementInterface
     */
    public function setProductLocation($productLocation)
    {
        return $this->setData(self::PRODUCT_LOCATION, $productLocation);
    }

    /**
     * Get order_event
     * @return string|null
     */
    public function getOrderEvent()
    {
        return $this->_get(self::ORDER_EVENT);
    }

    /**
     * Set order_event
     * @param string $orderEvent
     * @return \Ktpl\Warehousemanagement\Api\Data\WarehousemanagementInterface
     */
    public function setOrderEvent($orderEvent)
    {
        return $this->setData(self::ORDER_EVENT, $orderEvent);
    }
}
