<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Sales
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Sales\Model;

use Magento\Customer\Api\AccountManagementInterface as CustomerAccountManagement;
use Magento\Customer\Api\CustomerRepositoryInterface as CustomerRepository;
use Magento\Customer\Api\GroupManagementInterface as CustomerGroupManagement;
use Magento\Customer\Api\GroupRepositoryInterface as CustomerGroupRepository;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Store\Model\Store;
use Magento\Tax\Api\TaxClassRepositoryInterface;

/**
 * Tax Calculation Model
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Calculation extends \Magento\Tax\Model\Calculation
{
    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param Config $taxConfig
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param \Magento\Tax\Model\ResourceModel\TaxClass\CollectionFactory $classesFactory
     * @param \Magento\Tax\Model\ResourceModel\Calculation $resource
     * @param CustomerAccountManagement $customerAccountManagement
     * @param CustomerGroupManagement $customerGroupManagement
     * @param CustomerGroupRepository $customerGroupRepository
     * @param CustomerRepository $customerRepository
     * @param PriceCurrencyInterface $priceCurrency
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param FilterBuilder $filterBuilder
     * @param TaxClassRepositoryInterface $taxClassRepository
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Tax\Model\Config $taxConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Tax\Model\ResourceModel\TaxClass\CollectionFactory $classesFactory,
        \Magento\Tax\Model\ResourceModel\Calculation $resource,
        CustomerAccountManagement $customerAccountManagement,
        CustomerGroupManagement $customerGroupManagement,
        CustomerGroupRepository $customerGroupRepository,
        CustomerRepository $customerRepository,
        PriceCurrencyInterface $priceCurrency,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        FilterBuilder $filterBuilder,
        TaxClassRepositoryInterface $taxClassRepository,
        \Magedelight\Vendor\Model\Vendor $vendorModel,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->_scopeConfig = $scopeConfig;
        $this->_config = $taxConfig;
        $this->_storeManager = $storeManager;
        $this->_customerSession = $customerSession;
        $this->_customerFactory = $customerFactory;
        $this->_classesFactory = $classesFactory;
        $this->customerAccountManagement = $customerAccountManagement;
        $this->customerGroupManagement = $customerGroupManagement;
        $this->customerGroupRepository = $customerGroupRepository;
        $this->customerRepository = $customerRepository;
        $this->priceCurrency = $priceCurrency;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->filterBuilder = $filterBuilder;
        $this->taxClassRepository = $taxClassRepository;
        $this->_vendorModel = $vendorModel;
        parent::__construct(
            $context,
            $registry,
            $scopeConfig,
            $taxConfig,
            $storeManager,
            $customerSession,
            $customerFactory,
            $classesFactory,
            $resource,
            $customerAccountManagement,
            $customerGroupManagement,
            $customerGroupRepository,
            $customerRepository,
            $priceCurrency,
            $searchCriteriaBuilder,
            $filterBuilder,
            $taxClassRepository,
            $resourceCollection,
            []
        );
    }

    /**
     * Get request object for getting tax rate based on store shipping original address
     *
     * Updated to get Vendor Pickup address into calculation and return request object
     *
     * @param   null|string|bool|int|Store $store
     * @return  \Magento\Framework\DataObject
     */
    protected function getRateOriginRequest($store = null)
    {
        $request = new \Magento\Framework\DataObject();
        if ($this->_customerSession->getCurrentVendor() && $this->_customerSession->getCurrentVendor() != '') {
            $vendor = $this->_vendorModel->load($this->_customerSession->getCurrentVendor());
            if ($vendor) {
                $request->setCountryId(
                    $vendor->getPickupCountryId()
                )->setRegionId(
                    $vendor->getPickupRegionId()
                )->setPostcode(
                    $vendor->getPickupPincode()
                )->setCustomerClassId(
                    $this->getDefaultCustomerTaxClass($store)
                )->setStore(
                    $store
                );
            }
        } else {
            $request->setCountryId(
                $this->_scopeConfig->getValue(
                    \Magento\Shipping\Model\Config::XML_PATH_ORIGIN_COUNTRY_ID,
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                    $store
                )
            )->setRegionId(
                $this->_scopeConfig->getValue(
                    \Magento\Shipping\Model\Config::XML_PATH_ORIGIN_REGION_ID,
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                    $store
                )
            )->setPostcode(
                $this->_scopeConfig->getValue(
                    \Magento\Shipping\Model\Config::XML_PATH_ORIGIN_POSTCODE,
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                    $store
                )
            )->setCustomerClassId(
                $this->getDefaultCustomerTaxClass($store)
            )->setStore(
                $store
            );
        }
        $this->_customerSession->unsCurrentVendor();
        return $request;
    }
}
