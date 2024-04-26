<?php
/*
 * Copyright © 2018 Rocket Bazaar. All rights reserved.
 * See COPYING.txt for license details
 */

namespace Magedelight\Sales\Model;

use Magedelight\Sales\Api\Data\VendorOrderSearchResultInterfaceFactory;
use Magedelight\Sales\Api\OrderRepositoryInterface;
use Magedelight\Sales\Model\Order as VendorOrder;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Exception\CouldNotSaveException;

class OrderRepository implements OrderRepositoryInterface
{

    /**
     * Cached instances
     *
     * @var array
     */
    protected $instances = [];

    /**
     * @var \Magento\Authorization\Model\UserContextInterface
     */
    protected $userContext;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\CollectionFactoryInterface
     */
    protected $orderCollectioninterface;

    /**
     * @var \Magento\Sales\Model\Order\Config
     */
    protected $orderConfig;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\CollectionFactory
     */
    protected $orderCollectionFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Store\Model\StoreRepository
     */
    protected $storeRepository;

    /**
     * @var \Magedelight\Sales\Api\Data\VendorOrderInterfaceFactory
     */
    protected $vendorOrderInterface;

    /**
     * @var \Magedelight\Sales\Plugin\Order\Config
     */
    protected $statusConfig;

    /**
     * @var ResourceModel\Order
     */
    protected $vendorOrderResource;

    /**
     * @var ResourceModel\Order\CollectionFactory
     */
    protected $vendorOrderCollectionFactory;

    /**
     * @var \Magedelight\Vendor\Model\ResourceModel\Vendorrating\CollectionFactory
     */
    protected $vendorratingCollectionFactory;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $jsonHelper;

    /**
     * Search result factory
     *
     * @var \Magedelight\Sales\Api\Data\VendorOrderSearchResultInterfaceFactory
     */
    protected $searchResultsFactory;

    /**
     * @var CollectionProcessorInterface
     */
    protected $collectionProcessor;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var Order\Listing
     */
    protected $subOrdersListing;

    /**
     * @var \Magedelight\Base\Helper\Data
     */
    protected $baseHelper;

    /**
     * OrderRepository constructor.
     * @param \Magento\Authorization\Model\UserContextInterface $userContext
     * @param \Magento\Sales\Model\ResourceModel\Order\CollectionFactoryInterface $orderCollectioninterface
     * @param \Magento\Sales\Model\Order\Config $orderConfig
     * @param \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Store\Model\StoreRepository $storeRepository
     * @param \Magedelight\Sales\Api\Data\VendorOrderInterfaceFactory $vendorOrderInterface
     * @param ResourceModel\Order $vendorOrderResource
     * @param \Magedelight\Sales\Plugin\Order\Config $statusConfig
     * @param \Magedelight\Vendor\Model\ResourceModel\Vendorrating\CollectionFactory $vendorratingCollectionFactory
     * @param ResourceModel\Order\CollectionFactory $vendorOrderCollectionFactory
     * @param \Magedelight\Sales\Helper\Data $salesHelper
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param VendorOrderSearchResultInterfaceFactory $searchResultsFactory
     * @param CollectionProcessorInterface $collectionProcessor
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     * @param Order\Listing $subOrdersListing
     * @param \Magedelight\Base\Helper\Data $baseHelper
     */
    public function __construct(
        \Magento\Authorization\Model\UserContextInterface $userContext,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactoryInterface $orderCollectioninterface,
        \Magento\Sales\Model\Order\Config $orderConfig,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Store\Model\StoreRepository $storeRepository,
        \Magedelight\Sales\Api\Data\VendorOrderInterfaceFactory $vendorOrderInterface,
        \Magedelight\Sales\Model\ResourceModel\Order $vendorOrderResource,
        \Magedelight\Sales\Plugin\Order\Config $statusConfig,
        \Magedelight\Vendor\Model\ResourceModel\Vendorrating\CollectionFactory $vendorratingCollectionFactory,
        \Magedelight\Sales\Model\ResourceModel\Order\CollectionFactory $vendorOrderCollectionFactory,
        \Magedelight\Sales\Helper\Data $salesHelper,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        VendorOrderSearchResultInterfaceFactory $searchResultsFactory,
        CollectionProcessorInterface $collectionProcessor,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magedelight\Sales\Model\Order\Listing $subOrdersListing,
        \Magedelight\Base\Helper\Data $baseHelper
    ) {
        $this->userContext = $userContext;
        $this->orderCollectioninterface = $orderCollectioninterface;
        $this->orderConfig = $orderConfig;
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->storeManager = $storeManager;
        $this->storeRepository = $storeRepository;
        $this->vendorOrderInterface = $vendorOrderInterface;
        $this->statusConfig = $statusConfig;
        $this->vendorOrderResource = $vendorOrderResource;
        $this->vendorOrderCollectionFactory = $vendorOrderCollectionFactory;
        $this->vendorratingCollectionFactory = $vendorratingCollectionFactory;
        $this->salesHelper = $salesHelper;
        $this->jsonHelper = $jsonHelper;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->collectionProcessor = $collectionProcessor;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->subOrdersListing = $subOrdersListing;
        $this->baseHelper = $baseHelper;
    }

    /**
     * { @inheritDoc }
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById($vendorOrderId)
    {
        if (!isset($this->instances[$vendorOrderId])) {
            /** @var \Magedelight\Sales\Api\Data\VendorOrderInterface */
            $vendorOrder = $this->vendorOrderInterface->create();
            $this->vendorOrderResource->load($vendorOrder, $vendorOrderId);
            if (!$vendorOrder->getVendorOrderId()) {
                throw new \Magento\Framework\Exception\NoSuchEntityException(
                    __('Requested Vendor Order doesn\'t exist')
                );
            }
            $this->instances[$vendorOrderId] = $vendorOrder;
        }
        return $this->instances[$vendorOrderId];
    }

    /**
     * @param \Magedelight\Sales\Api\Data\VendorOrderInterface $vendorOrder
     * @return \Magedelight\Sales\Api\Data\VendorOrderInterface
     * @throws CouldNotSaveException
     */
    public function save(\Magedelight\Sales\Api\Data\VendorOrderInterface $vendorOrder)
    {
        try {
            //Validate changing of design.
            $this->vendorOrderResource->save($vendorOrder);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(
                __('Could not save the order: %1', $exception->getMessage()),
                $exception
            );
        }
        return $vendorOrder;
    }

    /**
     * {@inheritdoc}
     */
    public function getList($order_id = null, $type = null, $limit = null, $currPage = null)
    {
        $customerId = $this->userContext->getUserId();
        if ($limit === null) {
            $limit = 10;
        }
        if ($currPage === null) {
            $currPage = 1;
        }

        if (!isset($customerId)) {
            return false;
        }

        $this->orders = $this->orderCollectioninterface->create($customerId)->addFieldToSelect(
            '*'
        )->addFieldToFilter(
            'status',
            ['in' => $this->orderConfig->getVisibleOnFrontStatuses()]
        )->setOrder(
            'created_at',
            'desc'
        )->setPageSize(
            $limit
        )->setCurPage(
            $currPage
        );
        return $this->orders;
    }

    /**
     * { @inheritDoc }
     */
    public function getVendorOrders($section, $searchCriteria = null, $searchTerm = null)
    {
        $searchResults = $this->searchResultsFactory->create();
        if (!$searchCriteria) {
            $searchCriteria = $this->searchCriteriaBuilder->create();
        } else {
            foreach ($searchCriteria->getFilterGroups() as $filterGroup) {
                foreach ($filterGroup->getFilters() as $filter) {
                    if ($filter->getField() == 'created_at' &&
                        ($filter->getConditionType() == 'lteq' || $filter->getConditionType() == 'to')) {
                        $filter->setValue(date(
                            'Y-m-d H:i:s',
                            strtotime('+23 hours 59 minutes 59 seconds', strtotime($filter->getValue()))
                        ));
                    }
                }
            }
        }
        /** @var \Magedelight\Sales\Api\Data\VendorOrderSearchResultInterface $searchResults */
        $searchResults->setSearchCriteria($searchCriteria);

        $includeStatuses = [];
        $vendorId = $this->userContext->getUserId();

        switch ($section) {
            case 'upcoming':
                $includeStatuses = [VendorOrder::STATUS_PENDING, VendorOrder::STATUS_PROCESSING];
                break;
            case 'new':
                $includeStatuses = [
                    VendorOrder::STATUS_PENDING,
                    VendorOrder::STATUS_PROCESSING,
                    VendorOrder::STATUS_CONFIRMED
                ];
                break;
            case 'pack':
                $includeStatuses = [VendorOrder::STATUS_PACKED];
                break;
            case 'handover':
                $includeStatuses = [VendorOrder::STATUS_SHIPPED];
                break;
            case 'intransit':
                $includeStatuses = [VendorOrder::STATUS_IN_TRANSIT];
                break;
            case 'completed':
                $includeStatuses = [VendorOrder::STATUS_COMPLETE];
                break;
            case 'canceled':
                $includeStatuses = [VendorOrder::STATUS_CANCELED];
                break;
            case 'closed':
                $includeStatuses = [VendorOrder::STATUS_CLOSED];
                break;
        }

        $collection = $this->subOrdersListing->getSubOrdersCollection($vendorId);

        $collection->addFieldToFilter(
            'main_table.status',
            ['in' => $includeStatuses]
        );

        if ($section == 'upcoming') {
            $collection->getSelect()->join(
                ["main_order"=>"sales_order"],
                "main_order.entity_id = main_table.order_id and main_order.is_confirmed = 1",
                ["main_order.entity_id"]
            );
            $collection->addFieldToFilter('main_table.is_confirmed', ['eq' => 0]);
        } elseif ($section == 'new') {
            $collection->addFieldToFilter('main_table.is_confirmed', ['eq' => 1]);
        }

        if (!empty(trim($searchTerm))) {
            $this->subOrdersListing->addSearchFilterToCollection($collection, trim($searchTerm));
        }

        $collection->setOrder('main_table.created_at', 'DESC');
        $this->subOrdersListing->joinVendorCommissionPaymentTable($collection);
        $this->subOrdersListing->joinAddressColumnsToCollection($collection);
        $collection->addFilterToMap('increment_id', 'main_table.increment_id');
        $collection->addFilterToMap('created_at', 'main_table.created_at');
        $collection->addFilterToMap('grand_total', 'main_table.grand_total');
        $this->collectionProcessor->process($searchCriteria, $collection);

        $total = $collection->getSize();
        $response = [];

        foreach ($collection as $collectionData) {
            $vendorOrder = $this->vendorOrderInterface->create();
            $vendorOrder->setEntityId($collectionData->getEntityId());
            $vendorOrder->setVendorOrderId($collectionData->getRvoVendorOrderId());
            $vendorOrder->setVendorId($collectionData->getRvoVendorId());
            $vendorOrder->setIncrementId($collectionData->getRvoIncrementId());
            $orderStatus = $this->getStatusLabel('vendor', $collectionData->getStatus());
            $vendorOrder->setStatusLabel($orderStatus);
            $vendorOrder->setStatus($collectionData->getStatus());
            $vendorOrder->setTotalRefunded($collectionData->getTotalRefunded());
            $vendorOrder->setGrandTotal($collectionData->getRvoGrandTotal());
            $vendorOrder->setCreatedAt($collectionData->getRvoCreatedAt());
            $vendorOrder->setFirstName($collectionData->getFirstname());
            $vendorOrder->setLastName($collectionData->getLastname());
            $vendorOrder->setBillToName($collectionData->getBillToName());
            $vendorOrder->setShipToName($collectionData->getShipToName());
            $vendorOrder->setOrderId($collectionData->getOrderId());
            $vendorOrder->setIsConfirmed($collectionData->getIsConfirmed());
            $vendorOrder->setCanInvoice($vendorOrder->getCanInvoice());
            $vendorOrder->setCanManifest($vendorOrder->getCanManifest());
            $vendorOrder->setCanShip($vendorOrder->getCanShip());
            $vendorOrder->setCanPrintInvoice($vendorOrder->getCanPrintInvoice());
            $vendorOrder->setCanGeneratePackingSlip($vendorOrder->getCanGeneratePackingSlip());
            $vendorOrder->setMoveToIntransit($vendorOrder->getMoveToIntransit());
            $vendorOrder->setMoveToDelivered($vendorOrder->getMoveToDelivered());
            if ($section == 'canceled') {
                $vendorOrder->setCancellationFee($collectionData->getCancellationFee());
            }
            $vendorOrder->setItems(null);
            $response[] = $vendorOrder->getData();
        }

        $searchResults->setItems($response);
        $searchResults->setTotalCount($total);
        return $searchResults->setItems($response);
    }

    /**
     * @param $type
     * @param $status
     * @return string
     */
    public function getStatusLabel($type, $status)
    {
        return $this->statusConfig->getStatusLabelForScope($type, $status);
    }

    /**
     * Get Sub-Orders using main order
     * @param int|null $orderId
     * @return \Magedelight\Sales\Api\Data\VendorOrderInterface[]
     */
    public function getSubOrdersByMainOrder($orderId)
    {
        $collection = $this->vendorOrderCollectionFactory->create()
            ->addFieldToSelect(
                ['*']
            )->addFieldToFilter(
                'order_id',
                $orderId
            );
        $collection->setOrder('created_at');
        $response = [];
        foreach ($collection as $collectionData) {
            $vendorOrder = $this->vendorOrderInterface->create();
            $vendorOrder->setEntityId($collectionData->getOrderId());
            $vendorOrder->setVendorOrderId($collectionData->getVendorOrderId());
            $vendorOrder->setVendorId($collectionData->getVendorId());
            $vendorOrder->setIncrementId($collectionData->getIncrementId());
            $orderStatusLabel = $this->statusConfig->getStatusLabelForScope(
                'customer',
                $collectionData->getStatus()
            );
            $vendorOrder->setStatusLabel($orderStatusLabel);
            $vendorOrder->setStatus($collectionData->getStatus());
            $vendorOrder->setTotalRefunded($collectionData->getTotalRefunded());
            $vendorOrder->setGrandTotal($collectionData->getGrandTotal());
            $vendorOrder->setCreatedAt($collectionData->getCreatedAt());
            $vendorOrder->setFirstName($collectionData->getFirstname());
            $vendorOrder->setLastName($collectionData->getLastname());
            $vendorOrder->setBillToName($collectionData->getBillToName());
            $vendorOrder->setShipToName($collectionData->getShipToName());
	    $vendorOrder->setCanCancel($collectionData->getCanCancel());
            $vendorOrder->setIsCustomerReviewExists(
                $this->checkIfCustomerReviewExists($collectionData->getVendorOrderId())
            );
            $response[] = $vendorOrder->getData();
        }
        return $response;
    }

    /**
     * @param $vendorOrderId
     * @return boolean
     */
    protected function checkIfCustomerReviewExists($vendorOrderId)
    {
        $collection = $this->vendorratingCollectionFactory->create()
            ->addFieldToFilter('vendor_order_id', $vendorOrderId)
            ->getFirstItem();
        return ($collection->getId()) ? true : false;
    }

    /**
     *
     * @param type $websiteId
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getStoresForWebsite($websiteId = null)
    {
        if (!$websiteId) {
            $websiteId = $this->storeManager->getStore()->getWebsiteId();
        }
        if (empty($this->storeIds[$websiteId])) {
            $this->storeIds[$websiteId] = [];
            $stores = $this->storeRepository->getList();
            foreach ($stores as $store) {
                if ($websiteId == $store["website_id"]) {
                    array_push($this->storeIds[$websiteId], $store["store_id"]);
                }
            }
        }
        return $this->storeIds[$websiteId];
    }

    /**
     * {@inheritDoc}
     */
    public function getByOriginalOrderId($orderId, $vendorId = null)
    {
        $vendorId = ($vendorId) ?? $this->userContext->getUserId();
        if ($orderId && $vendorId) {
            $orderData = $this->vendorOrderResource->getByOriginOrderId($orderId, $vendorId);
            if ($orderData && count($orderData) > 0) {
                $vendorOrder = $this->vendorOrderInterface->create()->setData($orderData);
                $vendorOrder->setStatusLabel($this->getStatusLabel('vendor', $orderData['status']));
                return $vendorOrder;
            }
        }
    }

    /**
     * Used specifically in seller area/app.
     * Need to maintain the status as per vendor scope in seller area. Default is customer scope.
     * {@inheritDoc}
     */
    public function getVendorOrderById($vendorOrderId)
    {
        $vendorOrder = $this->getById($vendorOrderId);
        /* Match the date and time with web.*/
        $vendorOrder->setCreatedAt($this->baseHelper->formatDate(
            $vendorOrder->getCreatedAt(),
            \IntlDateFormatter::MEDIUM,
            true
        ));
        return $vendorOrder->setStatusLabel($this->getStatusLabel('vendor', $vendorOrder->getStatus()));
    }

    /**
     * Get Customer order cancel reasons
     */
    public function getCustomerOrderCancelReason()
    {
        $reasons = $this->jsonHelper->jsonDecode(
            $this->jsonHelper->jsonEncode($this->salesHelper->getCustomerCancelOrderReason()),
            true
        );
        return $reasons;
    }
}
