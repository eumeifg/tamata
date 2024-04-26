<?php
/*
 * Copyright © 2018 Rocket Bazaar. All rights reserved.
 * See COPYING.txt for license details
 */

namespace MDC\Sales\Model;

use Magedelight\Sales\Api\Data\VendorOrderSearchResultInterfaceFactory;
use Magedelight\Sales\Model\Order as VendorOrder;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use MDC\Sales\Model\Source\Order\PickupStatus;

class OrderRepository extends \Magedelight\Sales\Model\OrderRepository
{

    /**
     * @var \MDC\Sales\Model\Source\Order\PickupStatus
     */
    protected $pickupStatuses;

    /**
     * @var \Magedelight\Catalog\Model\ProductFactory
     */
    protected $vendorProductFactory;

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
        \Magedelight\Base\Helper\Data $baseHelper,
        \MDC\Sales\Model\Source\Order\PickupStatus $pickupStatuses,
        \Magedelight\Catalog\Model\ProductFactory $vendorProductFactory,
        \Magento\Sales\Api\Data\OrderInterfaceFactory $salesOrderInterface
    ) {
        $this->pickupStatuses = $pickupStatuses;
        $this->salesOrderInterface = $salesOrderInterface;
        $this->vendorProductFactory = $vendorProductFactory;
        parent::__construct(
            $userContext,
            $orderCollectioninterface,
            $orderConfig,
            $orderCollectionFactory,
            $storeManager,
            $storeRepository,
            $vendorOrderInterface,
            $vendorOrderResource,
            $statusConfig,
            $vendorratingCollectionFactory,
            $vendorOrderCollectionFactory,
            $salesHelper,
            $jsonHelper,
            $searchResultsFactory,
            $collectionProcessor,
            $searchCriteriaBuilder,
            $subOrdersListing,
            $baseHelper
        );
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
                $includeStatuses = [VendorOrder::STATUS_PENDING, VendorOrder::STATUS_PROCESSING, VendorOrder::STATUS_CONFIRMED];
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
                $includeStatuses = [VendorOrder::STATUS_PACKED, VendorOrder::STATUS_SHIPPED];
                break;
            case 'intransit':
                $includeStatuses = [VendorOrder::STATUS_IN_TRANSIT, VendorOrder::STATUS_OUT_WAREHOUSE];
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

        $collection = $this->getSubOrdersCollection($vendorId);

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
            $newId = !empty($collectionData->getVendorOrderWithClassification()) ? $collectionData->getVendorOrderWithClassification() : $collectionData->getRvoVendorOrderId();
            $orderItems = $this->getProductIdFromOrder(
                                $collectionData->getOrderId() , 
                                $collectionData->getRvoVendorOrderId()
                          );
            $vendorProductId = $this->getVendorProductIdFromProductId($orderItems);

            $vendorOrder = $this->vendorOrderInterface->create();
            $vendorOrder->setEntityId($collectionData->getEntityId());
            $vendorOrder->setVendorOrderId($collectionData->getRvoVendorOrderId());
            $vendorOrder->setVendorId($collectionData->getRvoVendorId());
            $vendorOrder->setIncrementId($collectionData->getRvoIncrementId());
            $orderStatus = $this->getStatusLabel('vendor', $collectionData->getStatus());
            $vendorOrder->setproductId($orderItems);
            $vendorOrder->setVendorProductId($vendorProductId);
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
            $vendorOrder->setReadyToPickStatus(PickupStatus::READY_TO_PICK);
            $vendorOrder->setPickupStatus($collectionData->getPickupStatus());
            $vendorOrder->setPickupStatusLabel($this->pickupStatuses->getOptionText($collectionData->getPickupStatus()));
            $vendorOrder->setItems(null);
            $vendorOrder->setVendorOrderWithClassification($newId);
            $response[] = $vendorOrder->getData();
        }

        $searchResults->setItems($response);
        $searchResults->setTotalCount($total);
        return $searchResults->setItems($response);
    }

    /**
     * @return bool|\Magento\Sales\Model\ResourceModel\Order\Collection
     */
    public function getSubOrdersCollection($vendorId)
    {
        $collection = $this->vendorOrderCollectionFactory->create()
            ->addFieldToSelect(
                [
                    "rvo_vendor_order_id" => "vendor_order_id",
                    "rvo_vendor_id" => "vendor_id",
                    'rvo_increment_id' => "increment_id",
                    "status", "total_refunded",
                    'rvo_grand_total' => "grand_total",
                    "rvo_created_at" => "created_at",
                    "order_currency_code",
                    "vendor_id",
                    "order_id",
                    "cancelled_by",
                    'pickup_status',
                    'vendor_order_with_classification'
                ]
            )->addFieldToFilter(
                'main_table.vendor_id',
                $vendorId
            );
        return $collection;
    }

    public function getProductIdFromOrder($orderId , $vendorOrderId)
    {
         $order = $this->salesOrderInterface->create()->load($orderId);
         $orderItems = $order->getAllItems();
           foreach ($orderItems as $item) {
                 if($vendorOrderId == $item->getVendorOrderId())
                 {
                    $productId  = $item->getProductId();  
                 }
             }
            return $productId;
    }

    public function getVendorProductIdFromProductId($marketplaceProductId) {
		$vendorProduct = $this->vendorProductFactory->create()->getCollection()
                ->addFieldToFilter('marketplace_product_id', $marketplaceProductId)
                ->getFirstItem();

        return $vendorProduct->getVendorProductId();

	}

    /**
     * @param int|null $orderId
     * @return array
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
}
