<?php
/*
 * Copyright © 2019 rocket Bazaar. All rights reserved.
 * See COPYING.txt for license details
 */

namespace Magedelight\Rma\Model;

use Magento\Framework\Api\SortOrder;
use Magento\Rma\Model\Rma\Source\Status;
use Magento\Sales\Model\Order;
use Magento\Rma\Api\Data\RmaInterface;

class MdRmaManagement implements \Magedelight\Rma\Api\MdRmaManagementInterface
{

    /**
     * @var \Magedelight\Rma\Helper\Data
     */
    private $rbRmaHelper;

    /**
     * @var \Magento\Rma\Model\ResourceModel\Item\CollectionFactory
     */
    private $_itemsFactory;

    /**
     * @var \Magento\Rma\Api\RmaManagementInterface
     */
    private $rmaManagementInterface;

    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * Message manager
     *
     * @var \Magento\Rma\Api\RmaAttributesManagementInterface
     */
    protected $metadataService;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var \Magento\Rma\Model\Rma\StatusHistoryFactory
     */
    private $statusHistoryFactory;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var \Magento\Rma\Model\RmaFactory
     */
    private $rmaModelFactory;

    /**
     * @var \Magento\Rma\Model\Service\CommentManagement
     */
    private $rmaCommentsInterface;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    private $dateTime;

    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    private $orderFactory;

    /**
     * @var \Magento\Rma\Helper\Data
     */
    private $rmaHelper;

    /**
     * MdRmaManagement constructor.
     * @param \Magento\Authorization\Model\UserContextInterface $userContext
     * @param \Magento\Framework\Api\FilterBuilder $filterBuilder
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     * @param \Magento\Rma\Api\RmaManagementInterface $rmaManagementInterface
     * @param \Magento\Rma\Model\Service\CommentManagement $commentManagement
     * @param \Magento\Framework\Api\SortOrderBuilder $sortOrderBuilder
     * @param \Magento\Rma\Helper\Data $rmaHelperData
     * @param \Magento\Rma\Model\ResourceModel\Item\CollectionFactory $itemsFactory
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     * @param \Magedelight\Rma\Helper\Data $rbRmaHelper
     * @param \Magedelight\Vendor\Helper\Data $rbVendorHelperData
     * @param \Magento\Rma\Model\ResourceModel\Item $resorceItem
     * @param \Magento\Rma\Model\Service\RmaAttributesManagement $rmaAttributesManagement
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     * @param \Magento\Rma\Model\Rma\Status\HistoryFactory $statusHistoryFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Rma\Model\RmaFactory $rmaModelFactory
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $dateTime
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param \Magento\Rma\Helper\Data $rmaHelper
     */
    public function __construct(
        \Magento\Authorization\Model\UserContextInterface $userContext,
        \Magento\Framework\Api\FilterBuilder $filterBuilder,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Rma\Api\RmaManagementInterface $rmaManagementInterface,
        \Magento\Rma\Model\Service\CommentManagement $commentManagement,
        \Magento\Framework\Api\SortOrderBuilder $sortOrderBuilder,
        \Magento\Rma\Helper\Data $rmaHelperData,
        \Magento\Rma\Model\ResourceModel\Item\CollectionFactory $itemsFactory,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magedelight\Rma\Helper\Data $rbRmaHelper,
        \Magedelight\Vendor\Helper\Data $rbVendorHelperData,
        \Magento\Rma\Model\ResourceModel\Item $resorceItem,
        \Magento\Rma\Model\Service\RmaAttributesManagement $rmaAttributesManagement,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Rma\Model\Rma\Status\HistoryFactory $statusHistoryFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Rma\Model\RmaFactory $rmaModelFactory,
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        Status $rmaStatusSource
    ) {
        $this->userContext = $userContext;
        $this->filterBuilder = $filterBuilder;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->rmaManagementInterface = $rmaManagementInterface;
        $this->rmaCommentsInterface = $commentManagement;
        $this->sortOrderBuilder = $sortOrderBuilder;
        $this->rmaHelperData = $rmaHelperData;
        $this->_itemsFactory = $itemsFactory;
        $this->orderRepository = $orderRepository;
        $this->rbRmaHelper = $rbRmaHelper;
        $this->rbVendorHelperData = $rbVendorHelperData;
        $this->resorceItem = $resorceItem;
        $this->rmaAttributesManagement = $rmaAttributesManagement;
        $this->customerRepository = $customerRepository;
        $this->statusHistoryFactory = $statusHistoryFactory;
        $this->logger = $logger;
        $this->rmaModelFactory = $rmaModelFactory;
        $this->dateTime = $dateTime;
        $this->orderFactory = $orderFactory;
        $this->_rmaStatusSouceModel = $rmaStatusSource;
    }

    /**
     * @inheritdoc
     */
    public function getRmaList($limit = null, $currPage = null)
    {
        if ($limit === null) {
            $limit = 10;
        }
        if ($currPage === null) {
            $currPage = 1;
        }

        $customerId = $this->userContext->getUserId();
        if (!empty($customerId)) {
            $filter = $this->filterBuilder
                ->setField('customer_id')
                ->setValue($customerId)
                ->setConditionType('eq')
                ->create();
        }

        $this->searchCriteriaBuilder->addFilters([$filter]);
        $searchCriteria = $this->searchCriteriaBuilder->create();
        $sortOrder = $this->sortOrderBuilder->setField("entity_id")
            ->setDirection(SortOrder::SORT_DESC)->create();
        $searchCriteria->setSortOrders([$sortOrder]);
        $searchCriteria->setPageSize($limit);
        $searchCriteria->setCurrentPage($currPage);

        $response = $this->rmaManagementInterface->search($searchCriteria);

        $responseData = $response->getData();
        if (is_array($responseData)) {
            foreach ($responseData as $key => $rma) {
                $customer = $this->customerRepository->getById($rma['customer_id']);
                $responseData[$key]['shipfrom'] = $customer->getFirstname().' '.$customer->getLastname();
                $responseData[$key]['date_requested'] = $this->dateTime->gmtDate('m/d/y', $rma['date_requested']);
                $responseData[$key]['status'] = $this->_rmaStatusSouceModel->getItemLabel($rma['status']);
            }
        }

        $data[] = ["data" => $responseData, 'total_count' => $response->getTotalCount()];
        return $data;
    }

    /**
     * @inheritdoc
     */
    public function getById($id)
    {
        $customerId = $this->userContext->getUserId();
        if (!empty($customerId)) {
            $filter = $this->filterBuilder
                ->setField('customer_id')
                ->setValue($customerId)
                ->setConditionType('eq')
                ->setField('entity_id')
                ->setValue($id)
                ->setConditionType('eq')
                ->create();
        }
        $this->searchCriteriaBuilder->addFilters([$filter]);
        $searchCriteria = $this->searchCriteriaBuilder->create();

        $response["request_info"] = $this->rmaManagementInterface->search($searchCriteria)->getFirstItem()->getData();
        $customerId = $response["request_info"]["customer_id"];
        $customer = $this->customerRepository->getById($customerId);
        $response["request_info"]["customer_email"] = $customer->getEmail();
        $response["request_info"]["date_requested"] = $this->dateTime->gmtDate('m/d/y', $response["request_info"]["date_requested"]);
        $response["request_info"]["status"] = $this->_rmaStatusSouceModel->getItemLabel($response["request_info"]["status"]);
        $response["shipping_info"] = $this->rmaHelperData->getReturnAddressData();
        $response["item_collection"] = $this->getItemsForDisplay($id);
        $response["comment_collection"] = $this->rmaCommentsInterface->commentsList($id)->getData();

        return [$response];
    }

    /**
     * @param $rmaId
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getItemsForDisplay($rmaId)
    {
        /** @var $collection \Magento\Rma\Model\ResourceModel\Item\Collection */
        $collection = $this->_itemsFactory->create();
        $collection->addFieldToFilter('rma_entity_id', $rmaId)
            ->setOrder('order_item_id')
            ->setOrder('entity_id');
        $collection->addAttributeToSelect('*');

        $response = [];
        foreach ($collection as $item) {
            $rmaItems = $item->getData();
            $rmaItems['condition'] = $this->getAttributeLabel('condition', $item->getCondition());
            $rmaItems['resolution'] = $this->getAttributeLabel('resolution', $item->getResolution());
            $response[] = $rmaItems;
        }

        return $response;
    }

    /**
     * @param $attributeCode
     * @param $optionId
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getAttributeLabel($attributeCode, $optionId)
    {
        $options = $this->rmaAttributesManagement->getAttributeMetadata($attributeCode)->getOptions();
        $attributeOptions = [];
        foreach ($options as $key => $option) {
            $attributeOptions[$option->getValue()] = $option->getLabel();
        }
        return $attributeOptions[$optionId];
    }

    /**
     * @inheritdoc
     */
    public function getCreateReturnData($orderId, $vendorId)
    {
        $order = $this->orderFactory->create()->load($orderId);
        if (!$order) {
            return false;
        }

        $vendorObj = $this->rbVendorHelperData->getVendorDetails($vendorId);
        if (!$vendorObj) {
            return false;
        }

        /* OrderData*/
        $orderData['entity_id'] = $order->getEntityId();
        $orderData['increment_id'] = $order->getIncrementId();
        $orderData['customer_fullname'] = $order->getCustomerFirstname()." ".$order->getCustomerLastname();
        $orderData['customer_email'] = $order->getCustomerEmail();
        $orderData['supplier_name'] = $vendorObj->getName();
        $orderData['supplier_email'] = $vendorObj->getEmail();

        /* Shipping Address */
        $shippingAddressObj = $order->getShippingAddress();
        $shippingAddressArray = $shippingAddressObj->getData();

        /* Order Item Data */
        $orderItemsData = [];
        $orderItemsCollection = $this->resorceItem->getOrderItems($orderId);
        foreach ($orderItemsCollection as $item) {
            $itemVendorId = intval($item->getVendorId());
            if ($itemVendorId == $vendorId) {
                $orderItemsData[] = $item->getData();
                continue;
            }
        }

        /* Attribute Data i.e resolution, condition, reason, reason_other */
        $predefineAttributes = ['resolution', 'condition', 'reason', 'reason_other'];
        $attributeData = [];
        foreach ($predefineAttributes as $preAttribute) {

            $attributeObj = $this->rmaAttributesManagement->getAttributeMetadata($preAttribute);

            $attributeData[$preAttribute]['attribute_code'] = $attributeObj->getAttributeCode();
            $attributeData[$preAttribute]['frontend_label'] = $attributeObj->getFrontendLabel();
            $attributeData[$preAttribute]['store_label'] = $attributeObj->getStoreLabel();
            $attributeData[$preAttribute]['frontend_input'] = $attributeObj->getFrontendInput();
            $options = $attributeObj->getOptions();
            $optionsArray = [];
            foreach ($options as $opKey => $opVal) {
                if (empty($opVal->getValue())) {
                    continue;
                }
                $optionsArray[] = [
                    "value" => $opVal->getValue(),
                    "label" => $opVal->getLabel()
                ];
            }
            $attributeData[$preAttribute]['options'] = $optionsArray;

        }

        /* order id, order increment id, customer email, supplier name, supplier email */
        $response["order_data"] = $orderData;
        /* customer shipping information */
        $response["shipping_info"] = $shippingAddressArray;
        /* Item Collection data with pending items or reaming items */
        $response["item_collection"] = $orderItemsData;
        /* attribute data with options value */
        $response["attribute_data"] = $attributeData;

        return [$response];
    }

    /**
     * @inheritdoc
     */
    public function createReturn($rmaData)
    {
        $orderId = $rmaData['order_id'];
        $vendorId = $rmaData['vendor_id'];
        if (!$this->rmaHelperData->canCreateRma($orderId)) {
            return 'Can\'t able to create RMA for this order';
        }

        if (!empty($rmaData['items'])) {
            try {
                /** @var \Magento\Sales\Model\Order $order */
                $order = $this->orderRepository->get($orderId);

                /** @var Rma $rmaObject */
                $rmaObject = $this->buildRma($order, $rmaData);
                if (!$rmaObject->saveRma($rmaData)) {
                    return 'Something went wrong while saving RMA';
                }

                $statusHistory = $this->statusHistoryFactory->create();
                $statusHistory->setRmaEntityId($rmaObject->getEntityId());
                // Temporally Comment
                $statusHistory->sendNewRmaEmail();
                $statusHistory->saveSystemComment();

                if (isset($rmaData['rma_comment']) && !empty($rmaData['rma_comment'])) {
                    /** @var History $comment */
                    $comment = $this->statusHistoryFactory->create();
                    $comment->setRmaEntityId($rmaObject->getEntityId());
                    $comment->setComment($rmaData['rma_comment']);
                    $comment->setVendorId($rmaData['vendor_id']);
                    $comment->setVendorName($rmaData['vendor_name']);
                    $comment->setIsVisibleOnFront(true);
                    $this->rmaCommentsInterface->addComment($comment);
                }

                return 'You submitted Return #%1'.$rmaObject->getIncrementId();
            } catch (\Throwable $e) {
                $this->logger->critical($e->getMessage());
                return 'We can\'t create a return right now. Please try again later.';
            }
        } else {
            return 'Please add items to return';
        }
    }

    /**
     * Triggers save order and create history comment process
     *
     * @param Order $order
     * @param array $rmaData
     * @return RmaInterface
     */
    private function buildRma(Order $order, array $rmaData): RmaInterface
    {
        /** @var RmaInterface $rmaModel */
        $rmaModel = $this->rmaModelFactory->create();

        $rmaModel->setData(
            [
                'status' => Status::STATE_PENDING,
                'date_requested' => $this->dateTime->gmtDate(),
                'order_id' => $order->getId(),
                'order_increment_id' => $order->getIncrementId(),
                'store_id' => $order->getStoreId(),
                'customer_id' => $order->getCustomerId(),
                'order_date' => $order->getCreatedAt(),
                'customer_name' => $order->getCustomerName(),
                'customer_custom_email' => $rmaData['customer_custom_email'],
                'vendor_id' => $rmaData['vendor_id'],
                'vendor_name' => $rmaData['vendor_name'],
            ]
        );

        return $rmaModel;
    }
}
