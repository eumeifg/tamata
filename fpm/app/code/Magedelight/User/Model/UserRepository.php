<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_User
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\User\Model;

use Magedelight\Vendor\Model\ResourceModel\Vendor\Collection;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\State\InputMismatchException;
use Magedelight\Vendor\Model\VendorRegistry;

/**
 * Description of UserRepository
 *
 * @author Rocket Bazaar Core Team
 */
class UserRepository implements \Magedelight\User\Api\UserRepositoryInterface
{
    /**
     * @var UserFactory
     */
    protected $userFactory;
    
    /**
     * @var VendorRegistry
     */
    protected $vendorRegistry;

    /**
     * @var Vendor[]
     */
    protected $instancesById = [];

    /**
     * @var \Magedelight\Vendor\Api\Data\VendorSearchResultsInterfaceFactory
     */
    protected $searchResultsFactory;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var \Magento\Framework\Api\FilterBuilder
     */
    protected $filterBuilder;

    /**
     * @var \Magedelight\Vendor\Model\ResourceModel\Vendor\CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var \Magedelight\Vendor\Model\ResourceModel\Vendor
     */
    protected $resourceModel;


    /**
     * @param UserFactory $userFactory
     * @param VendorRegistry $vendorRegistry
     * @param \Magedelight\Vendor\Api\Data\VendorSearchResultsInterfaceFactory $searchResultsFactory
     * @param \Magedelight\Vendor\Model\ResourceModel\Vendor\CollectionFactory $collectionFactory
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     * @param \Magedelight\Vendor\Model\ResourceModel\Vendor $resourceModel
     * @param \Magento\Framework\Api\FilterBuilder $filterBuilder
     * @param ProductVendorsFactory $productVendorsFactory
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        UserFactory $userFactory,
        VendorRegistry $vendorRegistry,
        \Magedelight\Vendor\Api\Data\VendorSearchResultsInterfaceFactory $searchResultsFactory,
        \Magedelight\Vendor\Model\ResourceModel\Vendor\CollectionFactory $collectionFactory,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magedelight\Vendor\Model\ResourceModel\Vendor $resourceModel,
        \Magento\Framework\Api\FilterBuilder $filterBuilder
    ) {
        $this->userFactory = $userFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->collectionFactory = $collectionFactory;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->resourceModel = $resourceModel;
        $this->vendorRegistry = $vendorRegistry;
        $this->filterBuilder = $filterBuilder;
    }

    /**
     * {@inheritdoc}
     */
    public function getById($vendorId = null, $forceReload = false)
    {
        $cacheKey = $this->getCacheKey(func_get_args());
        $vendor = $this->userFactory->create();
        if (!$vendorId) {
            return $vendor;
        }
        if (!isset($this->instancesById[$vendorId][$cacheKey]) || $forceReload) {
            $vendor->load($vendorId);
            if (!$vendor->getId()) {
                throw new NoSuchEntityException(__('Requested vendor doesn\'t exist'));
            }
            $this->instancesById[$vendorId][$cacheKey] = $vendor;
        }
        return $this->instancesById[$vendorId][$cacheKey];
    }
    
    /**
     *
     * @param type $email
     */
    public function get($email)
    {
        $cacheKey = $this->getCacheKey(func_get_args());
        if (!isset($this->instances[$email][$cacheKey])) {
            $vendor = $this->userFactory->create();

            $vendorId = $this->resourceModel->getIdByEmail($email);
            if (!$vendorId) {
                return $vendor;
            }
            $vendor->load($vendorId);
            $this->instances[$email][$cacheKey] = $vendor;
            $this->instancesById[$vendor->getId()][$cacheKey] = $vendor;
        }
        return $this->instances[$email][$cacheKey];
    }
    
    /**
     * {@inheritdoc}
     * @see \Magedelight\Vendor\Api\VendorRepositoryInterface;
     */
    public function getByEmailVerificationCode($token)
    {
        $cacheKey = $this->getCacheKey(func_get_args());
        if (!isset($this->instances[$token][$cacheKey])) {
            $vendor = $this->userFactory->create();

            $vendorId = $this->resourceModel->getIdByEmailVerificationCode($token);
            if (!$vendorId) {
                return $vendor;
            }
            $vendor->load($vendorId);
            $this->instances[$token][$cacheKey] = $vendor;
            $this->instancesById[$vendor->getId()][$cacheKey] = $vendor;
        }
        return $this->instances[$token][$cacheKey];
    }

    /**
     * Get key for cache
     *
     * @param array $data
     * @return string
     */
    protected function getCacheKey($data)
    {
        unset($data[0]);
        unset($data['forceReload']);
        $serializeData = [];
        foreach ($data as $key => $value) {
            if (is_object($value)) {
                $serializeData[$key] = $value->getId();
            } else {
                $serializeData[$key] = $value;
            }
        }

        return md5(serialize($serializeData));
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function save(\Magedelight\Vendor\Api\Data\VendorInterface $vendor, $passwordHash = null)
    {
        // Populate model with secure data
        if ($vendor->getId()) {
            $vendorSecure = $this->vendorRegistry->retrieveSecureData($vendor->getId());
            $vendor->setRpToken($vendorSecure->getRpToken());
            $vendor->setRpTokenCreatedAt($vendorSecure->getRpTokenCreatedAt());
            $vendor->setPasswordHash($vendorSecure->getPasswordHash());
        } else {
            if ($passwordHash) {
                $vendor->setPasswordHash($passwordHash);
            }
        }
        try {
            $this->resourceModel->save($vendor);
        } catch (AlreadyExistsException $e) {
            throw new InputMismatchException(
                __($e->getMessage())
            );
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\CouldNotSaveException(__('Unable to save vendor'));
        }
        
        unset($this->instancesById[$vendor->getId()]);
        
        return $this->getById($vendor->getId());
    }

    /**
     * {@inheritdoc}
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria)
    {
        /** @var \Magedelight\Vendor\Model\ResourceModel\Vendor\Collection $collection */
        $collection = $this->collectionFactory->create();

        //Add filters from root filter group to the collection
        foreach ($searchCriteria->getFilterGroups() as $group) {
            $this->addFilterGroupToCollection($group, $collection);
        }
        /** @var SortOrder $sortOrder */
        foreach ((array)$searchCriteria->getSortOrders() as $sortOrder) {
            $field = $sortOrder->getField();
            $collection->addOrder(
                $field,
                ($sortOrder->getDirection() == SortOrder::SORT_ASC) ? 'ASC' : 'DESC'
            );
        }
        $collection->setCurPage($searchCriteria->getCurrentPage());
        $collection->setPageSize($searchCriteria->getPageSize());
        $collection->load();

        $searchResult = $this->searchResultsFactory->create();
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
     *
     * @param \Magedelight\Vendor\Api\Data\VendorInterface $vendor
     */
    public function delete(\Magedelight\Vendor\Api\Data\VendorInterface $vendor)
    {
        return $this->deleteById($vendor->getId());
    }
    /**
     *
     * @param type $vendorId
     */
    public function deleteById($vendorId)
    {
        $vendorModel = $this->vendorRegistry->retrieve($vendorId);
        $vendorModel->delete();
        $this->vendorRegistry->remove($vendorId);
        return true;
    }
}
