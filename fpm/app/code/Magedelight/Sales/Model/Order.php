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

use Magedelight\Sales\Api\Data\VendorOrderInterface;
use Magedelight\Sales\Model\Config\Source\Order\CancelledBy;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderItemRepositoryInterface;
use Magento\Sales\Model\ResourceModel\Order\Invoice\Collection as InvoiceCollection;
use Magento\Sales\Model\ResourceModel\Order\Payment\CollectionFactory as PaymentCollectionFactory;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\Model\AbstractExtensibleModel;
use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Api\ExtensionAttributesFactory;

/**
 * @author Rocket Bazaar Core Team
 * Created at 18 May, 2016 6:28:10 PM
 */
class Order extends AbstractExtensibleModel implements VendorOrderInterface
{
    const STATUS_PENDING = 'pending';
    const STATUS_PROCESSING = 'processing';
    const STATUS_COMPLETE = 'complete';
    const STATUS_CANCELED = 'canceled';
    const STATUS_CLOSED = 'closed';
    const STATUS_PACKED = 'packed';
    const STATUS_CONFIRMED = 'confirmed';
    const STATUS_SHIPPED = 'shipped';
    const STATUS_HANDOVER = 'handover';
    const STATUS_IN_TRANSIT = 'in_transit';
    const STATUS_OUT_WAREHOUSE = 'out_warehouse';
    const STATUS_DELIVERED = 'delivered';
    const CONFIG_PATH_ORDER_AUTO_CONFIRMED = 'vendor_sales/order/auto_confirm';
    const CONFIG_PATH_VENDOR_ORDER_AUTO_CONFIRMED = 'vendor_sales/order/vendor_auto_confirm';
    const IS_MANUAL_SHIPMENT_ALLOWED = 'vendor_sales/order/is_manual_shipment';
    const IS_MAGENTO_ORDER_STATUS_ALLOWED = 'vendor_sales/order/display_main_order_status';
    const LIABLE_ENTITY_FOR_DISCOUNT_SELLER = 'seller';
    const LIABLE_ENTITY_FOR_DISCOUNT_ADMIN = 'admin';

    /**
     * @var \Magento\Sales\Model\Order\Config
     */
    protected $_orderConfig;

    /**
     * @var ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var \Magedelight\Catalog\Helper\Data
     */
    protected $catalogHelper;

    /**
     * @var \Magento\Sales\Model\OrderRepository
     */
    protected $_orderRepository;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Item\CollectionFactory
     */
    protected $_orderItemCollectionFactory;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $_productRepository;

    /**
     * @var PriceCurrencyInterface
     */
    protected $_priceCurrency;

    /**
     * @var \Magedelight\Catalog\Model\ResourceModel\Product
     */
    protected $vendorProductResource;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     *
     * @var \Magento\Sales\Model\Order
     */
    protected $mainOrder;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /*
     * @var \Magento\Sales\Model\ResourceModel\Order\Invoice\CollectionFactory
     */
    protected $_invoice;

    /**
     *
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var OrderItemRepositoryInterface
     */
    protected $itemRepository;

    /**
     * @var PaymentCollectionFactory
     */
    protected $paymentCollectionFactory;

    /**
     * @var \Magedelight\Sales\Plugin\Order\Config
     */
    protected $statusConfig;

    /**
     * @var CancelledBy
     */
    protected $cancelledBy;

    /**
     * @var \Magedelight\Sales\Helper\Data
     */
    protected $salesHelper;

    /**
     * Order constructor.
     * @param \Magento\Framework\Model\Context $context
     * @param \Magedelight\Catalog\Helper\Data $catalogHelper
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Sales\Model\OrderRepository $orderRepository
     * @param ScopeConfigInterface $scopeConfig
     * @param \Magento\Sales\Model\ResourceModel\Order\Item\CollectionFactory $orderItemCollectionFactory
     * @param PriceCurrencyInterface $priceCurrency
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Magento\Sales\Model\Order\Config $orderConfig
     * @param \Magedelight\Catalog\Model\ResourceModel\Product $vendorProductResource
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param OrderItemRepositoryInterface $itemRepository
     * @param PaymentCollectionFactory $paymentCollectionFactory
     * @param \Magedelight\Sales\Plugin\Order\Config $statusConfig
     * @param CancelledBy $cancelledBy
     * @param \Magedelight\Sales\Helper\Data $salesHelper
     * @param \Magento\Sales\Model\ResourceModel\Order\Invoice\CollectionFactory $invoice
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magedelight\Catalog\Helper\Data $catalogHelper,
        \Magento\Framework\Registry $registry,
        \Magento\Sales\Model\OrderRepository $orderRepository,
        ScopeConfigInterface $scopeConfig,
        \Magento\Sales\Model\ResourceModel\Order\Item\CollectionFactory $orderItemCollectionFactory,
        PriceCurrencyInterface $priceCurrency,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Sales\Model\Order\Config $orderConfig,
        \Magedelight\Catalog\Model\ResourceModel\Product $vendorProductResource,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        OrderItemRepositoryInterface $itemRepository,
        PaymentCollectionFactory $paymentCollectionFactory,
        \Magedelight\Sales\Plugin\Order\Config $statusConfig,
        CancelledBy $cancelledBy,
        \Magedelight\Sales\Helper\Data $salesHelper,
        \Magento\Sales\Model\ResourceModel\Order\Invoice\CollectionFactory $invoice,
        ExtensionAttributesFactory $extensionFactory,
        AttributeValueFactory $customAttributeFactory,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->_scopeConfig = $scopeConfig;
        $this->_orderRepository = $orderRepository;
        $this->_orderItemCollectionFactory = $orderItemCollectionFactory;
        $this->_priceCurrency = $priceCurrency;
        $this->_productRepository = $productRepository;
        $this->_orderConfig = $orderConfig;
        $this->vendorProductResource = $vendorProductResource;
        $this->logger = $context->getLogger();
        $this->_invoice = $invoice;
        $this->storeManager = $storeManager;
        $this->catalogHelper = $catalogHelper;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->itemRepository = $itemRepository;
        $this->paymentCollectionFactory = $paymentCollectionFactory;
        $this->statusConfig = $statusConfig;
        $this->cancelledBy = $cancelledBy;
        $this->salesHelper = $salesHelper;
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $resource,
            $resourceCollection,
            $data
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getVendorOrderId()
    {
        return $this->getData(VendorOrderInterface::VENDOR_ORDER_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setVendorOrderId($vendorOrderId)
    {
        return $this->setData(VendorOrderInterface::VENDOR_ORDER_ID, $vendorOrderId);
    }

    /**
     * {@inheritdoc}
     */
    public function getOrderId()
    {
        return $this->getData(VendorOrderInterface::ORDER_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setOrderId($orderId)
    {
        return $this->setData(VendorOrderInterface::ORDER_ID, $orderId);
    }

    /**
     * {@inheritdoc}
     */
    public function getVendorId()
    {
        return $this->getData(VendorOrderInterface::VENDOR_ID);
    }

    /**
     * Return subtotal
     *
     * @return float|null
     */
    public function getSubtotal()
    {
        return $this->getData(VendorOrderInterface::SUBTOTAL);
    }

    /**
     * @inheritdoc
     */
    public function setSubtotal($amount)
    {
        return $this->setData(VendorOrderInterface::SUBTOTAL, $amount);
    }

    /**
     * {@inheritdoc}
     */
    public function setVendorId($vendorId)
    {
        return $this->setData(VendorOrderInterface::VENDOR_ID, $vendorId);
    }

    /**
     * {@inheritdoc}
     */
    public function getGrandTotal()
    {
        return $this->getData(VendorOrderInterface::GRAND_TOTAL);
    }

    /**
     * {@inheritdoc}
     */
    public function setGrandTotal($grandTotal)
    {
        return $this->setData(VendorOrderInterface::GRAND_TOTAL, $grandTotal);
    }

    /**
     * {@inheritdoc}
     */
    public function getIncrementId()
    {
        return $this->getData(VendorOrderInterface::INCREMENT_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setIncrementId($incrementId)
    {
        return $this->setData(VendorOrderInterface::INCREMENT_ID, $incrementId);
    }

    /**
     * @inheritdoc
     */
    public function setShippingAmount($amount)
    {
        return $this->setData(VendorOrderInterface::SHIPPING_AMOUNT, $amount);
    }

    /**
     * {@inheritdoc}
     */
    public function getShippingAmount()
    {
        return $this->getData(VendorOrderInterface::SHIPPING_AMOUNT);
    }

    /**
     * @inheritdoc
     */
    public function setShippingDescription($description)
    {
        return $this->setData(VendorOrderInterface::SHIPPING_DESCRIPTION, $description);
    }

    /**
     * @inheritdoc
     */
    public function getShippingDescription()
    {
        return $this->getData(VendorOrderInterface::SHIPPING_DESCRIPTION);
    }

    /**
     * {@inheritdoc}
     */
    public function getStatus()
    {
        return $this->getData(VendorOrderInterface::STATUS);
    }

    /**
     * {@inheritdoc}
     */
    public function setStatus($status)
    {
        return $this->setData(VendorOrderInterface::STATUS, $status);
    }

    /**
     * {@inheritDoc}
     */
    public function getStatusLabel()
    {
        if (!$this->getData(VendorOrderInterface::STATUS_LABEL)) {
            return $this->getConfig()->getStatusLabel($this->getStatus());
        }
        return $this->getData(VendorOrderInterface::STATUS_LABEL);
    }

    /**
     * {@inheritDoc}
     */
    public function setStatusLabel($statusLabel)
    {
        return $this->setData(VendorOrderInterface::STATUS_LABEL, $statusLabel);
    }

    /**
     * {@inheritdoc}
     */
    public function getTotalRefunded()
    {
        return $this->getData(VendorOrderInterface::TOTAL_REFUNDED);
    }

    /**
     * {@inheritdoc}
     */
    public function setTotalRefunded($totalRefunded)
    {
        return $this->setData(VendorOrderInterface::TOTAL_REFUNDED, $totalRefunded);
    }

    /**
     * {@inheritdoc}
     */
    public function getCreatedAt()
    {
        return $this->getData(VendorOrderInterface::CREATED_AT);
    }

    /**
     * {@inheritdoc}
     */
    public function setCreatedAt($createdAt)
    {
        return $this->setData(VendorOrderInterface::CREATED_AT, $createdAt);
    }

    /**
     * {@inheritdoc}
     */
    public function getFirstName()
    {
        return $this->getData(VendorOrderInterface::CUSTOMER_FIRSTNAME);
    }

    /**
     * {@inheritdoc}
     */
    public function setFirstName($firstName)
    {
        return $this->setData(VendorOrderInterface::CUSTOMER_FIRSTNAME, $firstName);
    }

    /**
     * {@inheritdoc}
     */
    public function getLastName()
    {
        return $this->getData(VendorOrderInterface::CUSTOMER_LASTNAME);
    }

    /**
     * {@inheritdoc}
     */
    public function setLastName($lastName)
    {
        return $this->setData(VendorOrderInterface::CUSTOMER_LASTNAME, $lastName);
    }

    /**
     * {@inheritdoc}
     */
    public function getBillToName()
    {
        return $this->getData(VendorOrderInterface::BILL_TO_NAME);
    }

    /**
     * {@inheritdoc}
     */
    public function setBillToName($billToName)
    {
        return $this->setData(VendorOrderInterface::BILL_TO_NAME, $billToName);
    }

    /**
     * {@inheritdoc}
     */
    public function getShipToName()
    {
        return $this->getData(VendorOrderInterface::SHIP_TO_NAME);
    }

    /**
     * {@inheritdoc}
     */
    public function setShipToName($shipToName)
    {
        return $this->setData(VendorOrderInterface::SHIP_TO_NAME, $shipToName);
    }

    /**
     * {@inheritdoc}
     */
    public function getIsCustomerReviewExists()
    {
        return $this->getData(VendorOrderInterface::IS_CUSTOMER_REVIEW_EXISTS);
    }

    /**
     * {@inheritdoc}
     */
    public function setIsCustomerReviewExists($flag)
    {
        return $this->setData(VendorOrderInterface::IS_CUSTOMER_REVIEW_EXISTS, $flag);
    }

    /**
     * {@inheritdoc}
     */
    public function getIsConfirmed()
    {
        return $this->getData(VendorOrderInterface::IS_CONFIRMED);
    }

    /**
     * {@inheritdoc}
     */
    public function setIsConfirmed($isConfirmed)
    {
        return $this->setData(VendorOrderInterface::IS_CONFIRMED, $isConfirmed);
    }

    /**
     * {@inheritDoc}
     */
    public function getCanShip()
    {
        if (!$this->getData(VendorOrderInterface::CAN_SHIP)) {
            return $this->canShip();
        }
        return $this->getData(VendorOrderInterface::CAN_SHIP);
    }

    /**
     * {@inheritDoc}
     */
    public function getCanGeneratePackingSlip()
    {
        if (!$this->getData(VendorOrderInterface::CAN_GENERATE_PACKING_SLIP)) {
            return $this->isShipmentGenerated();
        }
        return $this->getData(VendorOrderInterface::CAN_GENERATE_PACKING_SLIP);
    }

    /**
     * {@inheritDoc}
     */
    public function getCanPrintInvoice()
    {
        if (!$this->getData(VendorOrderInterface::CAN_PRINT_INVOICE)) {
            return $this->checkInvoiceIsAvailable($this->getOrderId(), $this->getVendorId());
        }
        return $this->getData(VendorOrderInterface::CAN_PRINT_INVOICE);
    }

    /**
     * {@inheritDoc}
     */
    public function getCanManifest()
    {
        if (!$this->getData(VendorOrderInterface::CAN_MANIFEST)) {
            return $this->isShipmentGenerated();
        }
        return $this->getData(VendorOrderInterface::CAN_MANIFEST);
    }

    /**
     * {@inheritDoc}
     */
    public function getCanInvoice()
    {
        if (!$this->getData(VendorOrderInterface::CAN_INVOICE)) {
            return $this->canInvoice();
        }
        return $this->getData(VendorOrderInterface::CAN_INVOICE);
    }

    /**
     * {@inheritDoc}
     */
    public function getCanConfirm()
    {
        if (!$this->getData(VendorOrderInterface::CAN_CONFIRM)) {
            return ($this->getIsConfirmed() == false);
        }
        return $this->getData(VendorOrderInterface::CAN_CONFIRM);
    }

    /**
     * {@inheritDoc}
     */
    public function getCanCancel()
    {
        if (!$this->getData(VendorOrderInterface::CAN_CANCEL)) {
            return $this->canCancel();
        }
        return $this->getData(VendorOrderInterface::CAN_CANCEL);
    }

    /**
     * {@inheritDoc}
     */
    public function getMoveToIntransit()
    {
        if (!$this->getData(VendorOrderInterface::MOVE_TO_INTRANSIT)) {
            return ($this->isManualShipmentAllowed() && !$this->canShip() &&
                $this->isShipmentGenerated() && $this->getStatus() == self::STATUS_SHIPPED);
        }
        return $this->getData(VendorOrderInterface::MOVE_TO_INTRANSIT);
    }

    /**
     * {@inheritDoc}
     */
    public function getMoveToDelivered()
    {
        if (!$this->getData(VendorOrderInterface::MOVE_TO_DELIVERED)) {
            return ($this->isManualShipmentAllowed() && !$this->canShip() && !$this->canInvoice()
                && $this->getStatus() == self::STATUS_IN_TRANSIT);
        }
        return $this->getData(VendorOrderInterface::MOVE_TO_DELIVERED);
    }

    /**
     * @inheritdoc
     */
    public function setItems($items)
    {
        return $this->setData(OrderInterface::ITEMS, $items);
    }

    /**
     * @inheritdoc
     */
    public function getItems()
    {
        if ($this->getData(OrderInterface::ITEMS) == null) {
            $this->searchCriteriaBuilder->addFilter('vendor_order_id', $this->getVendorOrderId());

            $searchCriteria = $this->searchCriteriaBuilder->create();
            $this->setData(
                OrderInterface::ITEMS,
                $this->itemRepository->getList($searchCriteria)->getItems()
            );
        }
        return $this->getData(OrderInterface::ITEMS);
    }

    /**
     * @inheritdoc
     */
    public function setPayment(\Magento\Sales\Api\Data\OrderPaymentInterface $payment = null)
    {
        $this->setData(OrderInterface::PAYMENT, $payment);
        if ($payment !== null) {
            $payment->setOrder($this)->setParentId($this->getOrderId());
            if (!$payment->getId()) {
                $this->setDataChanges(true);
            }
        }
        return $payment;
    }

    /**
     * @inheritdoc
     */
    public function getPayment()
    {
        $payment = $this->getData(OrderInterface::PAYMENT);
        if ($payment === null) {
            $payment = $this->paymentCollectionFactory->create()
                ->addFieldToFilter('parent_id', $this->getOrderId())->getFirstItem();
            if ($payment && $payment->getId()) {
                $this->setData(
                    OrderInterface::PAYMENT,
                    $payment
                );
            }
        }
        return $payment;
    }

    /**
     * Initialize order resource model
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init(\Magedelight\Sales\Model\ResourceModel\Order::class);
    }

    /**
     * describes that auto confirm is enabled for order by admin
     * @param int $websiteId
     * @return type
     */
    public function isAutoConfirmEnabledForVendor($websiteId = 1)
    {
        return $this->_scopeConfig->getValue(
            self::CONFIG_PATH_VENDOR_ORDER_AUTO_CONFIRMED,
            ScopeInterface::SCOPE_WEBSITE,
            $websiteId
        );
    }

    /**
     * @param $orderId
     * @param $vendorId
     * @param $vendorOrderId
     * @return $this
     *
     */
    // public function getByOriginOrderId($orderId, $vendorId)
    // {
    //     $vendorOrder = $this->getResource()->getByOriginOrderId($orderId, $vendorId);
    //     if (!empty($vendorOrder)) {
    //         $this->setData($vendorOrder);
    //     }
    //     return $this;
    // }
    
    public function getByOriginOrderId($orderId, $vendorId, $vendorOrderId = null)
    {
        $vendorOrder = $this->getResource()->getByOriginOrderId($orderId, $vendorId, $vendorOrderId);
        if (!empty($vendorOrder)) {
        $this->setData($vendorOrder);
        }
        return $this;
    }

    /**
     * @return bool
     */
    public function canInvoice()
    {
        if (!$this->getData('is_confirmed')) {
            return false;
        }

        if ($this->getStatus() === self::STATUS_CANCELED ||
            $this->getStatus() === self::STATUS_CLOSED ||
            $this->getStatus() === self::STATUS_COMPLETE) {
            return false;
        }

        foreach ($this->getItems() as $item) {
            if ($item->getQtyToInvoice() > 0 && !$item->getLockedDoInvoice()) {
                return true;
            }
        }

        return false;
    }

    /**
     *
     * @return boolean
     */
    public function canConfirm($item = '')
    {
        if ($item) {
            if ($item->getParentItem()) {
                $itemStatus = $item->getParentItem()->getStatusId();
            } else {
                $itemStatus = $item->getStatusId();
            }
            if ((strtolower($itemStatus) == \Magento\Sales\Model\Order\Item::STATUS_CANCELED)
                || $this->getOriginalOrder()->getState() == self::STATUS_CANCELED
                || $this->getOriginalOrder()->getIsConfirmed() == false
            ) {
                return false;
            }
        }
        return ($this->getData('is_confirmed') == false);
    }

    /**
     *
     * @return boolean
     */
    public function canCancel()
    {
        $allInvoiced = true;
        foreach ($this->getItems() as $item) {
            if ($item->getQtyToInvoice()) {
                $allInvoiced = false;
                break;
            }
        }

        if ($allInvoiced) {
            return false;
        }

        if ($this->getStatus() === self::STATUS_CANCELED ||
            $this->getStatus() === self::STATUS_CLOSED ||
            $this->getStatus() === self::STATUS_SHIPPED ||
            $this->getStatus() === self::STATUS_COMPLETE) {
            return false;
        }

        return true;
    }

    public function isCanceled()
    {
        return ($this->getStatus() === self::STATUS_CANCELED);
    }

    public function canShip()
    {
        if (!$this->getData('is_confirmed')) {
            return false;
        }

        if ($this->getStatus() === self::STATUS_CANCELED ||
            $this->getStatus() === self::STATUS_CLOSED ||
            $this->getStatus() === self::STATUS_PROCESSING ||
            $this->getStatus() === self::STATUS_COMPLETE) {
            return false;
        }

        foreach ($this->getItems() as $item) {
            if ($item->getQtyToShip() > 0 && !$item->getIsVirtual() && !$item->getLockedDoShip()) {
                return true;
            }
        }

        return false;
    }

    public function isShipmentGenerated()
    {
        foreach ($this->getItems() as $item) {
            if ($item->getQtyShipped() > 0) {
                return true;
            }
        }
        return false;
    }

    public function canCreditmemo()
    {
        if ($this->getStatus() === self::STATUS_CANCELED || $this->getStatus() === self::STATUS_CLOSED) {
            return false;
        }
        if (abs($this->_priceCurrency->round($this->getTotalPaid()) - $this->getTotalRefunded()) < .0001) {
            return false;
        }

        return true;
    }

    public function canReorder()
    {
        $order = $this->getOriginalOrder();

        if (!$order->getId() || !$order->getCustomerId() || $order->isPaymentReview()) {
            return false;
        }

        $products = [];
        foreach ($this->getItems() as $item) {
            $products[] = $item->getProductId();
        }

        if (!empty($products)) {
            foreach ($products as $productId) {
                $product = $this->_productRepository->getById($productId, false, $order->getStoreId());
                if (!$product->getId() || !$product->isSalable()) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * @return \Magento\Sales\Api\Data\OrderInterface
     */
    public function getOriginalOrder()
    {
        if (!$this->mainOrder) {
            $this->mainOrder = $this->_orderRepository->get($this->getData('order_id'));
        }
        return $this->mainOrder;
    }

    /**
     * Retrieve order invoices collection
     *
     * @return InvoiceCollection
     */
    public function getInvoiceCollection()
    {
        if ($this->_invoices === null) {
            $this->_invoices = $this->_invoiceCollectionFactory->create()->setOrderFilter($this);

            if ($this->getId()) {
                foreach ($this->_invoices as $invoice) {
                    $invoice->setOrder($this);
                }
            }
        }
        return $this->_invoices;
    }

    /**
     * Set order invoices collection
     *
     * @param InvoiceCollection $invoices
     * @return $this
     */
    public function setInvoiceCollection(InvoiceCollection $invoices)
    {
        $this->_invoices = $invoices;
        return $this;
    }

    /**
     * Retrieve order shipments collection
     *
     * @return ShipmentCollection|false
     */
    public function getShipmentsCollection()
    {
        if (empty($this->_shipments)) {
            if ($this->getId()) {
                $this->_shipments = $this->_shipmentCollectionFactory->create()->setOrderFilter($this)->load();
            } else {
                return false;
            }
        }
        return $this->_shipments;
    }

    /**
     * Retrieve order creditmemos collection
     *
     * @return CreditmemoCollection|false
     */
    public function getCreditmemosCollection()
    {
        if (empty($this->_creditmemos)) {
            if ($this->getId()) {
                $this->_creditmemos = $this->_memoCollectionFactory->create()->setOrderFilter($this)->load();
            } else {
                return false;
            }
        }
        return $this->_creditmemos;
    }

    /**
     * Retrieve order configuration model
     *
     * @return \Magento\Sales\Model\Order\Config
     */
    public function getConfig()
    {
        return $this->_orderConfig;
    }

    /**
     * @param $invoice
     */
    public function registerInvoice($invoice, $liableEntityForDiscount)
    {
//         $this->setHiddenTaxInvoiced($this->getHiddenTaxInvoiced() + $invoice->getHiddenTaxAmount());
//         $this->setBaseHiddenTaxInvoiced($this->getBaseHiddenTaxInvoiced() + $invoice->getBaseHiddenTaxAmount());

        $this->setShippingTaxInvoiced($this->getShippingTaxInvoiced() + $invoice->getShippingTaxAmount());
        $this->setBaseShippingTaxInvoiced($this->getBaseShippingTaxInvoiced() + $invoice->getBaseShippingTaxAmount());

        $this->setShippingInvoiced($this->getShippingInvoiced() + $invoice->getShippingAmount());
        $this->setBaseShippingInvoiced($this->getBaseShippingInvoiced() + $invoice->getBaseShippingAmount());
        /* Save this data only if liable entity for discount is seller. */
        $this->setTotalInvoiced($this->getTotalInvoiced() + $invoice->getGrandTotal());
        $this->setBaseTotalInvoiced($this->getBaseTotalInvoiced() + $invoice->getBaseGrandTotal());
        $this->setSubtotalInvoiced($this->getSubtotalInvoiced() + $invoice->getSubtotal());
        $this->setBaseSubtotalInvoiced($this->getBaseSubtotalInvoiced() + $invoice->getBaseSubtotal());
        $this->setTaxInvoiced($this->getTaxInvoiced() + $invoice->getTaxAmount());
        $this->setBaseTaxInvoiced($this->getBaseTaxInvoiced() + $invoice->getBaseTaxAmount());
        if ($liableEntityForDiscount == self::LIABLE_ENTITY_FOR_DISCOUNT_SELLER) {
            $this->setDiscountInvoiced($this->getDiscountInvoiced() + $invoice->getDiscountAmount());
            $this->setBaseDiscountInvoiced($this->getBaseDiscountInvoiced() + $invoice->getBaseDiscountAmount());
//         $this->setBaseTotalInvoicedCost($this->getBaseTotalInvoicedCost() + $invoice->getBaseCost());
        }
        $this->setTotalPaid(
            $this->getTotalPaid() + $invoice->getGrandTotal()
        );
        $this->setBaseTotalPaid(
            $this->getBaseTotalPaid() + $invoice->getBaseGrandTotal()
        );

//        if($this->getStatus() == self::STATUS_PENDING) {
//            $this->setStatus(self::STATUS_PROCESSING);
//        }
        //$this->_registerCommission($invoice, 'commission_amount');
        $invoice->setVendorId($this->getVendorId());
    }

    /**
     *
     * @param \Magento\Sales\Model\Order $order
     * @param type $fromCron
     * @return \Magedelight\Sales\Model\Order
     * @throws \Exception
     */
    public function registerCancel(\Magento\Sales\Model\Order $order, $fromCron = false)
    {
        if ($this->canCancel() || $fromCron) {
            $vendorOrderId = $this->getData('vendor_order_id');
            $vendorId = $this->getData('vendor_id');
            foreach ($order->getAllVisibleItems() as $item) {
                if ($item->getData('vendor_order_id') == $vendorOrderId) {
                    if (!$fromCron) {
                        $item->cancel();
                    }
                    /* Increase vendor product qty when order canceled */
                    if ($item->getId() && $item->getProduct()) {
                        $connection = $this->vendorProductResource->getConnection();
                        $productTable = $this->vendorProductResource->getMainTable();
                        if ($item->getProductType() === "configurable") {
                            foreach ($item->getChildrenItems() as $childItem) {
                                $connection->query("update {$productTable} SET qty = qty + {$childItem->getQtyOrdered()} where vendor_id = {$vendorId} AND marketplace_product_id = {$childItem->getProduct()->getId()}");
                            }
                        } else {
                            $connection->query("update {$productTable} SET qty = qty + {$item->getQtyOrdered()} where vendor_id = {$vendorId} AND marketplace_product_id = {$item->getProduct()->getId()}");
                        }
                    }
                }
            }

            $this->setSubtotalCanceled($this->getSubtotal() - $this->getSubtotalInvoiced());
            $this->setBaseSubtotalCanceled($this->getBaseSubtotal() - $this->getBaseSubtotalInvoiced());

            $this->setTaxCanceled($this->getTaxAmount() - $this->getTaxInvoiced());
            $this->setBaseTaxCanceled($this->getBaseTaxAmount() - $this->getBaseTaxInvoiced());

            $this->setShippingCanceled($this->getShippingAmount() - $this->getShippingInvoiced());
            $this->setBaseShippingCanceled($this->getBaseShippingAmount() - $this->getBaseShippingInvoiced());

            $this->setDiscountCanceled(abs($this->getVendorDiscountAmount()) - $this->getDiscountInvoiced());
            $this->setBaseDiscountCanceled(abs($this->getBaseDiscountAmount()) - $this->getBaseDiscountInvoiced());

            $this->setTotalCanceled($this->getGrandTotal() - $this->getTotalPaid());
            $this->setBaseTotalCanceled($this->getBaseGrandTotal() - $this->getBaseTotalPaid());
            if (!$fromCron) {
                //Update original order:
                $order->setSubtotalCanceled($order->getSubtotalCanceled() + $this->getSubtotalCanceled());
                $order->setBaseSubtotalCanceled($order->getBaseSubtotalCanceled() + $this->getBaseSubtotalCanceled());

                $order->setTaxCanceled($order->getTaxCanceled() + $this->getTaxCanceled());
                $order->setBaseTaxCanceled($order->getBaseTaxCanceled() + $this->getBaseTaxCanceled());

                $order->setShippingCanceled($order->getShippingCanceled() + $this->getShippingCanceled());
                $order->setBaseShippingCanceled($order->getBaseShippingCanceled() + $this->getBaseShippingCanceled());

                $order->setDiscountCanceled($order->getDiscountCanceled() + $this->getDiscountCanceled());
                $order->setBaseDiscountCanceled($order->getBaseDiscountCanceled() + $this->getBaseDiscountCanceled());

                $order->setTotalCanceled($order->getTotalCanceled() + $this->getTotalCanceled());
                $order->setBaseTotalCanceled($order->getBaseTotalCanceled() + $this->getBaseTotalCanceled());
            }
            $this->setStatus(self::STATUS_CANCELED);
        /**
         * @todo status history management
         * $this->_setStatusHistory(self::STATUS_CANCELED);
         */
        } else {
            throw new \Exception(__('Order does not allow to be canceled.'));
        }

        return $this;
    }

    /**
     * @return bool
     */
    public function isCurrencyDifferent()
    {
        $mainOrder = $this->getOriginalOrder();
        return $mainOrder->getOrderCurrencyCode() != $mainOrder->getBaseCurrencyCode();
    }

    /**
     * Format price precision
     *
     * @param float $price
     * @param int $precision
     * @param bool $addBrackets
     * @return string
     */
    public function formatPricePrecision($price, $precision, $addBrackets = false)
    {
        return $this->getOriginalOrder()->formatPricePrecision($price, $precision, $addBrackets);
    }

    /**
     * Format Base Price Precision
     *
     * @param float $price
     * @param int $precision
     * @return string
     */
    public function formatBasePricePrecision($price, $precision)
    {
        return $this->getOriginalOrder()->formatBasePricePrecision($price, $precision);
    }

    /**
     * Retrieve store model instance
     *
     * @return \Magento\Store\Model\Store
     */
    public function getStore()
    {
        $mainOrder = $this->getOriginalOrder();
        $storeId = $mainOrder->getStoreId();
        if ($storeId) {
            return $this->storeManager->getStore($storeId);
        }
        return $this->storeManager->getStore();
    }

    /**
     * Returns forced_shipment_with_invoice
     *
     * @return int
     */
    public function getForcedShipmentWithInvoice()
    {
        return $this->getOriginalOrder()->getForcedShipmentWithInvoice();
    }

    public function isMutipleVendorOrder($orderId)
    {
        $vendorOrder = $this->getResource()->getOrderVendorCount($orderId);
        if (intval($vendorOrder['vendor_count']) > 1) {
            return true;
        }
        return false;
    }

    public function checkInvoiceIsAvailable($orderId, $vendorId)
    {
        $collection = $this->_invoice->create();
        $collection->addFieldToFilter('vendor_order_id', ['eq' => $this->getVendorOrderId()]);

        $collection->addFieldToFilter('vendor_id', ['eq' => $vendorId]);
        if ($collection->count() > 0) {
            return true;
        }
        return false;
    }

    /*
     * Check if order is eligible for split invoice.
     * If there are membership product or store credit product in order, it will return false.
     */
    public function canSplitInvoice($order)
    {
        $storeCreditProduct = $this->catalogHelper->getStoreCreditProduct();
        if ($order) {
            foreach ($order->getAllItems() as $_item) {
                if ($storeCreditProduct && $_item->getProduct()->getSku() == $storeCreditProduct) {
                    return false;
                }
            }
            return true;
        }
        return false;
    }

    /**
     * Format value based on order currency
     *
     * @param   string $value
     * @return  string
     */
    public function formatValue($value)
    {
        $order = $this->getOriginalOrder();
        return ($order) ? $order->formatPrice($value) : $value;
    }

    /**
     * @return string|null
     */
    public function getCancelledByEntity()
    {
        return ($this->getStatus() == self::STATUS_CANCELED) ?
            $this->cancelledBy->getOptionText($this->getCancelledBy()) : null;
    }

    /**
     * @return bool
     */
    public function canGenerateInvoice($order = null)
    {
        if ($order) {
            $orderPaymentMethod = $order->getPayment()->getMethod();
            $allowedToGenerateInvoice = $this->salesHelper->canGenerateInvoice();
            $allowedToGenerateInvoiceMethods = explode(',', $this->salesHelper->getAllowedPaymentMethodsForInvoice());
            return ($allowedToGenerateInvoice &&
                in_array($orderPaymentMethod, $allowedToGenerateInvoiceMethods) &&
                $this->salesHelper->canGenerateInvoice());
        }
        return false;
    }

    /**
     * @return bool
     */
    public function isManualShipmentAllowed()
    {
        return $this->_scopeConfig->getValue(self::IS_MANUAL_SHIPMENT_ALLOWED, ScopeInterface::SCOPE_WEBSITE);
    }

    /**
     * @inheritdoc
     *
     * @return \Magento\Framework\Api\ExtensionAttributesInterface
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * @inheritdoc
     *
     * @param \Magedelight\Sales\Api\Data\VendorOrderExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Magedelight\Sales\Api\Data\VendorOrderExtensionInterface $extensionAttributes
    ) {
        return $this->_setExtensionAttributes($extensionAttributes);
    }
}
