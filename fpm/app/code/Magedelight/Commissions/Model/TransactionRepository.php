<?php
/*
 * Copyright © 2016 Rocket Bazaar. All rights reserved.
 * See COPYING.txt for license details
 */

namespace Magedelight\Commissions\Model;

use Magedelight\Commissions\Api\Data\TransactionSummarySearchResultInterfaceFactory;
use Magedelight\Commissions\Api\TransactionRepositoryInterface;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;

/**
 * Handle various vendor account actions
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class TransactionRepository implements TransactionRepositoryInterface
{

    /**
     * @var \Magedelight\Commissions\Model\ResourceModel\Reports\Commission\Payment\CollectionFactory
     */
    protected $paymentReportCollection;

    /**
     * @var \Magento\Framework\Pricing\Helper\Data
     */
    protected $priceHelper;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Authorization\Model\UserContextInterface
     */
    protected $userContext;

    /**
     * @var \RB\Commissions\Model\ResourceModel\Commission\Payment\CollectionFactory
     */
    protected $collectionFactory;

    /**
     * Search result factory
     *
     * @var \Magedelight\Commissions\Api\Data\TransactionSummarySearchResultInterfaceFactory
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

    protected $_amountBalance = [];

    /**
     * TransactionRepository constructor.
     * @param ResourceModel\Commission\Payment\CollectionFactory $collectionFactory
     * @param \Magento\Authorization\Model\UserContextInterface $userContext
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Pricing\Helper\Data $priceHelper
     * @param ResourceModel\Reports\Commission\Payment\CollectionFactory $paymentReportCollection
     * @param Source\PaymentStatus $paymentStatus
     * @param TransactionSummarySearchResultInterfaceFactory $searchResultsFactory
     * @param CollectionProcessorInterface $collectionProcessor
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        \Magedelight\Commissions\Model\ResourceModel\Commission\Payment\CollectionFactory $collectionFactory,
        \Magento\Authorization\Model\UserContextInterface $userContext,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Pricing\Helper\Data $priceHelper,
        \Magedelight\Commissions\Model\ResourceModel\Reports\Commission\Payment\CollectionFactory $paymentReportCollection,
        \Magedelight\Commissions\Model\Source\PaymentStatus $paymentStatus,
        TransactionSummarySearchResultInterfaceFactory $searchResultsFactory,
        CollectionProcessorInterface $collectionProcessor,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->userContext = $userContext;
        $this->storeManager = $storeManager;
        $this->priceHelper = $priceHelper;
        $this->paymentReportCollection = $paymentReportCollection;
        $this->paymentStatus = $paymentStatus;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->collectionProcessor = $collectionProcessor;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * { @inheritDoc }
     */
    public function getList($searchCriteria = null, $searchTerm = null)
    {
        $searchResults = $this->searchResultsFactory->create();
        if (!$searchCriteria) {
            $searchCriteria = $this->searchCriteriaBuilder->create();
        } else {
            foreach ($searchCriteria->getFilterGroups() as $filterGroup) {
                foreach ($filterGroup->getFilters() as $filter) {
                    if ($filter->getField() == 'created_at' &&
                        ($filter->getConditionType() == 'lteq' || $filter->getConditionType() == 'to')) {
                        $filter->setValue(date('Y-m-d H:i:s', strtotime(
                            '+23 hours 59 minutes 59 seconds',
                            strtotime($filter->getValue())
                        )));
                    }
                }
            }
        }
        /** @var \Magedelight\Commissions\Api\Data\TransactionSummarySearchResultInterface $searchResults */
        $searchResults->setSearchCriteria($searchCriteria);

        $store = $this->storeManager->setCurrentStore($this->storeManager->getStore()->getId());
        $vendorId = $this->userContext->getUserId();

        $collection = $this->collectionFactory->create();

        $collection->getSelect()->join(
            ['rvo' => 'md_vendor_order'],
            "main_table.vendor_order_id = rvo.vendor_order_id",
            ['increment_id', 'vendor_order_id', 'grand_total', 'rvo_shipping_amount' => 'rvo.shipping_amount',
                'total_refunded','order_currency_code']
        );

        $collection->addFieldToFilter('main_table.vendor_id', ['eq' => $vendorId]);
        $collection->addFieldToFilter(
            'main_table.website_id',
            ['eq' => $this->storeManager->getWebsite()->getWebsiteId()]
        );
        $collection->setOrder('created_at', 'desc');
        $collection->addFilterToMap('created_at', 'main_table.created_at');
        $collection->addFilterToMap('status', 'main_table.status');
        $this->collectionProcessor->process($searchCriteria, $collection);

        $result = [];
        foreach ($collection as $collectionData) {
            $collectionData->setOrderNumber($collectionData->getIncrementId());
            $collectionData->setCreatedAt($collectionData->getCreatedAt());
            $collectionData->setGrandTotal($this->formatAmount($collectionData->getGrandTotal(), $store));
            $collectionData->setShippingAmount($this->formatAmount($collectionData->getShippingAmount(), $store));
            $collectionData->setTotalRefunded($this->formatAmount($collectionData->getTotalRefunded(), $store));
            $collectionData->setTotalCommission($this->formatAmount($collectionData->getTotalCommission(), $store));
            $collectionData->setMarketplaceFee($this->formatAmount($collectionData->getMarketplaceFee(), $store));
            $collectionData->setCancellationFee($this->formatAmount($collectionData->getCancellationFee(), $store));
            $collectionData->setServiceTax($this->formatAmount($collectionData->getServiceTax(), $store));
            $collectionData->setTotalAmount($this->formatAmount($collectionData->getTotalAmount(), $store));
            $collectionData->setStatus($collectionData->getStatus());
            $collectionData->setStatusLabel(
                $this->paymentStatus->getOptionText($collectionData->getStatus())->getText()
            );
            $collectionData->setAction($collectionData->getVendorPaymentId());
            $result[] = $collectionData->getData();
        }

        $searchResults->setAmountBalance($this->formatAmount($this->getAmountBalance(), $store));
        $searchResults->setTotalCount($collection->getSize());
        return $searchResults->setItems($result);
    }

    /**
     * @return mixed
     */
    protected function getAmountBalance()
    {
        $collection = $this->paymentReportCollection->create();
        $this->_amountBalance = $collection->calculateAmountBalanceForVendor()->getFirstItem()->convertToArray();
        return $this->_amountBalance['amount_balance'];
    }

    /**
     * @param $amount
     * @param $store
     * @return float|string
     */
    protected function formatAmount($amount, $store)
    {
        return $this->priceHelper->currencyByStore($amount, $store, true, false);
    }
}
