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

namespace Ktpl\Warehousemanagement\Api;

use Magento\Framework\Api\SearchCriteriaInterface;

interface WarehousemanagementRepositoryInterface
{

    /**
     * Save Warehousemanagement
     * @param \Ktpl\Warehousemanagement\Api\Data\WarehousemanagementInterface $warehousemanagement
     * @return \Ktpl\Warehousemanagement\Api\Data\WarehousemanagementInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(
        \Ktpl\Warehousemanagement\Api\Data\WarehousemanagementInterface $warehousemanagement
    );

    /**
     * Retrieve Warehousemanagement
     * @param string $warehousemanagementId
     * @return \Ktpl\Warehousemanagement\Api\Data\WarehousemanagementInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getById($warehousemanagementId);

    /**
     * Retrieve Warehousemanagement matching the specified criteria.
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Ktpl\Warehousemanagement\Api\Data\WarehousemanagementSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
    );

    /**
     * Delete Warehousemanagement
     * @param \Ktpl\Warehousemanagement\Api\Data\WarehousemanagementInterface $warehousemanagement
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(
        \Ktpl\Warehousemanagement\Api\Data\WarehousemanagementInterface $warehousemanagement
    );

    /**
     * Delete Warehousemanagement by ID
     * @param string $warehousemanagementId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($warehousemanagementId);
}
