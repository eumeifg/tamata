<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Commissions
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Commissions\Model;

use Magedelight\Commissions\Api\CategoryCommissionRepositoryInterface;
use Magedelight\Commissions\Model\ResourceModel\Commission\Collection;
use Magedelight\Commissions\Model\ResourceModel\Commission\CollectionFactory;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Repository for category commissions.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CommissionRepository implements CategoryCommissionRepositoryInterface
{

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var CommissionFactory
     */
    protected $commissionFactory;

    /**
     * @var Categorycommission[]
     */
    protected $instances = [];

    /**
     * @var \Magedelight\Commissions\Api\Data\CommissionSearchResultsInterfaceFactory
     */
    protected $categoryCommissionSearchResultsFactory;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var \Magedelight\Commissions\Model\ResourceModel\Commission\CollectionFactory
     */
    protected $categoryCommissionCollectionFactory;

    /**
     * @var \Magedelight\Commissions\Model\ResourceModel\Commission
     */
    protected $categoryCommissionResource;

    /**
     *
     * @param CommissionFactory $commissionFactory
     * @param \Magedelight\Commissions\Api\Data\CommissionSearchResultsInterfaceFactory $categoryCommissionSearchResultsFactory
     * @param CollectionFactory $categoryCommissionCollectionFactory
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     * @param \Magedelight\Commissions\Model\ResourceModel\Commission $categoryCommissionResource
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        CommissionFactory $commissionFactory,
        \Magedelight\Commissions\Api\Data\CommissionSearchResultsInterfaceFactory $categoryCommissionSearchResultsFactory,
        CollectionFactory $categoryCommissionCollectionFactory,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magedelight\Commissions\Model\ResourceModel\Commission $categoryCommissionResource,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->commissionFactory = $commissionFactory;
        $this->categoryCommissionSearchResultsFactory = $categoryCommissionSearchResultsFactory;
        $this->categoryCommissionCollectionFactory = $categoryCommissionCollectionFactory;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->categoryCommissionResource = $categoryCommissionResource;
        $this->storeManager = $storeManager;
    }

    /**
     * {@inheritdoc}
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        /** @var Collection $collection */
        $collection = $this->categoryCommissionCollectionFactory->create();

        //Add filters from root filter group to the collection
        foreach ($searchCriteria->getFilterGroups() as $group) {
            $this->addFilterGroupToCollection($group, $collection);
        }

        $collection->setCurPage($searchCriteria->getCurrentPage());
        $collection->setPageSize($searchCriteria->getPageSize());
        $collection->load();

        $searchResult = $this->categoryCommissionSearchResultsFactory->create();
        $searchResult->setSearchCriteria($searchCriteria);
        $searchResult->setItems($collection->getItems());
        $searchResult->setTotalCount($collection->getSize());
        return $searchResult;
    }

    /**
     * Helper function that adds a FilterGroup to the collection.
     *
     * @param \Magento\Framework\Api\Search\FilterGroup $filterGroup
     * @param Collection $collection
     * @return void
     */
    protected function addFilterGroupToCollection(
        \Magento\Framework\Api\Search\FilterGroup $filterGroup,
        Collection $collection
    ) {
        $fields = [];
        $conditions = [];
        foreach ($filterGroup->getFilters() as $filter) {
            $condition = $filter->getConditionType() ?
                $filter->getConditionType() :
                'eq';
            $fields[] = $filter->getField();
            $conditions[] = [$condition => $filter->getValue()];
        }
        if ($fields) {
            $collection->addFieldToFilter($fields, $conditions);
        }
    }

    /**
     * Get info about vendor by vendor id
     *
     * @param int $vendorId
     * @return array
     */
    public function getAssociatedProductsIds($vendorId)
    {
        return $this->productVendorsFactory->create()->getVendorProductIds($vendorId);
    }

    /**
     * @inheritdoc
     */
    public function delete(\Magedelight\Commissions\Api\Data\CommissionInterface $commission): bool
    {
        try {
            $commissionId = $commission->getId();
            $this->categoryCommissionResource->delete($commission);
        } catch (\Exception $e) {
            throw new StateException(
                __(
                    'Cannot delete category commission with id %1',
                    $commission->getId()
                ),
                $e
            );
        }
        unset($this->instances[$commissionId]);
        return true;
    }

    /**
     * @inheritdoc
     */
    public function deleteByIdentifier($commissionId): bool
    {
        $category = $this->get($commissionId);
        return  $this->delete($category);
    }

    /**
     * @inheritdoc
     */
    public function save(\Magedelight\Commissions\Api\Data\CommissionInterface $commission)
    {
        $storeId = (int)$this->storeManager->getStore()->getId();

        if ($commission->getId()) {
            $commission = $this->get($commission->getId(), $storeId);
        }

        try {
            $this->categoryCommissionResource->save($commission);
        } catch (\Exception $e) {
            throw new CouldNotSaveException(
                __(
                    'Could not save category commission: %1',
                    $e->getMessage()
                ),
                $e
            );
        }
        unset($this->instances[$commission->getId()]);
        return $this->get($commission->getId(), $storeId);
    }

    /**
     * {@inheritdoc}
     */
    public function get($commissionId, $storeId = null): \Magedelight\Commissions\Api\Data\CommissionInterface
    {
        $cacheKey = $storeId ?? 'all';
        if (!isset($this->instances[$commissionId][$cacheKey])) {
            /** @var Category $category */
            $category = $this->commissionFactory->create();
            if (null !== $storeId) {
                $category->setStoreId($storeId);
            }
            $category->load($commissionId);
            if (!$category->getId()) {
                throw NoSuchEntityException::singleField('id', $commissionId);
            }
            $this->instances[$commissionId][$cacheKey] = $category;
        }
        return $this->instances[$commissionId][$cacheKey];
    }
}
