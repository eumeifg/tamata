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

namespace Ktpl\Warehousemanagement\Api\Data;

interface WarehousemanagementInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{

    const PRICE = 'price';
    const USER_ID = 'user_id';
    const SUB_ORDER_ID = 'sub_order_id';
    const CREATED_AT = 'created_at';
    const MAIN_ORDER_ID = 'main_order_id';
    const QTY = 'qty';
    const BARCODE_NUMBER = 'barcode_number';
    const PRODUCT_NAME = 'product_name';
    const MAIN_ORDER_STATUS = 'main_order_status';
    const WAREHOUSEMANAGEMENT_ID = 'warehousemanagement_id';
    const IP_ADDRESS = 'ip_address';
    const PRODUCT_LOCATION = 'product_location';
    const SUB_ORDER_STATUS = 'sub_order_status';
    const UPDATED_AT = 'updated_at';
    const ORDER_EVENT = 'order_event';

    /**
     * Get warehousemanagement_id
     * @return string|null
     */
    public function getWarehousemanagementId();

    /**
     * Set warehousemanagement_id
     * @param string $warehousemanagementId
     * @return \Ktpl\Warehousemanagement\Api\Data\WarehousemanagementInterface
     */
    public function setWarehousemanagementId($warehousemanagementId);

    /**
     * Get main_order_id
     * @return string|null
     */
    public function getMainOrderId();

    /**
     * Set main_order_id
     * @param string $mainOrderId
     * @return \Ktpl\Warehousemanagement\Api\Data\WarehousemanagementInterface
     */
    public function setMainOrderId($mainOrderId);

    /**
     * Retrieve existing extension attributes object or create a new one.
     * @return \Ktpl\Warehousemanagement\Api\Data\WarehousemanagementExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     * @param \Ktpl\Warehousemanagement\Api\Data\WarehousemanagementExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Ktpl\Warehousemanagement\Api\Data\WarehousemanagementExtensionInterface $extensionAttributes
    );

    /**
     * Get sub_order_id
     * @return string|null
     */
    public function getSubOrderId();

    /**
     * Set sub_order_id
     * @param string $subOrderId
     * @return \Ktpl\Warehousemanagement\Api\Data\WarehousemanagementInterface
     */
    public function setSubOrderId($subOrderId);

    /**
     * Get product_name
     * @return string|null
     */
    public function getProductName();

    /**
     * Set product_name
     * @param string $productName
     * @return \Ktpl\Warehousemanagement\Api\Data\WarehousemanagementInterface
     */
    public function setProductName($productName);

    /**
     * Get price
     * @return string|null
     */
    public function getPrice();

    /**
     * Set price
     * @param string $price
     * @return \Ktpl\Warehousemanagement\Api\Data\WarehousemanagementInterface
     */
    public function setPrice($price);

    /**
     * Get qty
     * @return string|null
     */
    public function getQty();

    /**
     * Set qty
     * @param string $qty
     * @return \Ktpl\Warehousemanagement\Api\Data\WarehousemanagementInterface
     */
    public function setQty($qty);

    /**
     * Get main_order_status
     * @return string|null
     */
    public function getMainOrderStatus();

    /**
     * Set main_order_status
     * @param string $mainOrderStatus
     * @return \Ktpl\Warehousemanagement\Api\Data\WarehousemanagementInterface
     */
    public function setMainOrderStatus($mainOrderStatus);

    /**
     * Get sub_order_status
     * @return string|null
     */
    public function getSubOrderStatus();

    /**
     * Set sub_order_status
     * @param string $subOrderStatus
     * @return \Ktpl\Warehousemanagement\Api\Data\WarehousemanagementInterface
     */
    public function setSubOrderStatus($subOrderStatus);

    /**
     * Get created_at
     * @return string|null
     */
    public function getCreatedAt();

    /**
     * Set created_at
     * @param string $createdAt
     * @return \Ktpl\Warehousemanagement\Api\Data\WarehousemanagementInterface
     */
    public function setCreatedAt($createdAt);

    /**
     * Get updated_at
     * @return string|null
     */
    public function getUpdatedAt();

    /**
     * Set updated_at
     * @param string $updatedAt
     * @return \Ktpl\Warehousemanagement\Api\Data\WarehousemanagementInterface
     */
    public function setUpdatedAt($updatedAt);

    /**
     * Get user_id
     * @return string|null
     */
    public function getUserId();

    /**
     * Set user_id
     * @param string $userId
     * @return \Ktpl\Warehousemanagement\Api\Data\WarehousemanagementInterface
     */
    public function setUserId($userId);

    /**
     * Get ip_address
     * @return string|null
     */
    public function getIpAddress();

    /**
     * Set ip_address
     * @param string $ipAddress
     * @return \Ktpl\Warehousemanagement\Api\Data\WarehousemanagementInterface
     */
    public function setIpAddress($ipAddress);

    /**
     * Get barcode_number
     * @return string|null
     */
    public function getBarcodeNumber();

    /**
     * Set barcode_number
     * @param string $barcodeNumber
     * @return \Ktpl\Warehousemanagement\Api\Data\WarehousemanagementInterface
     */
    public function setBarcodeNumber($barcodeNumber);

    /**
     * Get product_location
     * @return string|null
     */
    public function getProductLocation();

    /**
     * Set product_location
     * @param string $productLocation
     * @return \Ktpl\Warehousemanagement\Api\Data\WarehousemanagementInterface
     */
    public function setProductLocation($productLocation);

    /**
     * Get order_event
     * @return string|null
     */
    public function getOrderEvent();

    /**
     * Set order_event
     * @param string $orderEvent
     * @return \Ktpl\Warehousemanagement\Api\Data\WarehousemanagementInterface
     */
    public function setOrderEvent($orderEvent);
}
