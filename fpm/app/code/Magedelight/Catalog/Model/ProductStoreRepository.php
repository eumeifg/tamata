<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Catalog
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Catalog\Model;

class ProductStoreRepository implements \Magedelight\Catalog\Api\ProductStoreRepositoryInterface
{
    /**
     * Cached instances
     *
     * @var array
     */
    protected $instances = [];

    /**
     * Product Store resource model
     *
     * @var \Magedelight\Catalog\Model\ResourceModel\ProductStore
     */
    protected $resource;

    /**
     * Product Store collection factory
     *
     * @var \Magedelight\Catalog\Model\ResourceModel\ProductStore\CollectionFactory
     */
    protected $ProductStoreCollectionFactory;

    /**
     * Product Store interface factory
     *
     * @var \Magedelight\Catalog\Api\Data\ProductStoreInterfaceFactory
     */
    protected $ProductStoreInterfaceFactory;

    /**
     * Data Object Helper
     *
     * @var \Magento\Framework\Api\DataObjectHelper
     */
    protected $dataObjectHelper;

    /**
     * Search result factory
     *
     * @var \Magedelight\Catalog\Api\Data\ProductStoreSearchResultInterfaceFactory
     */
    protected $searchResultsFactory;

    /**
     * constructor
     *
     * @param \Magedelight\Catalog\Model\ResourceModel\ProductStore $resource
     * @param \Magedelight\Catalog\Model\ResourceModel\ProductStore\CollectionFactory $ProductStoreCollectionFactory
     * @param \Magedelight\Catalog\Api\Data\ProductStoreInterfaceFactory $ProductStoreInterfaceFactory
     * @param \Magento\Framework\Api\DataObjectHelper $dataObjectHelper
     * @param \Magedelight\Catalog\Api\Data\ProductStoreSearchResultInterfaceFactory $searchResultsFactory
     */
    public function __construct(
        \Magedelight\Catalog\Model\ResourceModel\ProductStore $resource,
        \Magedelight\Catalog\Model\ResourceModel\ProductStore\CollectionFactory $ProductStoreCollectionFactory,
        \Magedelight\Catalog\Api\Data\ProductStoreInterfaceFactory $ProductStoreInterfaceFactory,
        \Magento\Framework\Api\DataObjectHelper $dataObjectHelper,
        \Magedelight\Catalog\Api\Data\ProductStoreSearchResultInterfaceFactory $searchResultsFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->resource                      = $resource;
        $this->ProductStoreCollectionFactory = $ProductStoreCollectionFactory;
        $this->ProductStoreInterfaceFactory  = $ProductStoreInterfaceFactory;
        $this->dataObjectHelper              = $dataObjectHelper;
        $this->searchResultsFactory          = $searchResultsFactory;
        $this->storeManager            = $storeManager;
    }

    /**
     * Save Product Store.
     *
     * @param \Magedelight\Catalog\Api\Data\ProductStoreInterface $ProductStore
     * @return \Magedelight\Catalog\Api\Data\ProductStoreInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(\Magedelight\Catalog\Api\Data\ProductStoreInterface $ProductStore)
    {
        /** @var \Magedelight\Catalog\Api\Data\ProductStoreInterface|\Magento\Framework\Model\AbstractModel $ProductStore */
        try {
            $this->resource->save($ProductStore);
        } catch (\Exception $exception) {
            throw new \Magento\Framework\Exception\CouldNotSaveException(__(
                'Could not save the Product&#x20;Store: %1',
                $exception->getMessage()
            ));
        }
        return $ProductStore;
    }

    /**
     * Retrieve Product Store.
     *
     * @param int $ProductStoreId
     * @return \Magedelight\Catalog\Api\Data\ProductStoreInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getById($ProductStoreId)
    {
        if (!isset($this->instances[$ProductStoreId])) {
            /** @var \Magedelight\Catalog\Api\Data\ProductStoreInterface|\Magento\Framework\Model\AbstractModel $ProductStore */
            $ProductStore = $this->ProductStoreInterfaceFactory->create();
            $this->resource->load($ProductStore, $ProductStoreId);
            if (!$ProductStore->getId()) {
                throw new \Magento\Framework\Exception\NoSuchEntityException(
                    __('Requested Product&#x20;Store doesn\'t exist')
                );
            }
            $this->instances[$ProductStoreId] = $ProductStore;
        }
        return $this->instances[$ProductStoreId];
    }

    /**
     *
     * @param type $productId
     * @param type int | null $storeId
     * @return type
     */
    public function getProductStoreData($productId, $storeId = null)
    {
        // $currentStore = ($storeId) ? : $this->storeManager->getStore()->getStoreId();
        $currentStore = $storeId;
        $collection = $this->ProductStoreCollectionFactory->create();
        if ($currentStore != '') {
            $storeData = $collection->addFieldToFilter('vendor_product_id', $productId)
                ->addFieldToFilter('store_id', $currentStore);
        } else {
            $storeData = $collection->addFieldToFilter('vendor_product_id', $productId);
        }
        if ($storeData->count()) {
            $collection->getSelect()
               ->reset(\Zend_Db_Select::COLUMNS)
               ->columns(['warranty_description', 'condition_note','store_id']);
            return $collection->getFirstItem();
        }
        return false;
    }

    /**
     *
     * @param type $productId
     * @param type int | null $websiteId
     * @return array | false
     */
    public function getProductStores($productId, $websiteId = null)
    {
        $collection = $this->ProductStoreCollectionFactory->create();
        $storeData = $collection->addFieldToFilter('vendor_product_id', $productId);
        if ($websiteId) {
            $collection->addFieldToFilter('website_id', $websiteId);
        }
        if ($storeData->count()) {
            return $collection->getColumnValues('store_id');
        }
        return false;
    }

    /**
     * Retrieve Product Stores matching the specified criteria.
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Magedelight\Catalog\Api\Data\ProductStoreSearchResultInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria)
    {
        /** @var \Magedelight\Catalog\Api\Data\ProductStoreSearchResultInterface $searchResults */
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);

        /** @var \Magedelight\Catalog\Model\ResourceModel\ProductStore\Collection $collection */
        $collection = $this->ProductStoreCollectionFactory->create();

        //Add filters from root filter group to the collection
        /** @var \Magento\Framework\Api\Search\FilterGroup $group */
        foreach ($searchCriteria->getFilterGroups() as $group) {
            $this->addFilterGroupToCollection($group, $collection);
        }
        $sortOrders = $searchCriteria->getSortOrders();
        /** @var \Magento\Framework\Api\SortOrder $sortOrder */
        if ($sortOrders) {
            foreach ($searchCriteria->getSortOrders() as $sortOrder) {
                $field = $sortOrder->getField();
                $collection->addOrder(
                    $field,
                    ($sortOrder->getDirection() == \Magento\Framework\Api\SortOrder::SORT_ASC) ? 'ASC' : 'DESC'
                );
            }
        } else {
            // set a default sorting order since this method is used constantly in many
            // different blocks
            $field = 'row_id';
            $collection->addOrder($field, 'ASC');
        }
        $collection->setCurPage($searchCriteria->getCurrentPage());
        $collection->setPageSize($searchCriteria->getPageSize());

        /** @var \Magedelight\Catalog\Api\Data\ProductStoreInterface[] $ProductStores */
        $ProductStores = [];
        /** @var \Magedelight\Catalog\Model\ProductStore $ProductStore */
        foreach ($collection as $ProductStore) {
            /** @var \Magedelight\Catalog\Api\Data\ProductStoreInterface $ProductStoreDataObject */
            $ProductStoreDataObject = $this->ProductStoreInterfaceFactory->create();
            $this->dataObjectHelper->populateWithArray(
                $ProductStoreDataObject,
                $ProductStore->getData(),
                \Magedelight\Catalog\Api\Data\ProductStoreInterface::class
            );
            $ProductStores[] = $ProductStoreDataObject;
        }
        $searchResults->setTotalCount($collection->getSize());
        return $searchResults->setItems($ProductStores);
    }

    /**
     * Delete Product Store.
     *
     * @param \Magedelight\Catalog\Api\Data\ProductStoreInterface $ProductStore
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(\Magedelight\Catalog\Api\Data\ProductStoreInterface $ProductStore)
    {
        /** @var \Magedelight\Catalog\Api\Data\ProductStoreInterface|\Magento\Framework\Model\AbstractModel $ProductStore */
        $id = $ProductStore->getId();
        try {
            unset($this->instances[$id]);
            $this->resource->delete($ProductStore);
        } catch (\Magento\Framework\Exception\ValidatorException $e) {
            throw new \Magento\Framework\Exception\CouldNotSaveException(__($e->getMessage()));
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\StateException(
                __('Unable to remove Product&#x20;Store %1', $id)
            );
        }
        unset($this->instances[$id]);
        return true;
    }

    /**
     * Delete Product Store by ID.
     *
     * @param int $ProductStoreId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($ProductStoreId)
    {
        $ProductStore = $this->getById($ProductStoreId);
        return $this->delete($ProductStore);
    }

    /**
     * Helper function that adds a FilterGroup to the collection.
     *
     * @param \Magento\Framework\Api\Search\FilterGroup $filterGroup
     * @param \Magedelight\Catalog\Model\ResourceModel\ProductStore\Collection $collection
     * @return $this
     * @throws \Magento\Framework\Exception\InputException
     */
    protected function addFilterGroupToCollection(
        \Magento\Framework\Api\Search\FilterGroup $filterGroup,
        \Magedelight\Catalog\Model\ResourceModel\ProductStore\Collection $collection
    ) {
        $fields = [];
        $conditions = [];
        foreach ($filterGroup->getFilters() as $filter) {
            $condition = $filter->getConditionType() ? $filter->getConditionType() : 'eq';
            $fields[] = $filter->getField();
            $conditions[] = [$condition => $filter->getValue()];
        }
        if ($fields) {
            $collection->addFieldToFilter($fields, $conditions);
        }
        return $this;
    }
}
