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
use Ktpl\Warehousemanagement\Api\Data\WarehousemanagementInterfaceFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Ktpl\Warehousemanagement\Model\ResourceModel\Warehousemanagement as ResourceWarehousemanagement;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Reflection\DataObjectProcessor;
use Ktpl\Warehousemanagement\Api\Data\WarehousemanagementSearchResultsInterfaceFactory;
use Ktpl\Warehousemanagement\Api\WarehousemanagementRepositoryInterface;
use Ktpl\Warehousemanagement\Model\ResourceModel\Warehousemanagement\CollectionFactory as WarehousemanagementCollectionFactory;
use Magento\Framework\Api\ExtensibleDataObjectConverter;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface;

class WarehousemanagementRepository implements WarehousemanagementRepositoryInterface
{

    private $storeManager;

    protected $extensibleDataObjectConverter;
    protected $extensionAttributesJoinProcessor;

    protected $dataObjectProcessor;

    protected $warehousemanagementFactory;

    private $collectionProcessor;

    protected $warehousemanagementCollectionFactory;

    protected $resource;

    protected $searchResultsFactory;

    protected $dataObjectHelper;

    protected $dataWarehousemanagementFactory;

    /**
     * @param ResourceWarehousemanagement $resource
     * @param WarehousemanagementFactory $warehousemanagementFactory
     * @param WarehousemanagementInterfaceFactory $dataWarehousemanagementFactory
     * @param WarehousemanagementCollectionFactory $warehousemanagementCollectionFactory
     * @param WarehousemanagementSearchResultsInterfaceFactory $searchResultsFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param DataObjectProcessor $dataObjectProcessor
     * @param StoreManagerInterface $storeManager
     * @param CollectionProcessorInterface $collectionProcessor
     * @param JoinProcessorInterface $extensionAttributesJoinProcessor
     * @param ExtensibleDataObjectConverter $extensibleDataObjectConverter
     */
    public function __construct(
        ResourceWarehousemanagement $resource,
        WarehousemanagementFactory $warehousemanagementFactory,
        WarehousemanagementInterfaceFactory $dataWarehousemanagementFactory,
        WarehousemanagementCollectionFactory $warehousemanagementCollectionFactory,
        WarehousemanagementSearchResultsInterfaceFactory $searchResultsFactory,
        DataObjectHelper $dataObjectHelper,
        DataObjectProcessor $dataObjectProcessor,
        StoreManagerInterface $storeManager,
        CollectionProcessorInterface $collectionProcessor,
        JoinProcessorInterface $extensionAttributesJoinProcessor,
        ExtensibleDataObjectConverter $extensibleDataObjectConverter
    ) {
        $this->resource = $resource;
        $this->warehousemanagementFactory = $warehousemanagementFactory;
        $this->warehousemanagementCollectionFactory = $warehousemanagementCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->dataWarehousemanagementFactory = $dataWarehousemanagementFactory;
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->storeManager = $storeManager;
        $this->collectionProcessor = $collectionProcessor;
        $this->extensionAttributesJoinProcessor = $extensionAttributesJoinProcessor;
        $this->extensibleDataObjectConverter = $extensibleDataObjectConverter;
    }

    /**
     * {@inheritdoc}
     */
    public function save(
        \Ktpl\Warehousemanagement\Api\Data\WarehousemanagementInterface $warehousemanagement
    ) {
        /* if (empty($warehousemanagement->getStoreId())) {
            $storeId = $this->storeManager->getStore()->getId();
            $warehousemanagement->setStoreId($storeId);
        } */

        $warehousemanagementData = $this->extensibleDataObjectConverter->toNestedArray(
            $warehousemanagement,
            [],
            \Ktpl\Warehousemanagement\Api\Data\WarehousemanagementInterface::class
        );

        $warehousemanagementModel = $this->warehousemanagementFactory->create()->setData($warehousemanagementData);

        try {
            $this->resource->save($warehousemanagementModel);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__(
                'Could not save the warehousemanagement: %1',
                $exception->getMessage()
            ));
        }
        return $warehousemanagementModel->getDataModel();
    }

    /**
     * {@inheritdoc}
     */
    public function getById($warehousemanagementId)
    {
        $warehousemanagement = $this->warehousemanagementFactory->create();
        $this->resource->load($warehousemanagement, $warehousemanagementId);
        if (!$warehousemanagement->getId()) {
            throw new NoSuchEntityException(__('Warehousemanagement with id "%1" does not exist.', $warehousemanagementId));
        }
        return $warehousemanagement->getDataModel();
    }

    /**
     * {@inheritdoc}
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $criteria
    ) {
        $collection = $this->warehousemanagementCollectionFactory->create();

        $this->extensionAttributesJoinProcessor->process(
            $collection,
            \Ktpl\Warehousemanagement\Api\Data\WarehousemanagementInterface::class
        );

        $this->collectionProcessor->process($criteria, $collection);

        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($criteria);

        $items = [];
        foreach ($collection as $model) {
            $items[] = $model->getDataModel();
        }

        $searchResults->setItems($items);
        $searchResults->setTotalCount($collection->getSize());
        return $searchResults;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(
        \Ktpl\Warehousemanagement\Api\Data\WarehousemanagementInterface $warehousemanagement
    ) {
        try {
            $warehousemanagementModel = $this->warehousemanagementFactory->create();
            $this->resource->load($warehousemanagementModel, $warehousemanagement->getWarehousemanagementId());
            $this->resource->delete($warehousemanagementModel);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__(
                'Could not delete the Warehousemanagement: %1',
                $exception->getMessage()
            ));
        }
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteById($warehousemanagementId)
    {
        return $this->delete($this->getById($warehousemanagementId));
    }
}
