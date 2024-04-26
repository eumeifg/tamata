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

class ProductWebsiteRepository implements \Magedelight\Catalog\Api\ProductWebsiteRepositoryInterface
{
    /**
     * Cached instances
     *
     * @var array
     */
    protected $instances = [];

    /**
     * Product Website resource model
     *
     * @var \Magedelight\Catalog\Model\ResourceModel\ProductWebsite
     */
    protected $resource;

    /**
     * Product Website collection factory
     *
     * @var \Magedelight\Catalog\Model\ResourceModel\ProductWebsite\CollectionFactory
     */
    protected $ProductWebsiteCollectionFactory;

    /**
     * Product Website interface factory
     *
     * @var \Magedelight\Catalog\Api\Data\ProductWebsiteInterfaceFactory
     */
    protected $ProductWebsiteInterfaceFactory;

    /**
     * Data Object Helper
     *
     * @var \Magento\Framework\Api\DataObjectHelper
     */
    protected $dataObjectHelper;

    /**
     * Search result factory
     *
     * @var \Magedelight\Catalog\Api\Data\ProductWebsiteSearchResultInterfaceFactory
     */
    protected $searchResultsFactory;

    /**
     * constructor
     *
     * @param \Magedelight\Catalog\Model\ResourceModel\ProductWebsite $resource
     * @param \Magedelight\Catalog\Model\ResourceModel\ProductWebsite\CollectionFactory $ProductWebsiteCollectionFactory
     * @param \Magedelight\Catalog\Api\Data\ProductWebsiteInterfaceFactory $ProductWebsiteInterfaceFactory
     * @param \Magento\Framework\Api\DataObjectHelper $dataObjectHelper
     * @param \Magedelight\Catalog\Api\Data\ProductWebsiteSearchResultInterfaceFactory $searchResultsFactory
     */
    public function __construct(
        \Magedelight\Catalog\Model\ResourceModel\ProductWebsite $resource,
        \Magedelight\Catalog\Model\ResourceModel\ProductWebsite\CollectionFactory $ProductWebsiteCollectionFactory,
        \Magedelight\Catalog\Api\Data\ProductWebsiteInterfaceFactory $ProductWebsiteInterfaceFactory,
        \Magento\Framework\Api\DataObjectHelper $dataObjectHelper,
        \Magedelight\Catalog\Api\Data\ProductWebsiteSearchResultInterfaceFactory $searchResultsFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->resource                        = $resource;
        $this->ProductWebsiteCollectionFactory = $ProductWebsiteCollectionFactory;
        $this->ProductWebsiteInterfaceFactory  = $ProductWebsiteInterfaceFactory;
        $this->dataObjectHelper                = $dataObjectHelper;
        $this->searchResultsFactory            = $searchResultsFactory;
        $this->storeManager            = $storeManager;
    }

    /**
     * Save Product Website.
     *
     * @param \Magedelight\Catalog\Api\Data\ProductWebsiteInterface $ProductWebsite
     * @return \Magedelight\Catalog\Api\Data\ProductWebsiteInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(\Magedelight\Catalog\Api\Data\ProductWebsiteInterface $ProductWebsite)
    {
        /** @var \Magedelight\Catalog\Api\Data\ProductWebsiteInterface|\Magento\Framework\Model\AbstractModel $ProductWebsite */
        try {
            $this->resource->save($ProductWebsite);
        } catch (\Exception $exception) {
            throw new \Magento\Framework\Exception\CouldNotSaveException(__(
                'Could not save the Product&#x20;Website: %1',
                $exception->getMessage()
            ));
        }
        return $ProductWebsite;
    }

    /**
     * Retrieve Product Website.
     *
     * @param int $ProductWebsiteId
     * @return \Magedelight\Catalog\Api\Data\ProductWebsiteInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getById($ProductWebsiteId)
    {
        $currentWebsite = $this->storeManager->getStore()->getWebsiteId();
        if (!isset($this->instances[$ProductWebsiteId])) {
            /** @var \Magedelight\Catalog\Api\Data\ProductWebsiteInterface|\Magento\Framework\Model\AbstractModel $ProductWebsite */
            $ProductWebsite = $this->ProductWebsiteInterfaceFactory->create();
            $this->resource->load($ProductWebsite, $ProductWebsiteId);
            if (!$ProductWebsite->getId()) {
                throw new \Magento\Framework\Exception\NoSuchEntityException(
                    __('Requested Product&#x20;Website doesn\'t exist')
                );
            }
            $this->instances[$ProductWebsiteId] = $ProductWebsite;
        }
        return $this->instances[$ProductWebsiteId];
    }

    /**
     *
     * @param type $productId
     * @param type int | null $websiteId
     * @return type
     */
    public function getProductWebsiteData($productId, $websiteId = null)
    {
        // $currentWebsite = ($websiteId) ? : $this->storeManager->getStore()->getWebsiteId();
        $currentWebsite = $websiteId;
        $collection = $this->ProductWebsiteCollectionFactory->create();
        if ($currentWebsite != '') {
            $websiteData = $collection->addFieldToFilter('vendor_product_id', $productId)
                ->addFieldToFilter('website_id', $currentWebsite);
        } else {
            $websiteData = $collection->addFieldToFilter('vendor_product_id', $productId);
        }

        if ($websiteData->count() > 0) {
            $collection->getSelect()
                ->reset(\Zend_Db_Select::COLUMNS)
                ->columns(
                    [
                        'price',
                        'special_price',
                        'special_from_date',
                        'special_to_date',
                        'category_id',
                        'status',
                        'condition',
                        'warranty_type',
                        'reorder_level'
                    ]
                );
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
    public function getProductWebsites($productId)
    {
        $collection = $this->ProductWebsiteCollectionFactory->create();
        $storeData = $collection->addFieldToFilter('vendor_product_id', $productId);
        if ($storeData->count()) {
            return $collection->getColumnValues('website_id');
        }
        return false;
    }

    /**
     * Retrieve Product Websites matching the specified criteria.
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Magedelight\Catalog\Api\Data\ProductWebsiteSearchResultInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria)
    {
        /** @var \Magedelight\Catalog\Api\Data\ProductWebsiteSearchResultInterface $searchResults */
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);

        /** @var \Magedelight\Catalog\Model\ResourceModel\ProductWebsite\Collection $collection */
        $collection = $this->ProductWebsiteCollectionFactory->create();

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

        /** @var \Magedelight\Catalog\Api\Data\ProductWebsiteInterface[] $ProductWebsites */
        $ProductWebsites = [];
        /** @var \Magedelight\Catalog\Model\ProductWebsite $ProductWebsite */
        foreach ($collection as $ProductWebsite) {
            /** @var \Magedelight\Catalog\Api\Data\ProductWebsiteInterface $ProductWebsiteDataObject */
            $ProductWebsiteDataObject = $this->ProductWebsiteInterfaceFactory->create();
            $this->dataObjectHelper->populateWithArray(
                $ProductWebsiteDataObject,
                $ProductWebsite->getData(),
                \Magedelight\Catalog\Api\Data\ProductWebsiteInterface::class
            );
            $ProductWebsites[] = $ProductWebsiteDataObject;
        }
        $searchResults->setTotalCount($collection->getSize());
        return $searchResults->setItems($ProductWebsites);
    }

    /**
     * Delete Product Website.
     *
     * @param \Magedelight\Catalog\Api\Data\ProductWebsiteInterface $ProductWebsite
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(\Magedelight\Catalog\Api\Data\ProductWebsiteInterface $ProductWebsite)
    {
        /** @var \Magedelight\Catalog\Api\Data\ProductWebsiteInterface|\Magento\Framework\Model\AbstractModel $ProductWebsite */
        $id = $ProductWebsite->getId();
        try {
            unset($this->instances[$id]);
            $this->resource->delete($ProductWebsite);
        } catch (\Magento\Framework\Exception\ValidatorException $e) {
            throw new \Magento\Framework\Exception\CouldNotSaveException(__($e->getMessage()));
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\StateException(
                __('Unable to remove Product&#x20;Website %1', $id)
            );
        }
        unset($this->instances[$id]);
        return true;
    }

    /**
     * Delete Product Website by ID.
     *
     * @param int $ProductWebsiteId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($ProductWebsiteId)
    {
        $ProductWebsite = $this->getById($ProductWebsiteId);
        return $this->delete($ProductWebsite);
    }

    /**
     * Helper function that adds a FilterGroup to the collection.
     *
     * @param \Magento\Framework\Api\Search\FilterGroup $filterGroup
     * @param \Magedelight\Catalog\Model\ResourceModel\ProductWebsite\Collection $collection
     * @return $this
     * @throws \Magento\Framework\Exception\InputException
     */
    protected function addFilterGroupToCollection(
        \Magento\Framework\Api\Search\FilterGroup $filterGroup,
        \Magedelight\Catalog\Model\ResourceModel\ProductWebsite\Collection $collection
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
