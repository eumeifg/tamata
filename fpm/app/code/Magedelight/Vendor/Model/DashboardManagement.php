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

use Magedelight\Catalog\Api\Data\VendorProductInterfaceFactory;
use Magedelight\Catalog\Api\Data\VendorProductSearchResultInterfaceFactory;
use Magedelight\Catalog\Model\ProductFactory as VendorProductFactory;
use Magedelight\Commissions\Model\ResourceModel\Reports\Commission\Payment\CollectionFactory as PaymentCollectionFactory;
use Magedelight\MobileInit\Api\Data\MobilePriceFormatDataInterfaceFactory as MobilePriceFormatFactory;
use Magedelight\Sales\Model\ResourceModel\Reports\Order\CollectionFactory as SalesCollectionFactory;
use Magedelight\Vendor\Api\Data\StoreDataInterface;
use Magedelight\Vendor\Api\Data\StoreDataInterfaceFactory;
use Magedelight\Vendor\Api\Data\VendorDashboardInterface;
use Magedelight\Vendor\Api\VendorRepositoryInterface;
use Magedelight\Vendor\Model\Source\Status as VendorStatus;
use Magento\Catalog\Model\Product\Type;
use Magento\Directory\Helper\Data as DirectoryHelper;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Module\Manager as ModuleManager;
use Magento\Framework\Pricing\Helper\Data as PriceHelper;
use Magento\Store\Model\StoreManagerInterface;
use Magedelight\Sales\Api\Data\VendorOrderSearchResultInterfaceFactory;
use Magedelight\Sales\Model\Order as VendorOrder;

class DashboardManagement implements \Magedelight\Vendor\Api\DashboardManagementInterface
{
    protected $calculatedSales = [];

    protected $amountPaid = [];

    protected $productSold = [];

    protected $paidTransactions = null;

    protected $amountBalance = [];

    /**
     * @var StoreDataInterfaceFactory
     */
    protected $storeDataFactory;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var PaymentCollectionFactory
     */
    protected $paymentcollectionFactory;

    /**
     * @var SalesCollectionFactory
     */
    protected $salesCollectionFactory;

    /**
     * @var ModuleManager
     */
    protected $moduleManager;

    /**
     * @var VendorProductFactory
     */
    protected $vendorProductFactory;

    /**
     * @var VendorProductSearchResultInterfaceFactory
     */
    protected $vendorProductItems;

    /**
     * @var VendorProductInterfaceFactory
     */
    protected $vendorProductInterface;

    /**
     * @var \Magedelight\Vendor\Helper\Data
     */
    protected $vendorHelper;

    /**
     * @var PriceHelper
     */
    protected $priceHelper;

    /**
     * @var \Magento\Sales\Model\Order\Item
     */
    protected $saleItem;

    /**
     * @var \Magedelight\Vendor\Api\Data\DashboardOverviewInterfaceFactory
     */
    protected $dashboardOverviewFactory;

    /**
     * @var \Magedelight\Backend\Model\UrlInterface
     */
    protected $url;

    /**
     * @var \Magedelight\Vendor\Helper\Dashboard\Data
     */
    protected $dashboardHelper;

    /**
     * @var \Magedelight\Vendor\Block\Sellerhtml\Dashboard\Orders\Sales
     */
    protected $salesSummary;

    /**
     * @var \Magedelight\Vendor\Api\Data\SalesSummaryInterfaceFactory
     */
    protected $salesSummaryInterface;

    /**
     * @var \Magedelight\Sales\Api\OrderRepositoryInterface
     */
    protected $vendorOrderRepository;

    /**
     * @var Vendorrating
     */
    protected $vendorRating;

    /**
     * @var \Magedelight\Vendor\Api\Data\VendorReviewInterfaceFactory
     */
    protected $ratingAvg;

    /**
     * @var \Magedelight\Vendor\Api\Data\VendorDashboardInterfaceFactory
     */
    protected $vendorDashboardFactory;

    /**
     * @var \Magedelight\Backend\Model\Auth\Session
     */
    protected $authSession;

    /**
     * @var \Magento\Authorization\Model\UserContextInterface
     */
    protected $userContext;

    /**
     * @var \Magento\Framework\App\State
     */
    protected $state;

    /**
     * @var VendorRepositoryInterface
     */
    protected $vendorRepository;

    /**
     * @var VendorStatus
     */
    protected $vendorStatus;

    /**
     * @var \Magento\Framework\Model\AbstractModel
     */
    protected $vendorAcl;

    /**
     * @var \Magento\Eav\Api\AttributeRepositoryInterface
     */
    protected $attributeRepository;

    /**
     * @var \Magento\Sales\Api\Data\OrderItemInterfaceFactory
     */
    protected $orderItemInterfaceFactory;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var \Magento\Framework\Locale\FormatInterface
     */
    protected $localeFormat;

    /**
     * @var \Magento\Directory\Model\CurrencyFactory
     */
    protected $currencyFactory;

    /**
     * @var MobilePriceFormatFactory
     */
    protected $mobilePriceFormatFactory;

    /**
     * DashboardManagement constructor.
     * @param StoreDataInterfaceFactory $storeDataFactory
     * @param StoreManagerInterface $storeManager
     * @param PaymentCollectionFactory $paymentcollectionFactory
     * @param SalesCollectionFactory $salesCollectionFactory
     * @param ModuleManager $moduleManager
     * @param VendorProductFactory $vendorProductFactory
     * @param VendorProductSearchResultInterfaceFactory $vendorProductItems
     * @param VendorProductInterfaceFactory $vendorProductInterface
     * @param \Magedelight\Vendor\Helper\Data $vendorHelper
     * @param PriceHelper $priceHelper
     * @param \Magento\Sales\Model\Order\Item $saleItem
     * @param \Magedelight\Vendor\Api\Data\DashboardOverviewInterfaceFactory $dashboardOverviewFactory
     * @param \Magedelight\Backend\Model\UrlInterface $url
     * @param \Magedelight\Vendor\Helper\Dashboard\Data $dashboardHelper
     * @param \Magedelight\Vendor\Block\Sellerhtml\Dashboard\Orders\Sales $salesSummary
     * @param \Magedelight\Vendor\Api\Data\SalesSummaryInterfaceFactory $salesSummaryInterface
     * @param \Magedelight\Sales\Api\OrderRepositoryInterface $vendorOrderRepository
     * @param Vendorrating $vendorRating
     * @param \Magedelight\Vendor\Api\Data\VendorReviewInterfaceFactory $ratingAvg
     * @param \Magedelight\Vendor\Api\Data\VendorDashboardInterfaceFactory $vendorDashboardFactory
     * @param \Magedelight\Backend\Model\Auth\Session $authSession
     * @param \Magento\Authorization\Model\UserContextInterface $userContext
     * @param \Magento\Framework\App\State $state
     * @param VendorRepositoryInterface $vendorRepository
     * @param VendorStatus $vendorStatus
     * @param \Magento\Framework\Model\AbstractModel $vendorAcl
     * @param \Magento\Eav\Api\AttributeRepositoryInterface $attributeRepository
     * @param \Magento\Sales\Api\Data\OrderItemInterfaceFactory $orderItemInterfaceFactory
     * @param RequestInterface $request
     * @param \Magento\Framework\Locale\FormatInterface $localeFormat
     * @param \Magento\Directory\Model\CurrencyFactory $currencyFactory
     * @param MobilePriceFormatFactory $mobilePriceFormatFactory
     */
    public function __construct(
        StoreDataInterfaceFactory $storeDataFactory,
        StoreManagerInterface $storeManager,
        PaymentCollectionFactory $paymentcollectionFactory,
        SalesCollectionFactory $salesCollectionFactory,
        ModuleManager $moduleManager,
        VendorProductFactory $vendorProductFactory,
        VendorProductSearchResultInterfaceFactory $vendorProductItems,
        VendorProductInterfaceFactory $vendorProductInterface,
        \Magedelight\Vendor\Helper\Data $vendorHelper,
        PriceHelper $priceHelper,
        \Magento\Sales\Model\Order\Item $saleItem,
        \Magedelight\Vendor\Api\Data\DashboardOverviewInterfaceFactory $dashboardOverviewFactory,
        \Magedelight\Backend\Model\UrlInterface $url,
        \Magedelight\Vendor\Helper\Dashboard\Data $dashboardHelper,
        \Magedelight\Vendor\Block\Sellerhtml\Dashboard\Orders\Sales $salesSummary,
        \Magedelight\Vendor\Api\Data\SalesSummaryInterfaceFactory $salesSummaryInterface,
        \Magedelight\Sales\Api\OrderRepositoryInterface $vendorOrderRepository,
        \Magedelight\Vendor\Model\Vendorrating $vendorRating,
        \Magedelight\Vendor\Api\Data\VendorReviewInterfaceFactory $ratingAvg,
        \Magedelight\Vendor\Api\Data\VendorDashboardInterfaceFactory $vendorDashboardFactory,
        \Magedelight\Backend\Model\Auth\Session $authSession,
        \Magento\Authorization\Model\UserContextInterface $userContext,
        \Magento\Framework\App\State $state,
        VendorRepositoryInterface $vendorRepository,
        VendorStatus $vendorStatus,
        \Magento\Framework\Model\AbstractModel $vendorAcl,
        \Magento\Eav\Api\AttributeRepositoryInterface $attributeRepository,
        \Magento\Sales\Api\Data\OrderItemInterfaceFactory $orderItemInterfaceFactory,
        RequestInterface $request,
        \Magento\Framework\Locale\FormatInterface $localeFormat,
        \Magento\Directory\Model\CurrencyFactory $currencyFactory,
        MobilePriceFormatFactory $mobilePriceFormatFactory,
        VendorOrderSearchResultInterfaceFactory $searchResultsFactory,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magedelight\Sales\Model\Order\Listing $subOrdersListing,
        CollectionProcessorInterface $collectionProcessor
    ) {
        $this->storeDataFactory = $storeDataFactory;
        $this->storeManager = $storeManager;
        $this->paymentcollectionFactory = $paymentcollectionFactory;
        $this->salesCollectionFactory = $salesCollectionFactory;
        $this->moduleManager = $moduleManager;
        $this->vendorProductFactory = $vendorProductFactory;
        $this->vendorProductItems = $vendorProductItems;
        $this->vendorProductInterface = $vendorProductInterface;
        $this->vendorHelper = $vendorHelper;
        $this->priceHelper = $priceHelper;
        $this->saleItem = $saleItem;
        $this->dashboardOverviewFactory = $dashboardOverviewFactory;
        $this->url = $url;
        $this->dashboardHelper = $dashboardHelper;
        $this->salesSummary = $salesSummary;
        $this->salesSummaryInterface = $salesSummaryInterface;
        $this->vendorOrderRepository = $vendorOrderRepository;
        $this->vendorRating = $vendorRating;
        $this->ratingAvg = $ratingAvg;
        $this->vendorDashboardFactory = $vendorDashboardFactory;
        $this->authSession = $authSession;
        $this->userContext = $userContext;
        $this->state = $state;
        $this->vendorRepository = $vendorRepository;
        $this->vendorStatus = $vendorStatus;
        $this->vendorAcl = $vendorAcl;
        $this->attributeRepository = $attributeRepository;
        $this->orderItemInterfaceFactory = $orderItemInterfaceFactory;
        $this->request = $request;
        $this->localeFormat = $localeFormat;
        $this->currencyFactory = $currencyFactory;
        $this->mobilePriceFormatFactory = $mobilePriceFormatFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->subOrdersListing = $subOrdersListing;
        $this->collectionProcessor = $collectionProcessor;
    }

    /**
     * @return VendorDashboardInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Zend_Db_Select_Exception
     */
    public function vendorDashboard()
    {
        $dashboardOverviewData = [];

        if ($this->authSession->getUser() == null) {
            $vendorId = $this->userContext->getUserId();
        } else {
            $vendorId = $this->authSession->getUser()->getId();
        }

        /*$this->getVendorDashboardMenu();*/

        $apiFlag = false;

        if ($this->state->getAreaCode() === 'webapi_rest') {
            $apiFlag = true;
        }

        $vendorDashboardData = $this->vendorDashboardFactory->create();

        $vendorDashboardData->setVendorId($vendorId);
        $vendor = $this->vendorRepository->getById($vendorId);
        $vendorDashboardData->setStatus($this->vendorStatus->getOptionText($vendor->getStatus()));
        $vendorDashboardData->setStatusId($vendor->getStatus());
        $vendorDashboardData->setVendorEmail($vendor->getEmail());
        $hasParent = $this->vendorAcl->getLoggedVendorRoleId($vendorId);
        $isSubUser = false;
        if (is_object($hasParent) && $hasParent->getParentId() != null) {
            if ($hasParent->getParentId() != $vendorId && $hasParent->getParentId() != 0) {
                $isSubUser = true;
            }
        }

        $statusMsg = $this->vendorHelper->getStatusMsg(
            $vendor->getStatus(),
            $isSubUser,
            $vendor->getStatusDescription()
        );
        if (is_array($statusMsg) && isset($statusMsg)) {
            $vendorDashboardData->setStatusMsg($statusMsg['msg']);
        } elseif (is_object($statusMsg) && $statusMsg->getText() != '') {
            $vendorDashboardData->setStatusMsg($statusMsg);
        }
        /*
         * getLifetimeSales
         */
        if (empty($this->calculatedSales)) {
            $this->calculatedSales = $this->getCollection()->calculateSales()->getFirstItem()->convertToArray();
        }

        if ($apiFlag) {
            $vendorDashboardData->setLifeTimeSales(floatval($this->calculatedSales['lifetime']));
        } else {
            $vendorDashboardData->setLifeTimeSales(
                $this->priceHelper->currency(floatval($this->calculatedSales['lifetime']))
            );
        }

        /*
         * getAverageOrder
         */
        if (empty($this->calculatedSales)) {
            $this->calculatedSales = $this->getCollection()->calculateSales()->getFirstItem()->convertToArray();
        }

        if ($apiFlag) {
            $vendorDashboardData->setAverageOrder(floatval($this->calculatedSales['average']));
        } else {
            $vendorDashboardData->setAverageOrder(
                $this->priceHelper->currency(floatval($this->calculatedSales['average']))
            );
        }

        /*
         * getTransactions
         */
        if (empty($this->calculatedSales)) {
            $this->calculatedSales = $this->getCollection()->calculateSales()->getFirstItem()->convertToArray();
        }

        $vendorDashboardData->setOrderCount(intval($this->calculatedSales['orders_count']));

        /*
         * getProductsSold
         */
        if (empty($this->productSold)) {
            $this->productSold = $this->getCollection()->calculateProductsSold()->getFirstItem()->convertToArray();
        }

        $vendorDashboardData->setProductsSold(intval($this->productSold['product_sold']));

        /*
         * getAmountPaid
         */
        if (empty($this->amountPaid)) {
            $this->amountPaid = $this->getPaymentCollection()->calculateAmountPaidToVendor()
                ->getFirstItem()->convertToArray();
        }

        if ($apiFlag) {
            $vendorDashboardData->setAmountPaid(floatval($this->amountPaid['amount_paid']));
        } else {
            $vendorDashboardData->setAmountPaid(
                $this->priceHelper->currency(floatval($this->amountPaid['amount_paid']))
            );
        }

        /*
         * getAmountBalance
         */
        if (empty($this->amountBalance)) {
            $this->amountBalance = $this->getPaymentCollection()->calculateAmountBalanceForVendor()
                ->getFirstItem()->convertToArray();
        }

        if ($apiFlag) {
            $vendorDashboardData->setAmountBalance(floatval($this->amountBalance['amount_balance']));
        } else {
            $vendorDashboardData->setAmountBalance(
                $this->priceHelper->currency(floatval($this->amountBalance['amount_balance']))
            );
        }

        /*
         * getAmountBalanceTransaction
         */
        if (empty($this->paidTransactions)) {
            $this->paidTransactions = $this->getPaymentCollection()->calculateTransactionNotPaid()->getSize();
        }

        $vendorDashboardData->setAmountBalanceTransaction(intval($this->paidTransactions));

        /*
         * getAmountBalanceWithoutFormat
         */
        if (empty($this->amountBalance)) {
            $this->amountBalance = $this->getPaymentCollection()->calculateAmountBalanceForVendor()
                ->getFirstItem()->convertToArray();
        }

        $vendorDashboardData->setAmountBalanceWithoutFormat(floatval($this->amountBalance['amount_balance']));
        /*
         * getBestSellingForVendor
         */
        $vendorDashboardData->setBestSellingItems($this->getBestSellingForVendor($vendorId));

        /*
         * getLastApproveItem
         */
        $vendorDashboardData->setLastApprovedItems($this->getLastApproveItems($vendorId)->getItems());

        /*
        * Set Overview Data
        *
        */
        $dashboardOverview = $this->dashboardOverviewFactory->create();

        $dashboardOverview->setLiveProducts(
            $this->getDashboardProducts($vendorId, 'liveInStock')
        );

        $dashboardOverview->setOutOfStockProducts(
            $this->getDashboardProducts($vendorId, 'liveOutOfStock')
        );
        //$this->vendorOrderRepository->getVendorOrders('new')->getTotalCount()
        $dashboardOverview->setNewOrders(
            $this->getVendorOrders('new', $vendorId)
        );

        //$this->vendorOrderRepository->getVendorOrders('pack')->getTotalCount()
        $dashboardOverview->setToBeShipped(
            $this->getVendorOrders('pack', $vendorId)
        );
        $dashboardOverview->setSalesSummary(
            $this->getSalesSummary($vendorId)
        );

        $dashboardOverviewData[] = $dashboardOverview->getData();
        $vendorDashboardData->setDashboardOverview($dashboardOverviewData);

        if ($apiFlag) {
            /* Set stores in dashboard data for store swiching in API. */
            $storeId = $this->request->getParam('storeId');
            if (empty($storeId)) {
                $storeId = $this->storeManager->getStore()->getId();
            }
            $stores = $this->getAvailableStores($storeId);
            $vendorDashboardData->setAvailableStores($stores);
        }

        /* Set current currency */
        $storeData = $this->storeManager->getStore();
        $currencyCode = $storeData->getDefaultCurrencyCode();
        $currencyModel = $this->currencyFactory->create()->load($currencyCode);
        $currencySymbol = $currencyModel->getCurrencySymbol() ? $currencyModel->getCurrencySymbol() : $currencyCode;
        $priceFormat = $this->localeFormat->getPriceFormat(
            $storeData->getConfig(DirectoryHelper::XML_PATH_DEFAULT_LOCALE),
            $currencyCode
        );
        $priceFormatData = $this->processPriceFormat($priceFormat);
        $vendorDashboardData->setPriceFormat($priceFormatData);
        $vendorDashboardData->setCurrentCurrency($currencyCode);
        $vendorDashboardData->setCurrentCurrencySymbol($currencySymbol);

        /*
         * getTransactionSummaryUrl
         */
        $vendorDashboardData->setTransactionSummaryUrl(
            $this->url->getUrl('rbsales/transaction/summary', ['tab' => '3,0'])
        );

        /*
         * getApprovedProductUrl
         */
        $vendorDashboardData->setApprovedProductUrl(
            $this->url->getUrl(
                'rbcatalog/listing/index',
                ['tab' => '1,0', 'vpro' => 'approve', 'sfrm' => 'nl']
            )
        );

        /*
         * getAvgRating
         */
        $vendorDashboardData->setRatingAvg(
            $this->getAverageRating($vendorId)
        );

        return $vendorDashboardData;
    }

    /**
     * @param array $priceFormat
     * @return array
     */
    protected function processPriceFormat(array $priceFormat)
    {
        $mobilePriceFormat = $this->mobilePriceFormatFactory->create();
        $mobilePriceFormat->setPrecision($priceFormat['precision']);
        $mobilePriceFormat->setDecimalSymbol($priceFormat['decimalSymbol']);
        $priceFormatData[] = $mobilePriceFormat->getData();
        return $priceFormatData;
    }

    /**
     * @param $storeId
     * @return StoreDataInterface[]|[]
     */
    public function getAvailableStores($storeId)
    {
        $stores = $this->storeManager->getStores($withDefault = false);
        $currentStore = $storeId;
        $isCurrent = false;
        $locales = [];
        foreach ($stores as $store) {
            $isCurrent = false;
            $locale = $this->storeDataFactory->create();
            $locale->setId($store->getStoreId());
            $locale->setLabel($store->getName());
            $locale->setCode($store->getCode());
            if ($currentStore == $store->getStoreId()) {
                $isCurrent = true;
            }
            $locale->setIsSelected($isCurrent);
            $locales[] = $locale->getData();
        }
        return $locales;
    }

    /**
     * @return \Magedelight\Sales\Model\ResourceModel\Reports\Order\Collection|DashboardManagement
     */
    public function getCollection()
    {
        if (!$this->moduleManager->isEnabled('Magento_Reports')) {
            return $this;
        }

        $collection = $this->salesCollectionFactory->create();
        /* ->addItemCountExpr()->joinCustomerName('customer')->orderByCreatedAt(); */

        return $collection;
    }

    /**
     * @return \Magedelight\Commissions\Model\ResourceModel\Reports\Commission\Payment\Collection|DashboardManagement
     */
    public function getPaymentCollection()
    {
        if (!$this->moduleManager->isEnabled('Magento_Reports')) {
            return $this;
        }
        $collection = $this->paymentcollectionFactory->create();
        /* ->addItemCountExpr()->joinCustomerName('customer')->orderByCreatedAt(); */
        return $collection;
    }

    /**
     * @param $vendorId
     * @return mixed
     */
    public function getLastApproveItems($vendorId)
    {
        $collectiongetLastApproveItem = $this->vendorProductFactory->create()->getCollection();
        $collectiongetLastApproveItem->addFieldToFilter('main_table.vendor_id', $vendorId)
            ->addFieldToFilter('rbvpw.status', '0')
            ->setPageSize(5);
        $collectiongetLastApproveItem->getSelect()->order('approved_at DESC')->distinct(true);
        if (!empty($this->vendorHelper->getConfigValue('vendor/dashboard_summary/approved_products'))) {
            $collectiongetLastApproveItem->setPageSize(
                $this->vendorHelper->getConfigValue('vendor/dashboard_summary/approved_products')
            )->setCurPage(1);
        }
        $lastApprovedItems = [];
        foreach ($collectiongetLastApproveItem as $lastApproved) {
            $vendorProductInterface = $this->vendorProductInterface->create();
            $vendorProductInterface->setData($lastApproved->getData());
            $lastApprovedItems[] = $vendorProductInterface->getData();
        }
        $approvedItems = $this->vendorProductItems->create();
        $approvedItems->setItems($lastApprovedItems);
        return $approvedItems;
    }

    /**
     * @param $vendorId
     * @param $type
     * @return mixed
     */
    public function getDashboardProducts($vendorId, $type)
    {
        $collection = $this->vendorProductFactory->create()->getCollection();
        $collection->addFieldToFilter('main_table.vendor_id', $vendorId)
            ->addFieldToFilter('main_table.type_id', ['eq' => Type::DEFAULT_TYPE ]);

        switch ($type) {
            case 'liveInStock':
                $collection->addFieldToFilter('rbvpw.status', ['eq' => 1])
                    ->addFieldToFilter('qty', ['gteq' => 1]);
                break;

            case 'liveOutOfStock':
                $collection->addFieldToFilter('rbvpw.status', ['eq' => 1])
                    ->addFieldToFilter('qty', ['lteq' => 0]);
                break;

            default:
                # code...
                break;
        }
        $count = $collection->count();
        return $count;
    }

    protected function getBestSellingForVendor($vendorId)
    {
        $collectiongetBestSellingForVendor = $this->saleItem->getCollection();
        $collectiongetBestSellingForVendor->getSelect()->joinLeft(
            ['rbvrt' => 'md_vendor_order'],
            "main_table.order_id = rbvrt.order_id",
            ['rbvrt.status']
        );
        $collectiongetBestSellingForVendor->getSelect()->joinLeft(
            ['prod' => 'catalog_product_entity'],
            "main_table.product_id = prod.entity_id",
            ['sku']
        )->joinLeft(
            ['cpev' => 'catalog_product_entity_varchar'],
            'cpev.row_id=prod.row_id AND cpev.attribute_id=' . $this->getAttributeIdofProductName() . '',
            ['vendorpname' => 'value']
        )->joinLeft(
            ['cpevw' => 'catalog_product_entity_varchar'],
            'cpevw.row_id=prod.row_id AND cpevw.attribute_id=' . $this->getAttributeIdofProductUrl() . '',
            ['vendorpurl' => 'value']
        )->distinct(true);

        $collectiongetBestSellingForVendor->getSelect()
            ->columns('SUM(main_table.qty_ordered) as total')
            ->group('main_table.product_id');
        $collectiongetBestSellingForVendor->getSelect()->where(
            "rbvrt.status = 'complete' AND main_table.vendor_id = '" . $vendorId . "'"
        )->limit(5);
        $collectiongetBestSellingForVendor->getSelect()->order("SUM(main_table.qty_ordered) DESC");

        if (!empty($this->vendorHelper->getConfigValue('vendor/dashboard_summary/best_sellers'))) {
            $collectiongetBestSellingForVendor->setPageSize(
                $this->vendorHelper->getConfigValue('vendor/dashboard_summary/best_sellers')
            )->setCurPage(1);
        }

        $bestSellerOrders = [];

        foreach ($collectiongetBestSellingForVendor as $bestSellers) {
            $orderItems = $this->orderItemInterfaceFactory->create();
            $orderItems->setData($bestSellers->getData());
            /*$extensionAttributes = $orderItems->getExtensionAttributes();
            $vendorOrderItems = $this->vendorOrderItem->create();
            $vendorOrderItems->setVendorOrderStatus($bestSellers->getStatus());
            $vendorOrderItems->setVendorProductName($bestSellers->getVendorpname());
            $vendorOrderItems->setVendorProductUrl($bestSellers->getVendorpurl());
            $vendorOrderItems->setTotal($bestSellers->getTotal());
            $extensionAttributes->setVendorOrderData($vendorOrderItems->getData());
            $orderItems->setExtensionAttributes($extensionAttributes);*/

            $bestSellerOrders[] = $orderItems->getData();
        }
        return $bestSellerOrders;
    }

    /**
     * @param $vendorId
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Zend_Db_Select_Exception
     */
    protected function getSalesSummary($vendorId)
    {
        $data = [];
        foreach ($this->dashboardHelper->getDatePeriods() as $value => $label) {
            $this->salesSummary->getCollection($value, $vendorId);
            $summary = $this->salesSummaryInterface->create();
            $summary->setLabel($label->getText());
            $summary->setSaleTotal(strip_tags($this->salesSummary->getTotals()['Revenue']['value']));
            $summary->setTotalOrders($this->salesSummary->getTotals()['Quantity']['value']);
            $data[] = $summary->getData();
        }
        return $data;
    }

    /**
     * @param $vendorId
     * @return mixed
     */
    protected function getAverageRating($vendorId)
    {
        $collectiongetAvgRating = $this->vendorRating->getCollection()->addFieldToSelect('vendor_id')
            ->addFieldToFilter('vendor_id', $vendorId)->addFieldToFilter('is_shared', 1);

        $collectiongetAvgRating->getSelect()->joinLeft(
            ['rvrt' => 'md_vendor_rating_rating_type'],
            "main_table.vendor_rating_id = rvrt.vendor_rating_id",
            ["ROUND(SUM(`rvrt`.`rating_avg`)/(SELECT  count(*) FROM md_vendor_rating WHERE (md_vendor_rating.vendor_id = '" . $vendorId . "') AND (md_vendor_rating.is_shared = 1))) as rating_avg"]
        );
        $ratingAvg = $this->ratingAvg->create();
        $ratingAvg->setRatingAvg($collectiongetAvgRating->getFirstItem()->getRatingAvg());
        $ratingAvgData = $ratingAvg->getData();
        return $ratingAvgData;
    }

    /**
     * @return int
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function getAttributeIdofProductName()
    {
        $productNameAttributeId = $this->attributeRepository->get('catalog_product', 'name')->getId();
        return $productNameAttributeId;
    }

    /**
     * @return int
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function getAttributeIdofProductUrl()
    {
        $productUrlAttributeId = $this->attributeRepository->get('catalog_product', 'url_key')->getId();
        return $productUrlAttributeId;
    }

    protected function getVendorOrders($orderstatus, $vendorId)
    {
        $searchResults = $this->searchResultsFactory->create();
        $searchCriteria = $this->searchCriteriaBuilder->create();
        $searchResults->setSearchCriteria($searchCriteria);

        switch ($orderstatus) {
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
        }

        $collection = $this->subOrdersListing->getSubOrdersCollection($vendorId);
        $collection->addFieldToFilter(
            'main_table.status',
            ['in' => $includeStatuses]
        );

        if ($orderstatus == 'new') {
            $collection->addFieldToFilter('main_table.is_confirmed', ['eq' => 1]);
        }
        $this->collectionProcessor->process($searchCriteria, $collection);
        return $total = $collection->getSize();

    }
}
