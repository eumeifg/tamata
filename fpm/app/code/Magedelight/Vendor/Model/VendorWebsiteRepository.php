<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Vendor
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Vendor\Model;

class VendorWebsiteRepository implements \Magedelight\Vendor\Api\VendorWebsiteRepositoryInterface
{

    /**
     * Cached instances
     *
     * @var array
     */
    protected $instances = [];

    /**
     * Vendor Website resource model
     *
     * @var \Magedelight\Vendor\Model\ResourceModel\VendorWebsite
     */
    protected $resource;

    /**
     * @var \Magedelight\Vendor\Api\Data\VendorWebsiteInterfaceFactory
     */
    protected $vendorWebsiteInterfaceFactory;

    /**
     * @var \Magedelight\Vendor\Model\ResourceModel\VendorWebsite\CollectionFactory
     */
    protected $vendorWebsiteCollectionFactory;

    /**
     * Data Object Helper
     *
     * @var \Magento\Framework\Api\DataObjectHelper
     */
    protected $dataObjectHelper;

    /**
     * Search result factory
     *
     * @var \Magedelight\Vendor\Api\Data\VendorWebsiteSearchResultInterfaceFactory
     */
    protected $searchResultsFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * constructor
     * @param ResourceModel\VendorWebsite $resource
     * @param ResourceModel\VendorWebsite\CollectionFactory $vendorWebsiteCollectionFactory
     * @param \Magedelight\Vendor\Api\Data\VendorWebsiteInterfaceFactory $vendorWebsiteInterfaceFactory
     * @param \Magento\Framework\Api\DataObjectHelper $dataObjectHelper
     * @param \Magedelight\Vendor\Api\Data\VendorWebsiteSearchResultInterfaceFactory $searchResultsFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        \Magedelight\Vendor\Model\ResourceModel\VendorWebsite $resource,
        \Magedelight\Vendor\Model\ResourceModel\VendorWebsite\CollectionFactory $vendorWebsiteCollectionFactory,
        \Magedelight\Vendor\Api\Data\VendorWebsiteInterfaceFactory $vendorWebsiteInterfaceFactory,
        \Magento\Framework\Api\DataObjectHelper $dataObjectHelper,
        \Magedelight\Vendor\Api\Data\VendorWebsiteSearchResultInterfaceFactory $searchResultsFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->resource                        = $resource;
        $this->vendorWebsiteCollectionFactory = $vendorWebsiteCollectionFactory;
        $this->vendorWebsiteInterfaceFactory = $vendorWebsiteInterfaceFactory;
        $this->dataObjectHelper                = $dataObjectHelper;
        $this->searchResultsFactory            = $searchResultsFactory;
        $this->storeManager            = $storeManager;
    }

    /**
     * Save Vendor Website.
     *
     * @param \Magedelight\Vendor\Api\Data\VendorWebsiteInterface $vendorWebsite
     * @return \Magedelight\Vendor\Api\Data\VendorWebsiteInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(\Magedelight\Vendor\Api\Data\VendorWebsiteInterface $vendorWebsite)
    {
        /** @var \Magedelight\Vendor\Api\Data\VendorWebsiteInterface|\Magento\Framework\Model\AbstractModel $vendorWebsite */
        try {
            $this->resource->save($vendorWebsite);
        } catch (\Exception $exception) {
            throw new \Magento\Framework\Exception\CouldNotSaveException(__(
                'Could not save the Vendor&#x20;Website: %1',
                $exception->getMessage()
            ));
        }
        return $vendorWebsite;
    }

    /**
     * Retrieve Vendor Website.
     *
     * @param int $vendorWebsiteId
     * @return \Magedelight\Vendor\Api\Data\VendorWebsiteInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getById($vendorWebsiteId)
    {
        $currentWebsite = $this->storeManager->getStore()->getWebsiteId();
        if (!isset($this->instances[$vendorWebsiteId])) {
            /** @var \Magedelight\Vendor\Api\Data\VendorWebsiteInterface|\Magento\Framework\Model\AbstractModel $VendorWebsite */
            $vendorWebsite = $this->vendorWebsiteInterfaceFactory->create();
            $this->resource->load($vendorWebsite, $vendorWebsiteId);
            if (!$vendorWebsite->getId()) {
                throw new \Magento\Framework\Exception\NoSuchEntityException(
                    __('Requested Vendor&#x20;Website doesn\'t exist')
                );
            }
            $this->instances[$vendorWebsiteId] = $vendorWebsite;
        }
        return $this->instances[$vendorWebsiteId];
    }

    /**
     *
     * @param type $vendorId
     * @param null $websiteId
     * @return bool|\Magento\Framework\DataObject
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getVendorWebsiteData($vendorId, $websiteId = null)
    {
        $currentWebsite = ($websiteId) ?: $this->storeManager->getStore()->getWebsiteId();
        $collection = $this->vendorWebsiteCollectionFactory->create();
        $websiteData = $collection->addFieldToFilter('vendor_id', $vendorId)
            ->addFieldToFilter('website_id', $currentWebsite);
        if ($websiteData->count()) {
            $collection->getSelect()
                ->reset(\Zend_Db_Select::COLUMNS)
                ->columns(['*']);
            return $collection->getFirstItem();
        }
        return false;
    }

    /**
     *
     * @param $token
     * @param null $websiteId
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getVendorIdByEmailVerificationCode($token, $websiteId = null)
    {
        $currentWebsite = ($websiteId) ?: $this->storeManager->getStore()->getWebsiteId();
        $collection = $this->vendorWebsiteCollectionFactory->create();
        $websiteData = $collection->addFieldToFilter('email_verification_code', $token)
            ->addFieldToFilter('website_id', $currentWebsite);
        if ($websiteData->count()) {
            $collection->getSelect()
                ->reset(\Zend_Db_Select::COLUMNS)
                ->columns(['vendor_id']);
            return $collection->getFirstItem()->getVendorId();
        }
        return false;
    }

    /**
     *
     * @param type $vendorId
     * @param type int | null $websiteId
     * @return array | false
     */
    public function getVendorWebsites($vendorId)
    {
        $collection = $this->vendorWebsiteCollectionFactory->create();
        $storeData = $collection->addFieldToFilter('vendor_id', $vendorId);
        if ($storeData->count()) {
            return $collection->getColumnValues('website_id');
        }
        return false;
    }

    /**
     * Retrieve Vendor Websites matching the specified criteria.
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Magedelight\Vendor\Api\Data\VendorWebsiteSearchResultInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria)
    {
        /** @var \Magedelight\Vendor\Api\Data\VendorWebsiteSearchResultInterface $searchResults */
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);

        /** @var \Magedelight\Vendor\Model\ResourceModel\VendorWebsite\Collection $collection */
        $collection = $this->vendorWebsiteCollectionFactory->create();

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

        /** @var \Magedelight\Vendor\Api\Data\VendorWebsiteInterface[] $vendorWebsites */
        $vendorWebsites = [];
        /** @var \Magedelight\Vendor\Model\VendorWebsite $vendorWebsite */
        foreach ($collection as $vendorWebsite) {
            /** @var \Magedelight\Vendor\Api\Data\VendorWebsiteInterface $vendorWebsiteDataObject */
            $vendorWebsiteDataObject = $this->vendorWebsiteInterfaceFactory->create();
            $this->dataObjectHelper->populateWithArray(
                $vendorWebsiteDataObject,
                $vendorWebsite->getData(),
                \Magedelight\Vendor\Api\Data\VendorWebsiteInterface::class
            );
            $vendorWebsites[] = $vendorWebsiteDataObject;
        }
        $searchResults->setTotalCount($collection->getSize());
        return $searchResults->setItems($vendorWebsites);
    }

    /**
     * Delete Vendor Website.
     *
     * @param \Magedelight\Vendor\Api\Data\VendorWebsiteInterface $vendorWebsite
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(\Magedelight\Vendor\Api\Data\VendorWebsiteInterface $vendorWebsite)
    {
        /** @var \Magedelight\Vendor\Api\Data\VendorWebsiteInterface|\Magento\Framework\Model\AbstractModel $vendorWebsite */
        $id = $vendorWebsite->getId();
        try {
            unset($this->instances[$id]);
            $this->resource->delete($vendorWebsite);
        } catch (\Magento\Framework\Exception\ValidatorException $e) {
            throw new \Magento\Framework\Exception\CouldNotSaveException(__($e->getMessage()));
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\StateException(
                __('Unable to remove Vendor&#x20;Website %1', $id)
            );
        }
        unset($this->instances[$id]);
        return true;
    }

    /**
     * Delete Vendor Website by ID.
     *
     * @param int $vendorWebsiteId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($vendorWebsiteId)
    {
        $vendorWebsite = $this->getById($vendorWebsiteId);
        return $this->delete($vendorWebsite);
    }

    /**
     * Helper function that adds a FilterGroup to the collection.
     *
     * @param \Magento\Framework\Api\Search\FilterGroup $filterGroup
     * @param \Magedelight\Vendor\Model\ResourceModel\VendorWebsite\Collection $collection
     * @return $this
     * @throws \Magento\Framework\Exception\InputException
     */
    protected function addFilterGroupToCollection(
        \Magento\Framework\Api\Search\FilterGroup $filterGroup,
        \Magedelight\Vendor\Model\ResourceModel\VendorWebsite\Collection $collection
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

    /**
     * Clean internal cache
     *
     * @return void
     */
    public function cleanCache()
    {
        $this->instances = null;
        $this->instancesById = null;
    }
}
