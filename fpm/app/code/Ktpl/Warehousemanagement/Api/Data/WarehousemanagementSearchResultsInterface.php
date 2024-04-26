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

interface WarehousemanagementSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{

    /**
     * Get Warehousemanagement list.
     * @return \Ktpl\Warehousemanagement\Api\Data\WarehousemanagementInterface[]
     */
    public function getItems();

    /**
     * Set main_order_id list.
     * @param \Ktpl\Warehousemanagement\Api\Data\WarehousemanagementInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
