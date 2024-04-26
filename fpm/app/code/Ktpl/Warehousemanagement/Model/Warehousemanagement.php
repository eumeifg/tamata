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

namespace Ktpl\Warehousemanagement\Model;

use Magento\Framework\Api\DataObjectHelper;
use Ktpl\Warehousemanagement\Api\Data\WarehousemanagementInterface;
use Ktpl\Warehousemanagement\Api\Data\WarehousemanagementInterfaceFactory;

class Warehousemanagement extends \Magento\Framework\Model\AbstractModel
{

    const IN_WAREHOUSE = 0;
    const OUT_OF_WAREHOUSE = 1;
    
    protected $warehousemanagementDataFactory;
    protected $_eventPrefix = 'ktpl_warehousemanagement_warehousemanagement';
    protected $dataObjectHelper;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param WarehousemanagementInterfaceFactory $warehousemanagementDataFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param \Ktpl\Warehousemanagement\Model\ResourceModel\Warehousemanagement $resource
     * @param \Ktpl\Warehousemanagement\Model\ResourceModel\Warehousemanagement\Collection $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        WarehousemanagementInterfaceFactory $warehousemanagementDataFactory,
        DataObjectHelper $dataObjectHelper,
        \Ktpl\Warehousemanagement\Model\ResourceModel\Warehousemanagement $resource,
        \Ktpl\Warehousemanagement\Model\ResourceModel\Warehousemanagement\Collection $resourceCollection,
        array $data = []
    ) {
        $this->warehousemanagementDataFactory = $warehousemanagementDataFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Retrieve warehousemanagement model with warehousemanagement data
     * @return WarehousemanagementInterface
     */
    public function getDataModel()
    {
        $warehousemanagementData = $this->getData();

        $warehousemanagementDataObject = $this->warehousemanagementDataFactory->create();
        $this->dataObjectHelper->populateWithArray(
            $warehousemanagementDataObject,
            $warehousemanagementData,
            WarehousemanagementInterface::class
        );

        return $warehousemanagementDataObject;
    }
}
