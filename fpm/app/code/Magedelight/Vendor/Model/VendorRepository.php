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

use Magedelight\Vendor\Model\ResourceModel\Vendor\Collection;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\State\InputMismatchException;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class VendorRepository implements \Magedelight\Vendor\Api\VendorRepositoryInterface
{
    /**
     * @var VendorFactory
     */
    protected $vendorFactory;

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
     * @var Source\Status
     */
    protected $vendorStatus;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var Config\Fields
     */
    protected $configFields;

    /**
     * @var \Magedelight\Vendor\Api\Data\VendorConfigFieldInterface
     */
    protected $vendorConfigFields;

    /**
     * @var \Magedelight\Vendor\Api\Data\PersonalDataInterfaceFactory
     */
    protected $personalData;

    /**
     * @var \Magedelight\Vendor\Api\Data\BusinessDataInterfaceFactory
     */
    protected $businessData;

    /**
     * @var \Magedelight\Vendor\Api\Data\StatusDataInterfaceFactory
     */
    protected $statusData;

    /**
     * @var \Magedelight\Vendor\Api\Data\ShippingDataInterfaceFactory
     */
    protected $shippingData;

    /**
     * @var \Magedelight\Vendor\Api\Data\BankDataInterfaceFactory
     */
    protected $bankingData;

    /**
     * @var \Magedelight\Vendor\Api\Data\VendorProfileInterfaceFactory
     */
    protected $vendorProfileData;

    /**
     * @var \Magedelight\Vendor\Api\Data\LoginDataInterfaceFactory
     */
    protected $loginData;

    /**
     * @var Microsite\Build\Category
     */
    protected $profileCategory;
    /**
     * @var \Magento\Framework\Serialize\SerializerInterface
     */
    private $serializer;

    /**
     * @param VendorFactory $vendorFactory
     * @param VendorRegistry $vendorRegistry
     * @param \Magedelight\Vendor\Api\Data\VendorSearchResultsInterfaceFactory $searchResultsFactory
     * @param \Magedelight\Vendor\Model\ResourceModel\Vendor\CollectionFactory $collectionFactory
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     * @param \Magedelight\Vendor\Model\ResourceModel\Vendor $resourceModel
     * @param \Magento\Framework\Api\FilterBuilder $filterBuilder
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param Source\Status $vendorStatus
     * @param Config\Fields $configFields
     * @param \Magedelight\Vendor\Api\Data\VendorConfigFieldInterface $vendorConfigFields
     * @param \Magedelight\Vendor\Api\Data\PersonalDataInterfaceFactory $personalData
     * @param \Magedelight\Vendor\Api\Data\BusinessDataInterfaceFactory $businessData
     * @param \Magedelight\Vendor\Api\Data\StatusDataInterfaceFactory $statusData
     * @param \Magedelight\Vendor\Api\Data\ShippingDataInterfaceFactory $shippingData
     * @param \Magedelight\Vendor\Api\Data\BankDataInterfaceFactory $bankingData
     * @param \Magedelight\Vendor\Api\Data\VendorProfileInterfaceFactory $vendorProfileData
     * @param \Magedelight\Vendor\Api\Data\LoginDataInterfaceFactory $loginData
     * @param Microsite\Build\Category $profileCategory
     * @param \Magento\Framework\Serialize\SerializerInterface $serializer
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        VendorFactory $vendorFactory,
        VendorRegistry $vendorRegistry,
        \Magedelight\Vendor\Api\Data\VendorSearchResultsInterfaceFactory $searchResultsFactory,
        \Magedelight\Vendor\Model\ResourceModel\Vendor\CollectionFactory $collectionFactory,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magedelight\Vendor\Model\ResourceModel\Vendor $resourceModel,
        \Magento\Framework\Api\FilterBuilder $filterBuilder,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magedelight\Vendor\Model\Source\Status $vendorStatus,
        \Magedelight\Vendor\Model\Config\Fields $configFields,
        \Magedelight\Vendor\Api\Data\VendorConfigFieldInterface $vendorConfigFields,
        \Magedelight\Vendor\Api\Data\PersonalDataInterfaceFactory $personalData,
        \Magedelight\Vendor\Api\Data\BusinessDataInterfaceFactory $businessData,
        \Magedelight\Vendor\Api\Data\StatusDataInterfaceFactory $statusData,
        \Magedelight\Vendor\Api\Data\ShippingDataInterfaceFactory $shippingData,
        \Magedelight\Vendor\Api\Data\BankDataInterfaceFactory $bankingData,
        \Magedelight\Vendor\Api\Data\VendorProfileInterfaceFactory $vendorProfileData,
        \Magedelight\Vendor\Api\Data\LoginDataInterfaceFactory $loginData,
        \Magedelight\Vendor\Model\Microsite\Build\Category $profileCategory,
        \Magento\Framework\Serialize\SerializerInterface $serializer
    ) {
        $this->vendorFactory = $vendorFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->collectionFactory = $collectionFactory;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->resourceModel = $resourceModel;
        $this->vendorRegistry = $vendorRegistry;
        $this->filterBuilder = $filterBuilder;
        $this->vendorStatus = $vendorStatus;
        $this->storeManager = $storeManager;
        $this->configFields = $configFields;
        $this->vendorConfigFields = $vendorConfigFields;
        $this->personalData = $personalData;
        $this->businessData = $businessData;
        $this->statusData = $statusData;
        $this->shippingData = $shippingData;
        $this->bankingData = $bankingData;
        $this->vendorProfileData = $vendorProfileData;
        $this->loginData = $loginData;
        $this->profileCategory = $profileCategory;
        $this->serializer = $serializer;
    }

    /**
     * {@inheritdoc}
     */
    public function getById($vendorId = null, $forceReload = false, $websiteId = null)
    {
        $cacheKey = $this->getCacheKey(func_get_args());
        $vendor = $this->vendorFactory->create();
        if (!$vendorId) {
            return $vendor;
        }
        if (!isset($this->instancesById[$vendorId][$cacheKey]) || $forceReload) {
            if ($websiteId) {
                $vendor->setWebsiteId($websiteId);
            }
            $vendor->load($vendorId);
            $statusText = $this->vendorStatus->getOptionText($vendor->getStatus());
            $vendor->setStatusText($statusText);
            if (!$vendor->getId()) {
                throw new NoSuchEntityException(__('Requested vendor doesn\'t exist'));
            }
            $this->instancesById[$vendorId][$cacheKey] = $vendor;
        }
        return $this->instancesById[$vendorId][$cacheKey];
    }

    /**
     * Retrieve vendor profile config fields.
     * @return array
     */
    public function getFormConfigFields()
    {
        $businessFields = $this->configFields->getBusinessConfigFields();
        $shippingFields = $this->configFields->getShippingConfigFields();
        $personalFields = $this->configFields->getPersonalConfigFields();

        $this->vendorConfigFields->setBusinessConfigFields($businessFields);
        $this->vendorConfigFields->setPersonalConfigFields($personalFields);
        $this->vendorConfigFields->setShippingConfigFields($shippingFields);

        $fields[] = $this->vendorConfigFields->getData();

        return $fields;
    }

    /**
     *
     * @param $email
     * @param $websiteId
     * @return Vendor
     * @throws NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function get($email, $websiteId = null)
    {
        $cacheKey = $this->getCacheKey(func_get_args());
        if (!isset($this->instances[$email][$cacheKey])) {
            $vendor = $this->vendorFactory->create();
            $vendorId = $this->resourceModel->getIdByEmail($email, $websiteId);
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
            $vendor = $this->vendorFactory->create();

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
        $serializeData = [];
        foreach ($data as $key => $value) {
            if (is_object($value)) {
                $serializeData[$key] = $value->getId();
            } else {
                $serializeData[$key] = $value;
            }
        }

        return sha1($this->serializer->serialize($serializeData));
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function save(\Magedelight\Vendor\Api\Data\VendorInterface $vendor, $passwordHash = null, $section = null)
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
            throw new \Magento\Framework\Exception\CouldNotSaveException(
                __('Unable to save vendor' . $e->getMessage())
            );
        }

        unset($this->instancesById[$vendor->getId()]);

        return $this->getById($vendor->getId());
    }

    /**
     * {@inheritdoc}
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria, $addWebsiteData = true)
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

        if ($addWebsiteData) {
            $collection->_addWebsiteData(['*']);
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
     * @return bool
     * @throws NoSuchEntityException
     */
    public function delete(\Magedelight\Vendor\Api\Data\VendorInterface $vendor)
    {
        return $this->deleteById($vendor->getId());
    }

    /**
     *
     * @param int $vendorId
     * @return bool
     * @throws NoSuchEntityException
     */
    public function deleteById($vendorId)
    {
        $vendorModel = $this->vendorRegistry->retrieve($vendorId);
        $vendorModel->delete();
        $this->vendorRegistry->remove($vendorId);
        return true;
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

    /**
     * {@inheritdoc}
     */
    public function getProfileById($vendorId)
    {
        $vendorData = $this->getById($vendorId);

        $personalInfo = $this->processPersonalData($vendorData);
        $businessInfo = $this->processBusinessData($vendorData);
        $categoryInfo = $this->processCategoryData($vendorData);
        $statusInfo   = $this->processStatusData($vendorData);
        $shippingInfo = $this->processShippingData($vendorData);
        $loginInfo    = $this->processLoginData($vendorData);
        $bankingInfo = $this->processBankingData($vendorData);

        $vendorProfile = $this->vendorProfileData->create();
        $vendorProfile->setPersonalInformation($personalInfo);
        $vendorProfile->setCategoryItems($categoryInfo);
        $vendorProfile->setBusinessInformation($businessInfo);
        $vendorProfile->setLoginInformation($loginInfo);
        $vendorProfile->setStatusInformation($statusInfo);
        $vendorProfile->setShippingInformation($shippingInfo);
        $vendorProfile->setBankingInformation($bankingInfo);
        return $vendorProfile;
    }

    /**
     * @param $vendorData
     * @return array
     * @throws NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function processCategoryData($vendorData)
    {
        $rootCategoryId = $this->storeManager->getStore()->getRootCategoryId();
        $storeId = $this->storeManager->getStore()->getStoreId();
        $data = $this->profileCategory->buildCategoryTree($vendorData, $storeId, true);
        return $data;
    }

    /**
     * @param $vendorData
     * @return \Magedelight\Vendor\Api\Data\LoginDataInterface
     */
    protected function processLoginData($vendorData)
    {
        $login = $this->loginData->create();
        $login->setMobile($vendorData->getMobile());
        $login->setEmail($vendorData->getEmail());
        return $login;
    }

    /**
     * @param $vendorData
     * @return \Magedelight\Vendor\Api\Data\BankDataInterface
     */
    protected function processBankingData($vendorData)
    {
        $banking = $this->bankingData->create();
        $banking->setBankAccountName($vendorData->getBankAccountName());
        $banking->setBankAccountNumber($vendorData->getBankAccountNumber());
        $banking->setBankName($vendorData->getBankName());
        $banking->setIfsc($vendorData->getIfsc());
        return $banking;
    }

    /**
     * @param $vendorData
     * @return \Magedelight\Vendor\Api\Data\ShippingDataInterface
     */
    protected function processShippingData($vendorData)
    {
        $shipping = $this->shippingData->create();
        $shipping->setPickupAddress1($vendorData->getPickupAddress1());
        $shipping->setPickupAddress2($vendorData->getPickupAddress2());
        $shipping->setPickupCountry($vendorData->getPickupCountryId());
        $shipping->setPickupRegion($vendorData->getPickupRegion());
        $shipping->setPickupRegionId($vendorData->getPickupRegionId());
        $shipping->setPickupCity($vendorData->getPickupCity());
        $shipping->setPickupPincode($vendorData->getPickupPincode());
        return $shipping;
    }

    /**
     * @param $vendorData
     * @return \Magedelight\Vendor\Api\Data\StatusDataInterface
     */
    protected function processStatusData($vendorData)
    {
        $status = $this->statusData->create();
        $status->setCurrentStatus($vendorData->getStatusText());
        return $status;
    }

    /**
     * @param $vendorData
     * @return \Magedelight\Vendor\Api\Data\BusinessDataInterface
     */
    protected function processBusinessData($vendorData)
    {
        $business = $this->businessData->create();
        $business->setBusinessName($vendorData->getBusinessName());
        $business->setVat($vendorData->getVat());
        $business->setVatDoc($vendorData->getVatDoc());
        $business->setOtherMarketplaceProfile($vendorData->getOtherMarketplaceProfile());
        return $business;
    }

    /**
     * @param $vendorData
     * @return \Magedelight\Vendor\Api\Data\PersonalDataInterface
     */
    protected function processPersonalData($vendorData)
    {
        $personal = $this->personalData->create();

        $personal->setName($vendorData->getName());
        $personal->setEmail($vendorData->getEmail());
        $personal->setAddress1($vendorData->getAddress1());
        $personal->setAddress2($vendorData->getAddress2());
        $personal->setCountryId($vendorData->getCountryId());
        $personal->setRegion($vendorData->getRegion());
        $personal->setRegionId($vendorData->getRegionId());
        $personal->setCity($vendorData->getCity());
        $personal->setPincode($vendorData->getPincode());

        return $personal;
    }
}
