<?php

namespace Magedelight\Customer\Model;

use Magedelight\Customer\Api\MobileDashboardInterface;

class MobileDashboard extends \Magento\Framework\DataObject implements MobileDashboardInterface
{
    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $jsonHelper;

    /**
     * @var \Magento\Authorization\Model\UserContextInterface
     */
    protected $userContext;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\App\ResponseInterface
     */
    protected $_response;

    /**
     * @var \Magento\Framework\Webapi\Rest\Request
     */
    protected $_request;

    /**
     * @var \Magedelight\Customer\Api\Data\MobileDashboardDataInterface
     */
    protected $mobileDashboardDataInterface;

    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @var \Magento\Framework\Api\FilterBuilder
     */
    protected $filterBuilder;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var \Magento\Review\Model\ResourceModel\Review\Product\CollectionFactory
     */
    protected $reviewcollectionFactory;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $dateTime;

    /**
     * @var \Magedelight\Review\Api\ReviewInterface
     */
    protected $reviewInterface;

    /**
     * @var \Magedelight\Vendor\Model\VendorReviewRepository
     */
    protected $vendorReviewRepository;

    /**
     * MobileDashboard constructor.
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param \Magento\Authorization\Model\UserContextInterface $userContext
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\App\ResponseInterface $response
     * @param \Magento\Framework\Webapi\Rest\Request $request
     * @param \Magedelight\Customer\Api\Data\MobileDashboardDataInterface $mobileDashboardDataInterface
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     * @param \Magento\Framework\Api\FilterBuilder $filterBuilder
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     * @param \Magento\Review\Model\ResourceModel\Review\Product\CollectionFactory $reviewcollectionFactory
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $dateTime
     * @param \Magedelight\Review\Api\ReviewInterface $reviewInterface
     * @param \Magedelight\Vendor\Model\VendorReviewRepository $vendorReviewRepository
     */
    public function __construct(
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Authorization\Model\UserContextInterface $userContext,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\ResponseInterface $response,
        \Magento\Framework\Webapi\Rest\Request $request,
        \Magedelight\Customer\Api\Data\MobileDashboardDataInterface $mobileDashboardDataInterface,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Framework\Api\FilterBuilder $filterBuilder,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Review\Model\ResourceModel\Review\Product\CollectionFactory $reviewcollectionFactory,
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime,
        \Magedelight\Review\Api\ReviewInterface $reviewInterface,
        \Magedelight\Vendor\Model\VendorReviewRepository $vendorReviewRepository
    ) {
        $this->jsonHelper = $jsonHelper;
        $this->userContext = $userContext;
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->_response = $response;
        $this->_request = $request;
        $this->mobileDashboardDataInterface = $mobileDashboardDataInterface;
        $this->orderRepository = $orderRepository;
        $this->filterBuilder = $filterBuilder;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->customerRepository = $customerRepository;
        $this->reviewcollectionFactory = $reviewcollectionFactory;
        $this->dateTime = $dateTime;
        $this->reviewInterface = $reviewInterface;
        $this->vendorReviewRepository = $vendorReviewRepository;
    }

    /**
     * @param $customerId
     */
    protected function processRecentOrders($customerId)
    {
        $filter = $this->filterBuilder
                ->setField('customer_id')
                ->setValue($customerId)
                ->setConditionType('eq')
                ->create();
        $this->searchCriteriaBuilder->addFilters([$filter]);

        $searchCriteria = $this->searchCriteriaBuilder->create();

        $result = $this->orderRepository->getList($searchCriteria);
        $result->getSelect()->order('entity_id DESC');
        $result->setPageSize(3);
        $this->mobileDashboardDataInterface->setRecentOrders($result->getData());
    }

    /**
     * @param $customerId
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function processCustomerDetails($customerId)
    {
        $customerDetail = $this->customerRepository->getById($customerId);
        $this->mobileDashboardDataInterface->setCustomerDetail($customerDetail);
    }

    /**
     * {@inheritdoc}
     */
    public function getMobileDashboard()
    {
        $customerId = $this->userContext->getUserId();
        if (!$customerId) {
            throw new \Exception(__("Something Went Wrong,Please login and Try Again"));
        }
        $this->processRecentOrders($customerId);
        $this->processCustomerDetails($customerId);
        $this->mobileDashboardDataInterface->setVendorReviews(
            $this->vendorReviewRepository->getByCustomerId($customerId, 2)
        );
        $this->mobileDashboardDataInterface->setCustomerReviews($this->reviewInterface->getReviewsList(2));
        return $this->mobileDashboardDataInterface;
    }
}
