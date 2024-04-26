<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Commissions
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Commissions\Model\Commission;

use Magedelight\Commissions\Api\Data\CommissionPaymentInterface;
use Magedelight\Sales\Model\Order as VendorOrder;
use Magedelight\Sales\Model\Order\SplitOrder\DiscountProcessor;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Store\Model\ScopeInterface;

class Payment extends AbstractModel implements CommissionPaymentInterface
{
    const VENDOR_ORDER_TABLE = "md_vendor_order";
    const VENDOR_PAYMENTS_TABLE = "md_vendor_commission_payment";
    const VENDOR_COMMISSION_TABLE = "md_vendor_commissions";
    const CATEGORY_COMMISSION_TABLE = "md_commissions";
    const ACTOR_ADMIN = 1;
    const ACTOR_VENDOR = 2;
    const COMMISSION_LEVEL_PRODUCT = 1;
    const COMMISSION_LEVEL_CATEGORY = 2;
    const COMMISSION_LEVEL_VENDOR = 3;
    const COMMISSION_LEVEL_GLOBAL = 0;
    const CALCULATION_TYPE_FLAT = 1;
    const CALCULATION_TYPE_PERCENTAGE = 2;
    const CONFIG_PATH_COMM_CALC_TYPE = "commission/general/commission_calc_type";
    const CONFIG_PATH_COMM_RATE = "commission/general/commission_value";
    const CONFIG_PATH_COMM_MARKETPLACE_FEE_CALC_TYPE = "commission/general/marketplace_fee_calc_type";
    const CONFIG_PATH_COMM_MARKETPLACE_FEE = "commission/general/marketplace_fee";
    const CONFIG_PATH_COMM_CANCELLATION_FEE_CALC_TYPE = "commission/general/cancellation_fee_calc_type";
    const CONFIG_PATH_COMM_CANCELLATION_FEE = "commission/general/cancellation_fee";
    const CONFIG_PATH_SERVICE_TAX_RATE = "commission/general/service_tax";
    const CONFIG_PATH_PO_SHIPPING_LIABILITY = "commission/payout/shipping_liability";
    const CONFIG_PATH_PO_ADJUSTMENT_LIABILITY = "commission/payout/adjustment_liability";
    const CONFIG_PATH_PO_TAX_LIABILITY = "commission/payout/tax_liability";
    const CONFIG_PATH_PO_COMM_LEVEL_PRECEDENCE = "commission/payout_commission";
    const CONFIG_PATH_PO_DURATION = "commission/payout/duration";
    const CONFIG_PATH_ADJUST_DISCOUNT = "commission/payout/adjust_discount";
    const PO_DEFAULT_DURATION = 15;
    const PO_ID_PREFIX = 'PO';
    const INVOICE_ID_PREFIX = 'Invoice';
    const FEE_TYPE_COMMISSION = 'total_commission'; /* initially "commission" */
    const FEE_TYPE_MARKETPLACE_FEE = 'marketplace_fee';
    const FEE_TYPE_CANCELLATION_FEE = 'cancellation_fee';
    const CREDIT_TRANSACTION_TYPE = "credit_note";
    const DEBIT_TRANSACTION_TYPE = "debit_note";

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $dateTime;

    /**
     *
     * @var array
     */
    public $itemData;

    /**
     * @var SerializerInterface
     */

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    protected $_connection;

    /**
     *
     * @var array
     */
    protected $categoryCommissionRates = [];

    protected $categoryMarketPlaceRates = [];

    protected $categoryCancellationRates = [];

    /**
     *
     * @var array
     */
    protected $vendorCommissionRates = [];
    protected $vendorMarketPlaceRates = [];
    protected $vendorCancellationRates = [];

    /**
     * @var array
     */
    protected $precedences = [];

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magedelight\Sales\Model\ResourceModel\Order\Collection
     */
    protected $vendorOrderCollection;

    /**
     * @var \Magedelight\Catalog\Helper\Data
     */
    protected $catalogHelper;

    /**
     * @var \Magedelight\Vendor\Api\VendorRepositoryInterface
     */
    private $vendorRepository;

    /**
     *
     * @var int
     */
    protected $websiteId;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;
    /**
     * @var DiscountProcessor
     */
    protected $discountProcessor;

    /**
     *
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magedelight\Vendor\Api\VendorRepositoryInterface $vendorRepository,
        \Magedelight\Sales\Model\ResourceModel\Order\CollectionFactory $vendorOrderCollectionFactory,
        \Magedelight\Catalog\Helper\Data $catalogHelper,
        \Psr\Log\LoggerInterface $logger,
        DiscountProcessor $discountProcessor,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        Json $serializer = null,
        array $data = []
    ) {
        $this->dateTime = $date;
        $this->scopeConfig = $scopeConfig;
        $this->vendorOrderCollection = $vendorOrderCollectionFactory->create();
        $this->vendorRepository = $vendorRepository;
        $this->serializer = $serializer ?: ObjectManager::getInstance()->get(Json::class);
        $this->logger = $logger;
        $this->catalogHelper = $catalogHelper;
        $this->discountProcessor = $discountProcessor;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Magedelight\Commissions\Model\ResourceModel\Commission\Payment::class);
    }

    /**
     *
     * {@inheritDoc}
     * @see \Magedelight\Commissions\Api\Data\CommissionPaymentInterface::getAdjustmentAmount()
     */
    public function getAdjustmentAmount()
    {
        return $this->getData(self::ADJUSTMENT_AMOUNT);
    }

    /**
     *
     * {@inheritDoc}
     * @see \Magedelight\Commissions\Api\Data\CommissionPaymentInterface::getCancellationFee()
     */
    public function getCancellationFee()
    {
        return $this->getData(self::CANCELLATION_FEE);
    }

    /**
     *
     * {@inheritDoc}
     * @see \Magedelight\Commissions\Api\Data\CommissionPaymentInterface::getComment()
     */
    public function getComment()
    {
        return $this->getData(self::COMMENT);
    }

    /**
     *
     * {@inheritDoc}
     * @see \Magedelight\Commissions\Api\Data\CommissionPaymentInterface::getCommissionLevel()
     */
    public function getCommissionLevel()
    {
        return $this->getData(self::COMMISSION_LEVEL);
    }

    /**
     *
     * {@inheritDoc}
     * @see \Magedelight\Commissions\Api\Data\CommissionPaymentInterface::getCreatedAt()
     */
    public function getCreatedAt()
    {
        return $this->getData(self::CREATED_AT);
    }

    /**
     *
     * {@inheritDoc}
     * @see \Magedelight\Commissions\Api\Data\CommissionPaymentInterface::getMarketplaceFee()
     */
    public function getMarketplaceFee()
    {
        return $this->getData(self::MARKETPLACE_FEE);
    }

    /**
     *
     * {@inheritDoc}
     * @see \Magedelight\Commissions\Api\Data\CommissionPaymentInterface::getPaidAt()
     */
    public function getPaidAt()
    {
        return $this->getData(self::PAID_AT);
    }

    /**
     *
     * {@inheritDoc}
     * @see \Magedelight\Commissions\Api\Data\CommissionPaymentInterface::getPurchaseOrderId()
     */
    public function getPurchaseOrderId()
    {
        return $this->getData(self::PURCHASE_ORDER_ID);
    }

    /**
     *
     * {@inheritDoc}
     * @see \Magedelight\Commissions\Api\Data\CommissionPaymentInterface::getServiceTax()
     */
    public function getServiceTax()
    {
        return $this->getData(self::SERVICE_TAX);
    }

    /**
     *
     * {@inheritDoc}
     * @see \Magedelight\Commissions\Api\Data\CommissionPaymentInterface::getShippingAmount()
     */
    public function getShippingAmount()
    {
        return $this->getData(self::SHIPPING_AMOUNT);
    }

    /**
     *
     * {@inheritDoc}
     * @see \Magedelight\Commissions\Api\Data\CommissionPaymentInterface::getStatus()
     */
    public function getStatus()
    {
        return $this->getData(self::STATUS);
    }

    /**
     *
     * {@inheritDoc}
     * @see \Magedelight\Commissions\Api\Data\CommissionPaymentInterface::getTaxAmount()
     */
    public function getTaxAmount()
    {
        return $this->getData(self::TAX_AMOUNT);
    }

    /**
     *
     * {@inheritDoc}
     * @see \Magedelight\Commissions\Api\Data\CommissionPaymentInterface::getTotalAmount()
     */
    public function getTotalAmount()
    {
        return $this->getData(self::TOTAL_AMOUNT);
    }

    /**
     *
     * {@inheritDoc}
     * @see \Magedelight\Commissions\Api\Data\CommissionPaymentInterface::getTotalCommission()
     */
    public function getTotalCommission()
    {
        return $this->getData(self::TOTAL_COMMISSION);
    }

    /**
     *
     * {@inheritDoc}
     * @see \Magedelight\Commissions\Api\Data\CommissionPaymentInterface::getTotalDue()
     */
    public function getTotalDue()
    {
        return $this->getData(self::TOTAL_DUE);
    }

    /**
     *
     * {@inheritDoc}
     * @see \Magedelight\Commissions\Api\Data\CommissionPaymentInterface::getTotalPaid()
     */
    public function getTotalPaid()
    {
        return $this->getData(self::TOTAL_PAID);
    }

    /**
     *
     * {@inheritDoc}
     * @see \Magedelight\Commissions\Api\Data\CommissionPaymentInterface::getTransactionFee()
     */
    public function getTransactionFee()
    {
        return $this->getData(self::TRANSACTION_FEE);
    }

    /**
     *
     * {@inheritDoc}
     * @see \Magedelight\Commissions\Api\Data\CommissionPaymentInterface::getUpdatedAt()
     */
    public function getUpdatedAt()
    {
        return $this->getData(self::UPDATED_AT);
    }

    /**
     *
     * {@inheritDoc}
     * @see \Magedelight\Commissions\Api\Data\CommissionPaymentInterface::getVendorId()
     */
    public function getVendorId()
    {
        return $this->getData(self::VENDOR_ID);
    }

    /**
     *
     * {@inheritDoc}
     * @see \Magedelight\Commissions\Api\Data\CommissionPaymentInterface::setAdjustmentAmount()
     */
    public function setAdjustmentAmount($adjustmentAmount)
    {
        return $this->setData(self::ADJUSTMENT_AMOUNT, $adjustmentAmount);
    }

    /**
     *
     * {@inheritDoc}
     * @see \Magedelight\Commissions\Api\Data\CommissionPaymentInterface::setCancellationFee()
     */
    public function setCancellationFee($cancellationFee)
    {
        return $this->setData(self::CANCELLATION_FEE, $cancellationFee);
    }

    /**
     *
     * {@inheritDoc}
     * @see \Magedelight\Commissions\Api\Data\CommissionPaymentInterface::setComment()
     */
    public function setComment($comment)
    {
        return $this->setData(self::COMMENT, $comment);
    }

    /**
     *
     * {@inheritDoc}
     * @see \Magedelight\Commissions\Api\Data\CommissionPaymentInterface::setCommissionLevel()
     */
    public function setCommissionLevel($commissionLevel)
    {
        return $this->setData(self::COMMISSION_LEVEL, $commissionLevel);
    }

    /**
     *
     * {@inheritDoc}
     * @see \Magedelight\Commissions\Api\Data\CommissionPaymentInterface::setMarketplaceFee()
     */
    public function setMarketplaceFee($marketplaceFee)
    {
        return $this->setData(self::MARKETPLACE_FEE, $marketplaceFee);
    }

    /**
     *
     * {@inheritDoc}
     * @see \Magedelight\Commissions\Api\Data\CommissionPaymentInterface::setPaidAt()
     */
    public function setPaidAt($paidAt)
    {
        return $this->setData(self::PAID_AT, $paidAt);
    }

    /**
     *
     * {@inheritDoc}
     * @see \Magedelight\Commissions\Api\Data\CommissionPaymentInterface::setPurchaseOrderId()
     */
    public function setPurchaseOrderId($purchaseOrderId)
    {
        return $this->setData(self::PURCHASE_ORDER_ID, $purchaseOrderId);
    }

    /**
     *
     * {@inheritDoc}
     * @see \Magedelight\Commissions\Api\Data\CommissionPaymentInterface::setServiceTax()
     */
    public function setServiceTax($serviceTax)
    {
        return $this->setData(self::SERVICE_TAX, $serviceTax);
    }

    /**
     *
     * {@inheritDoc}
     * @see \Magedelight\Commissions\Api\Data\CommissionPaymentInterface::setShippingAmount()
     */
    public function setShippingAmount($shippingAmount)
    {
        return $this->setData(self::SHIPPING_AMOUNT, $shippingAmount);
    }

    /**
     *
     * {@inheritDoc}
     * @see \Magedelight\Commissions\Api\Data\CommissionPaymentInterface::setStatus()
     */
    public function setStatus($status)
    {
        return $this->setData(self::STATUS, $status);
    }

    /**
     *
     * {@inheritDoc}
     * @see \Magedelight\Commissions\Api\Data\CommissionPaymentInterface::setTaxAmount()
     */
    public function setTaxAmount($taxAmount)
    {
        return $this->setData(self::TAX_AMOUNT, $taxAmount);
    }

    /**
     *
     * {@inheritDoc}
     * @see \Magedelight\Commissions\Api\Data\CommissionPaymentInterface::setTotalAmount()
     */
    public function setTotalAmount($totalAmount)
    {
        return $this->setData(self::TOTAL_AMOUNT, $totalAmount);
    }

    /**
     *
     * {@inheritDoc}
     * @see \Magedelight\Commissions\Api\Data\CommissionPaymentInterface::setTotalCommission()
     */
    public function setTotalCommission($totalCommission)
    {
        return $this->setData(self::TOTAL_COMMISSION, $totalCommission);
    }

    /**
     *
     * {@inheritDoc}
     * @see \Magedelight\Commissions\Api\Data\CommissionPaymentInterface::setTotalDue()
     */
    public function setTotalDue($totalDue)
    {
        return $this->setData(self::TOTAL_DUE, $totalDue);
    }

    /**
     *
     * {@inheritDoc}
     * @see \Magedelight\Commissions\Api\Data\CommissionPaymentInterface::setTotalPaid()
     */
    public function setTotalPaid($totalPaid)
    {
        return $this->setData(self::TOTAL_PAID, $totalPaid);
    }

    /**
     *
     * {@inheritDoc}
     * @see \Magedelight\Commissions\Api\Data\CommissionPaymentInterface::setTransactionFee()
     */
    public function setTransactionFee($transactionFee)
    {
        return $this->setData(self::TRANSACTION_FEE, $transactionFee);
    }

    /**
     *
     * {@inheritDoc}
     * @see \Magedelight\Commissions\Api\Data\CommissionPaymentInterface::setVendorId()
     */
    public function setVendorId($vendorId)
    {
        return $this->setData(self::VENDOR_ID, $vendorId);
    }

    /*
     * Payout calculations
     */

    /**
     * Retrieve write connection instance
     *
     * @return bool|\Magento\Framework\DB\Adapter\AdapterInterface
     */
    protected function _getConnection()
    {
        if (null === $this->_connection) {
            $this->_connection = $this->getResource()->getConnection();
        }
        return $this->_connection;
    }

    /**
     *
     * @param \Magento\Sales\Model\Order\Item $item
     * @return int
     */
    protected function _getWebsiteId(\Magento\Sales\Model\Order\Item $item)
    {
        if (null === $this->websiteId) {
            $this->websiteId = $item->getStore()->getWebsiteId();
        }
        return $this->websiteId;
    }

    protected function _getPOGenerationDuration()
    {
        $duration = $this->scopeConfig->getValue(self::CONFIG_PATH_PO_DURATION, ScopeInterface::SCOPE_STORE, 0);
        if ($duration == '' || $duration == null) {
            return self::PO_DEFAULT_DURATION;
        }
        return $duration;
    }

    /**
     *
     * @return \Magedelight\Sales\Model\ResourceModel\Order\Collection
     */
    protected function _getUnpaidVendorOrders()
    {
        $poTimeStamp = strtotime($this->dateTime->gmtDate() . ' - ' . $this->_getPOGenerationDuration() . ' days');
        $collection = $this->vendorOrderCollection
            ->addFieldToFilter('status', ['eq' => VendorOrder::STATUS_COMPLETE])
            ->addFieldToFilter('created_at', ['lteq' => $this->dateTime->date(null, $poTimeStamp)])
            ->addFieldToFilter('po_generated', ['eq' => 0]);
        return $collection;
    }

    /**
     * get vendor order collection by vendor order ids.
     * @param array $vendorOrderIds
     * @return \Magedelight\Sales\Model\ResourceModel\Order\Collection
     */
    protected function _getVendorOrdersByIds($vendorOrderIds)
    {
        if (!empty($vendorOrderIds)) {
            $collection = $this->vendorOrderCollection
                ->addFieldToFilter('vendor_order_id', ['in' => $vendorOrderIds])
                ->addFieldToFilter('po_generated', ['eq' => 0]);
            return $collection;
        }
        return false;
    }

    /**
     *
     * @param int $websiteId
     * @param int $categoryId
     * @return array
     */
    protected function _getCategoryCommissionRateByCatId($websiteId, $categoryId)
    {
        if (!$categoryId) {
            return [];
        }
        if (!array_key_exists($categoryId, $this->categoryCommissionRates)) {
            $connection = $this->_getConnection();
            $select = $connection->select()
                ->from(self::CATEGORY_COMMISSION_TABLE, ['calculation_type','commission_value',
                    'marketplace_fee', 'marketplace_fee_type', 'cancellation_fee_commission_value',
                    'cancellation_fee_calculation_type'])
                ->where('status = ?', 1)
                ->where('product_category = ?', $categoryId)
                ->where('website_id = ?', $websiteId);
            $this->categoryCommissionRates[$categoryId] = $connection->fetchRow($select);
        }
        return $this->categoryCommissionRates[$categoryId];
    }

    /**
     *
     * @param int $websiteId
     * @param int $vendorId
     * @return array
     */
    protected function _getVendorCommissionRateByVendorId($websiteId, $vendorId)
    {
        if (!$vendorId) {
            return [];
        }
        if (!array_key_exists($vendorId, $this->vendorCommissionRates)) {
            $connection = $this->_getConnection();
            $select = $connection->select()
                ->from(self::VENDOR_COMMISSION_TABLE, ['vendor_calculation_type', 'vendor_commission_value',
                    'vendor_marketplace_fee_type', 'vendor_marketplace_fee', 'vendor_cancellation_fee_type',
                    'vendor_cancellation_fee'])
                ->where('vendor_status = ?', 1)
                ->where('vendor_id = ?', $vendorId)
                ->where('website_id = ?', $websiteId);
            $this->vendorCommissionRates[$vendorId] = $connection->fetchRow($select);
        }
        return $this->vendorCommissionRates[$vendorId];
    }

    public function getGlobalCommRate($websiteId = 1)
    {
        return $this->scopeConfig->getValue(
            self::CONFIG_PATH_COMM_RATE,
            ScopeInterface::SCOPE_WEBSITE,
            $websiteId
        );
    }

    public function getGlobalCommCalcType($websiteId = 1)
    {
        return $this->scopeConfig->getValue(
            self::CONFIG_PATH_COMM_CALC_TYPE,
            ScopeInterface::SCOPE_WEBSITE,
            $websiteId
        );
    }

    public function getGlobalMarketRate($websiteId = 1)
    {
        return $this->scopeConfig->getValue(
            self::CONFIG_PATH_COMM_MARKETPLACE_FEE,
            ScopeInterface::SCOPE_WEBSITE,
            $websiteId
        );
    }

    public function getGlobalMarketCalcType($websiteId = 1)
    {
        return $this->scopeConfig->getValue(
            self::CONFIG_PATH_COMM_MARKETPLACE_FEE_CALC_TYPE,
            ScopeInterface::SCOPE_WEBSITE,
            $websiteId
        );
    }

    public function getGlobalCancelRate($websiteId = 1)
    {
        return $this->scopeConfig->getValue(
            self::CONFIG_PATH_COMM_CANCELLATION_FEE,
            ScopeInterface::SCOPE_WEBSITE,
            $websiteId
        );
    }

    public function getGlobalCancelCalcType($websiteId = 1)
    {
        return $this->scopeConfig->getValue(
            self::CONFIG_PATH_COMM_CANCELLATION_FEE_CALC_TYPE,
            ScopeInterface::SCOPE_WEBSITE,
            $websiteId
        );
    }

    /**
     *
     * @param float $total
     * @param int $type
     * @param float $rate
     * @param int $qty (optional)
     * @param string $currencyCode (optional)
     * @return float
     */
    public function calculateRate($total, $type, $rate, $qty = 1, $currencyCode = null)
    {
        /* Take quantity as 1 if null passed.*/
        if ($qty === null) {
            $qty = 1;
        }
        switch ($type) {
            case self::CALCULATION_TYPE_FLAT:
                if ($currencyCode) {
                    return $this->catalogHelper->currency(
                        $rate * $qty,
                        false,
                        false,
                        true,
                        $currencyCode
                    );
                }
                return $rate * $qty;
            case self::CALCULATION_TYPE_PERCENTAGE:
                return ($total * $rate) / 100;
            default:
                return null;
        }
    }

    /**
     * Check Net payable will be included or excluded the discount amount
     * @return boolean
     */
    public function canAdjustDiscount()
    {
        return $this->scopeConfig->getValue(
            self::CONFIG_PATH_ADJUST_DISCOUNT,
            ScopeInterface::SCOPE_WEBSITE,
            0
        );
    }

    public function getItemRowTotalWithAdjustedDiscount(\Magento\Sales\Model\Order\Item $item)
    {
        if ($this->canAdjustDiscount()) {
            return ($item->getRowTotal() - $item->getAmountRefunded());
        } else {
            $discountAmount =  $this->discountProcessor->calculateVendorDiscountAmount(
                $item,
                $item->getVendorId()
            );
            return ($item->getRowTotal() - $discountAmount - (
                $item->getAmountRefunded() + $item->getDiscountRefunded()
            ));
        }
    }

    /**
     * Commission Calculations based on product rate
     * @param \Magento\Sales\Model\Order\Item $item
     * @return float|null Commission
     */
    protected function _getCommissionBasedOnProductRate(\Magento\Sales\Model\Order\Item $item)
    {
        /** @var \Magento\Catalog\Api\Data\ProductInterface $product */
        $product = $item->getProduct();
        if (!$product) {
            return null;
        }
        $commissionRate = $product->getData('md_commission');
        if ($commissionRate) {
            $commissionType = $product->getData('md_calculation_type');
            $total = $this->getItemRowTotalWithAdjustedDiscount($item);
            $qty = $item->getQtyInvoiced() > 0 ? (
                $item->getQtyInvoiced()-$item->getQtyRefunded()
            ) : $item->getQtyOrdered();

            $this->setFeesCalcDataHistory(
                $item,
                self::FEE_TYPE_COMMISSION,
                [
                    'precedence' => self::COMMISSION_LEVEL_PRODUCT,
                    'calculationType' => $commissionType,
                    'commissionRate' => $commissionRate
                ]
            );
            return $this->calculateRate(
                $total,
                $commissionType,
                $commissionRate,
                $qty,
                $item->getOrder()->getCurrencyCode()
            );
        }
    }

    protected function _getMarketPlaceBasedOnProductRate(\Magento\Sales\Model\Order\Item $item)
    {
        /** @var \Magento\Catalog\Api\Data\ProductInterface $product */
        $product = $item->getProduct();
        if (!$product) {
            return null;
        }
        $marketplaceRate = $product->getData('md_marketplace_fee');
        if ($marketplaceRate) {
            $marketplaceType = $product->getData('md_marketplace_fee_calc_type');
            $total = $this->getItemRowTotalWithAdjustedDiscount($item);
            $qty = $item->getQtyInvoiced() > 0 ? (
                $item->getQtyInvoiced()-$item->getQtyRefunded()
            ) : $item->getQtyOrdered();

            $this->setFeesCalcDataHistory(
                $item,
                self::FEE_TYPE_MARKETPLACE_FEE,
                [
                    'precedence' => self::COMMISSION_LEVEL_PRODUCT,
                    'calculationType' => $marketplaceType,
                    'commissionRate' => $marketplaceRate
                ]
            );
            return $this->calculateRate(
                $total,
                $marketplaceType,
                $marketplaceRate,
                $qty,
                $item->getOrder()->getCurrencyCode()
            );
        }
    }

    /**
     *
     * @param \Magento\Sales\Model\Order\Item $item
     * @return float
     */
    protected function _getCancellationBasedOnProductRate(\Magento\Sales\Model\Order\Item $item)
    {
        /** @var \Magento\Catalog\Api\Data\ProductInterface $product */
        $product = $item->getProduct();
        if (!$product) {
            return null;
        }
        $cancellationRate = $product->getData('md_cancellation_fee');
        if ($cancellationRate) {
            $cancellationType = $product->getData('md_cancellation_calc_type');
            $total            = $this->getItemRowTotalWithAdjustedDiscount($item);
            $qty              = $item->getQtyInvoiced() > 0 ? ($item->getQtyInvoiced()
                - $item->getQtyRefunded()) : $item->getQtyOrdered();

            $this->setFeesCalcDataHistory(
                $item,
                self::FEE_TYPE_CANCELLATION_FEE,
                ['precedence' => self::COMMISSION_LEVEL_PRODUCT, 'calculationType' => $cancellationType,
                'commissionRate' => $cancellationRate]
            );
            return $this->calculateRate(
                $total,
                $cancellationType,
                $cancellationRate,
                $qty,
                $item->getOrder()->getCurrencyCode()
            );
        }
    }

    /**
     * Commission Calculations based on category rate
     * @param \Magento\Sales\Model\Order\Item $item
     * @return float|null Commission
     */
    protected function _getCommissionBasedOnCategoryRate(\Magento\Sales\Model\Order\Item $item)
    {
        /** @var \Magento\Catalog\Api\Data\ProductInterface $product */
        $product = $item->getProduct();
        if (!$product) {
            return null;
        }
        $categoryIds = $product->getCategoryIds();
        if (!empty($categoryIds)) {
            $websiteId = $this->_getWebsiteId($item);
            $commRates = $this->_getCategoryCommissionRateByCatId($websiteId, $categoryIds[0]);
            if (!empty($commRates)) {
                $total = $this->getItemRowTotalWithAdjustedDiscount($item);
                $qty   = $item->getQtyInvoiced() > 0 ? ($item->getQtyInvoiced() - $item->getQtyRefunded())
                        : $item->getQtyOrdered();

                $this->setFeesCalcDataHistory(
                    $item,
                    self::FEE_TYPE_COMMISSION,
                    [
                        'precedence' => self::COMMISSION_LEVEL_CATEGORY,
                        'calculationType' => $commRates['calculation_type'],
                        'commissionRate' => $commRates['commission_value']
                    ]
                );
                return $this->calculateRate(
                    $total,
                    $commRates['calculation_type'],
                    $commRates['commission_value'],
                    $qty,
                    $item->getOrder()->getCurrencyCode()
                );
            }
        }
    }

    protected function _getMarketPlaceBasedOnCategoryRate(\Magento\Sales\Model\Order\Item $item)
    {
        /** @var \Magento\Catalog\Api\Data\ProductInterface $product */
        $product = $item->getProduct();
        if (!$product) {
            return null;
        }
        $categoryIds = $product->getCategoryIds();
        if (!empty($categoryIds)) {
            $websiteId = $this->_getWebsiteId($item);
            $catmarketRates = $this->_getCategoryCommissionRateByCatId($websiteId, $categoryIds[0]);
            if (!empty($catmarketRates)) {
                $total = $this->getItemRowTotalWithAdjustedDiscount($item);
                $qty   = $item->getQtyInvoiced() > 0 ? ($item->getQtyInvoiced() - $item->getQtyRefunded())
                        : $item->getQtyOrdered();

                $this->setFeesCalcDataHistory(
                    $item,
                    self::FEE_TYPE_MARKETPLACE_FEE,
                    [
                        'precedence' => self::COMMISSION_LEVEL_CATEGORY,
                        'calculationType' => $catmarketRates['calculation_type'],
                        'commissionRate' => $catmarketRates['commission_value']
                    ]
                );
                return $this->calculateRate(
                    $total,
                    $catmarketRates['marketplace_fee_type'],
                    $catmarketRates['marketplace_fee'],
                    $qty,
                    $item->getOrder()->getCurrencyCode()
                );
            }
        }
    }

    protected function _getCancellationBasedOnCategoryRate(\Magento\Sales\Model\Order\Item $item)
    {
        /** @var \Magento\Catalog\Api\Data\ProductInterface $product */
        $product = $item->getProduct();
        if (!$product) {
            return null;
        }
        $categoryIds = $product->getCategoryIds();
        if (!empty($categoryIds)) {
            $websiteId = $this->_getWebsiteId($item);
            $catcancellationRates = $this->_getCategoryCommissionRateByCatId($websiteId, $categoryIds[0]);
            if (!empty($catcancellationRates)) {
                $total = $this->getItemRowTotalWithAdjustedDiscount($item);
                $qty   = $item->getQtyInvoiced() > 0 ? ($item->getQtyInvoiced() - $item->getQtyRefunded())
                        : $item->getQtyOrdered();

                $this->setFeesCalcDataHistory(
                    $item,
                    self::FEE_TYPE_CANCELLATION_FEE,
                    ['precedence' => self::COMMISSION_LEVEL_CATEGORY,
                        'calculationType' => $catcancellationRates['cancellation_fee_calculation_type'],
                    'commissionRate' => $catcancellationRates['cancellation_fee_commission_value']]
                );
                return $this->calculateRate(
                    $total,
                    $catcancellationRates['cancellation_fee_calculation_type'],
                    $catcancellationRates['cancellation_fee_commission_value'],
                    $qty,
                    $item->getOrder()->getCurrencyCode()
                );
            }
        }
    }

    /**
     * Commission Calculations based on vendor rate
     * @param \Magento\Sales\Model\Order\Item $item
     * @return float|null Commission
     */
    protected function _getCommissionBasedOnVendorRate(\Magento\Sales\Model\Order\Item $item)
    {
        $vendorId = $item->getData('vendor_id');
        $websiteId = $this->_getWebsiteId($item);
        $commRates = $this->_getVendorCommissionRateByVendorId($websiteId, $vendorId);
        if (!empty($commRates)) {
            $total = $this->getItemRowTotalWithAdjustedDiscount($item);
            $qty = $item->getQtyInvoiced() > 0 ? (
                $item->getQtyInvoiced()-$item->getQtyRefunded()
            ) : $item->getQtyOrdered();
            $this->setFeesCalcDataHistory(
                $item,
                self::FEE_TYPE_COMMISSION,
                ['precedence' => self::COMMISSION_LEVEL_VENDOR,
                    'calculationType' => $commRates['vendor_calculation_type'],
                    'commissionRate' => $commRates['vendor_commission_value']]
            );
            return $this->calculateRate(
                $total,
                $commRates['vendor_calculation_type'],
                $commRates['vendor_commission_value'],
                $qty,
                $item->getOrder()->getCurrencyCode()
            );
        }
    }

    protected function _getMarketPlaceBasedOnVendorRate(\Magento\Sales\Model\Order\Item $item)
    {
        $vendorId = $item->getData('vendor_id');
        $websiteId = $this->_getWebsiteId($item);
        $marketVendorRates = $this->_getVendorCommissionRateByVendorId($websiteId, $vendorId);
        if (!empty($marketVendorRates)) {
            $total = $this->getItemRowTotalWithAdjustedDiscount($item);
            $qty   = $item->getQtyInvoiced() > 0 ? ($item->getQtyInvoiced() - $item->getQtyRefunded())
                    : $item->getQtyOrdered();

            $this->setFeesCalcDataHistory(
                $item,
                self::FEE_TYPE_MARKETPLACE_FEE,
                ['precedence' => self::COMMISSION_LEVEL_VENDOR,
                    'calculationType' => $marketVendorRates['vendor_marketplace_fee_type'],
                'commissionRate' => $marketVendorRates['vendor_marketplace_fee']]
            );
            return $this->calculateRate(
                $total,
                $marketVendorRates['vendor_marketplace_fee_type'],
                $marketVendorRates['vendor_marketplace_fee'],
                $qty,
                $item->getOrder()->getCurrencyCode()
            );
        }
    }

    protected function _getCancellationBasedOnVendorRate(\Magento\Sales\Model\Order\Item $item)
    {
        $vendorId = $item->getData('vendor_id');
        $websiteId = $this->_getWebsiteId($item);
        $cancellationVendorRates = $this->_getVendorCommissionRateByVendorId($websiteId, $vendorId);
        if (!empty($cancellationVendorRates)) {
            $total = $this->getItemRowTotalWithAdjustedDiscount($item);
            $qty   = $item->getQtyInvoiced() > 0 ? ($item->getQtyInvoiced() - $item->getQtyRefunded())
                    : $item->getQtyOrdered();

            $this->setFeesCalcDataHistory(
                $item,
                self::FEE_TYPE_CANCELLATION_FEE,
                ['precedence' => self::COMMISSION_LEVEL_VENDOR,
                    'calculationType' => $cancellationVendorRates['vendor_cancellation_fee_type'],
                'commissionRate' => $cancellationVendorRates['vendor_cancellation_fee']]
            );
            return $this->calculateRate(
                $total,
                $cancellationVendorRates['vendor_cancellation_fee_type'],
                $cancellationVendorRates['vendor_cancellation_fee'],
                $qty,
                $item->getOrder()->getCurrencyCode()
            );
        }
    }

    /**
     * Commission Calculations based on global rate
     * @param \Magento\Sales\Model\Order\Item $item
     * @return float|null Commission
     */
    protected function _getCommissionBasedOnGlobalRate(\Magento\Sales\Model\Order\Item $item)
    {
        $websiteid = $this->_getWebsiteId($item);
        $globalCommCalcType = $this->getGlobalCommCalcType($websiteid);
        $globalCommRate     = $this->getGlobalCommRate($websiteid);
        $total              = $this->getItemRowTotalWithAdjustedDiscount($item);
        $qty                = $item->getQtyInvoiced() > 0 ? ($item->getQtyInvoiced()
            - $item->getQtyRefunded()) : $item->getQtyOrdered();
        $this->setFeesCalcDataHistory(
            $item,
            self::FEE_TYPE_COMMISSION,
            ['precedence' => self::COMMISSION_LEVEL_GLOBAL, 'calculationType' => $globalCommCalcType,
            'commissionRate' => $globalCommRate]
        );
        return $this->calculateRate(
            $total,
            $globalCommCalcType,
            $globalCommRate,
            $qty,
            $item->getOrder()->getCurrencyCode()
        );
    }

    protected function _getMarketPlaceBasedOnGlobalRate(\Magento\Sales\Model\Order\Item $item)
    {
        $websiteid = $this->_getWebsiteId($item);
        $globalMarketCalcType = $this->getGlobalMarketCalcType($websiteid);
        $globalMarketRate     = $this->getGlobalMarketRate($websiteid);
        $total                = $this->getItemRowTotalWithAdjustedDiscount($item);
        $qty                  = $item->getQtyInvoiced() > 0 ? ($item->getQtyInvoiced()
            - $item->getQtyRefunded()) : $item->getQtyOrdered();
        $this->setFeesCalcDataHistory(
            $item,
            self::FEE_TYPE_MARKETPLACE_FEE,
            ['precedence' => self::COMMISSION_LEVEL_GLOBAL, 'calculationType' => $globalMarketCalcType,
            'commissionRate' => $globalMarketRate]
        );
        return $this->calculateRate(
            $total,
            $globalMarketCalcType,
            $globalMarketRate,
            $qty,
            $item->getOrder()->getCurrencyCode()
        );
    }

    protected function _getCancellationBasedOnGlobalRate(\Magento\Sales\Model\Order\Item $item)
    {
        $websiteid = $this->_getWebsiteId($item);
        $globalCancelCalcType = $this->getGlobalCancelCalcType($websiteid);
        $globalCancelRate = $this->getGlobalCancelRate($websiteid);
        $total = $this->getItemRowTotalWithAdjustedDiscount($item);
        $qty = $item->getQtyInvoiced() > 0 ? (
            $item->getQtyInvoiced()-$item->getQtyRefunded()
        ) : $item->getQtyOrdered();

        $this->setFeesCalcDataHistory(
            $item,
            self::FEE_TYPE_CANCELLATION_FEE,
            ['precedence' => self::COMMISSION_LEVEL_GLOBAL,'calculationType' => $globalCancelCalcType,
                'commissionRate' => $globalCancelRate]
        );
        return $this->calculateRate(
            $total,
            $globalCancelCalcType,
            $globalCancelRate,
            $qty,
            $item->getOrder()->getCurrencyCode()
        );
    }

    /**
     * get commission rate based on precedence level
     * @param int $precedence
     * @return float|null
     */
    public function getCommissionRateByPrecedence($precedence, $websiteId = 1, $vendorId = null, $catId = null)
    {
        switch ($precedence) {
            case self::COMMISSION_LEVEL_CATEGORY:
                $commRates = $this->_getCategoryCommissionRateByCatId($websiteId, $catId);
                if (!empty($commRates)) {
                    return [
                        'rate' => $commRates['commission_value'],
                        'calc_type' => $commRates['calculation_type']
                    ];
                }
                break;
            case self::COMMISSION_LEVEL_VENDOR:
                $commRates = $this->_getVendorCommissionRateByVendorId($websiteId, $vendorId);
                if (!empty($commRates)) {
                    return [
                        'rate' => $commRates['vendor_commission_value'],
                        'calc_type' => $commRates['vendor_calculation_type']
                    ];
                }
                return [];
            default:
                return [
                    'rate' => $this->getGlobalCommRate($websiteId),
                    'calc_type' => $this->getGlobalCommCalcType($websiteId)
                ];
        }
    }

    /**
     * calculate commission based on precedence level
     * @param int $precedence
     * @param \Magento\Sales\Model\Order\Item $item
     * @return float|null
     */
    public function getCommissionAmountByPrecedence($precedence, \Magento\Sales\Model\Order\Item $item)
    {
        switch ($precedence) {
            case self::COMMISSION_LEVEL_PRODUCT:
                return $this->_getCommissionBasedOnProductRate($item);
            case self::COMMISSION_LEVEL_CATEGORY:
                return $this->_getCommissionBasedOnCategoryRate($item);
            case self::COMMISSION_LEVEL_VENDOR:
                return $this->_getCommissionBasedOnVendorRate($item);
            default:
                return $this->_getCommissionBasedOnGlobalRate($item);
        }
    }

    public function getMarketPlaceAmountByPrecedence($precedence, \Magento\Sales\Model\Order\Item $item)
    {
        switch ($precedence) {
            case self::COMMISSION_LEVEL_PRODUCT:
                return $this->_getMarketPlaceBasedOnProductRate($item);
            case self::COMMISSION_LEVEL_CATEGORY:
                return $this->_getMarketPlaceBasedOnCategoryRate($item);
            case self::COMMISSION_LEVEL_VENDOR:
                return $this->_getMarketPlaceBasedOnVendorRate($item);
            default:
                return $this->_getMarketPlaceBasedOnGlobalRate($item);
        }
    }

    public function getCancellationAmountByPrecedence($precedence, \Magento\Sales\Model\Order\Item $item)
    {
        switch ($precedence) {
            case self::COMMISSION_LEVEL_PRODUCT:
                return $this->_getCancellationBasedOnProductRate($item);
            case self::COMMISSION_LEVEL_CATEGORY:
                return $this->_getCancellationBasedOnCategoryRate($item);
            case self::COMMISSION_LEVEL_VENDOR:
                return $this->_getCancellationBasedOnVendorRate($item);
            default:
                return $this->_getCancellationBasedOnGlobalRate($item);
        }
    }

    /**
     * get commission level precedence from configurations.
     * @param int $websiteId
     * @return array
     */
    public function getCommissionLevelPrecedences($websiteId = 1)
    {
        if (empty($this->precedences)) {
            $precedenceConfig = $this->scopeConfig->getValue(
                self::CONFIG_PATH_PO_COMM_LEVEL_PRECEDENCE,
                ScopeInterface::SCOPE_WEBSITE,
                $websiteId
            );
            foreach ($precedenceConfig as $precedenceValue) {
                $this->precedences[] = $precedenceValue;
            }
            $this->precedences[] = self::COMMISSION_LEVEL_GLOBAL;
        }
        return $this->precedences;
    }

    /**
     * @param \Magedelight\Sales\Model\Order $vendorOrder
     * @param int $websiteId
     * @return array
     */
    public function calculateCommissionAmount(\Magedelight\Sales\Model\Order $vendorOrder, $websiteId = 1)
    {
        $precedences = $this->getCommissionLevelPrecedences($websiteId);
        $commissionData = [];
        $commissionData['commission'] = 0;
        $commissionData['marketplace_fee'] = 0;
        $vendorOrderCommission = 0;
        $vendorOrderMarketPlaceFee = 0;
        foreach ($vendorOrder->getItems() as $item) {
            foreach ($precedences as $precedence) {
                $itemCommission = $this->getCommissionAmountByPrecedence($precedence, $item);
                if ($itemCommission !== '' && $itemCommission !== null) {
                    $vendorOrderCommission += $itemCommission;
                    $itemMarketPlaceFee = $this->getMarketPlaceAmountByPrecedence($precedence, $item);
                    $vendorOrderMarketPlaceFee += $itemMarketPlaceFee;
                    /**
                     * @todo create seperate table to define commission level
                     * fields will be as following
                     * item_id
                     * commission_level(0 => product, 1 => Category, 2 => Vendor, 3 => Global)
                     * commission_amount
                     */
                    unset($itemCommission);
                    unset($itemMarketPlaceFee);
                    break;
                }
            }
        }

        $commissionData['commission'] = $vendorOrderCommission;
        $commissionData['marketplace_fee'] = $vendorOrderMarketPlaceFee;

        return $commissionData;
    }

    public function calculateMarketPlaceFeeAmount(\Magedelight\Sales\Model\Order $vendorOrder, $websiteId = 1)
    {
        $precedences = $this->getCommissionLevelPrecedences($websiteId);
        $vendorOrderMarketPlace = 0;
        foreach ($vendorOrder->getItems() as $item) {
            foreach ($precedences as $precedence) {
                $itemMarketPlace = $this->getMarketPlaceAmountByPrecedence($precedence, $item);
                $vendorOrderMarketPlace += $itemMarketPlace;
                /**
                 * @todo create seperate table to define commission level
                 * fields will be as following
                 * item_id
                 * commission_level(0 => product, 1 => Category, 2 => Vendor, 3 => Global)
                 * commission_amount
                 */
                unset($itemMarketPlace);
                break;
            }
        }
        return $vendorOrderMarketPlace;
    }

    public function calculateCancellationAmount(\Magedelight\Sales\Model\Order $vendorOrder)
    {
        $precedences = $this->getCommissionLevelPrecedences($vendorOrder->getStore()->getWebsiteId());
        $vendorOrderCancellation = 0;
        foreach ($vendorOrder->getItems() as $item) {
            foreach ($precedences as $precedence) {
                $itemCancellation = $this->getCancellationAmountByPrecedence($precedence, $item);
                if ($itemCancellation != '' && $itemCancellation != null) {
                    $vendorOrderCancellation += $itemCancellation;
                    /**
                     * @todo create seperate table to define commission level
                     * fields will be as following
                     * item_id
                     * commission_level(0 => product, 1 => Category, 2 => Vendor, 3 => Global)
                     * commission_amount
                     */
                    unset($itemCancellation);
                    break;
                }
            }
        }
        return $vendorOrderCancellation;
    }

    /**
     *
     * @param integer $websiteId
     * @return integer
     */
    public function getMarketplaceFeeRate($websiteId = 1)
    {
        $marketplaceFeeRate['calc_type'] = $this->scopeConfig->getValue(
            self::CONFIG_PATH_COMM_MARKETPLACE_FEE_CALC_TYPE,
            ScopeInterface::SCOPE_WEBSITE,
            $websiteId
        );
        $marketplaceFeeRate['rate'] = $this->scopeConfig->getValue(
            self::CONFIG_PATH_COMM_MARKETPLACE_FEE,
            ScopeInterface::SCOPE_WEBSITE,
            $websiteId
        );
        return $marketplaceFeeRate;
    }

    /**
     *
     * @param VendorOrder $vendorOrder
     * @return integer
     */
    public function calculateMarketplaceFee(\Magedelight\Sales\Model\Order $vendorOrder)
    {
        $websiteId = $vendorOrder->getStore()->getWebsiteId();
        $marketplaceFeeRate = $this->getMarketplaceFeeRate($websiteId);
        return $this->calculateRate(
            $vendorOrder->getData('subtotal') - $vendorOrder->getData('subtotal_refunded'),
            $marketplaceFeeRate['calc_type'],
            $marketplaceFeeRate['rate'],
            1,
            $vendorOrder->getData('order_currency_code')
        );
    }

    /**
     *
     * @param VendorOrder $vendorOrder
     * @return integer
     */
    public function calculateCancellationFee(\Magedelight\Sales\Model\Order $vendorOrder)
    {
        $websiteId = $vendorOrder->getStore()->getWebsiteId();
        $cancellationFeeRate = floatval($this->getGlobalCancelRate($websiteId));
        $cancellationFeeCalcType = $this->getGlobalCancelCalcType($websiteId);
        return $this->calculateRate(
            $vendorOrder->getData('subtotal'),
            $cancellationFeeCalcType,
            $cancellationFeeRate,
            1,
            $vendorOrder->getData('order_currency_code')
        );
    }

    /**
     *
     * @param integer $websiteId
     * @return integer
     */
    public function getServiceTaxRate($websiteId = 1)
    {
        return $this->scopeConfig->getValue(
            self::CONFIG_PATH_SERVICE_TAX_RATE,
            ScopeInterface::SCOPE_WEBSITE,
            $websiteId
        );
    }

    /**
     *
     * @param integer $total
     * @param integer $websiteId
     * @return integer
     */
    public function calculateServiceTax($total, $websiteId = 1)
    {
        $serviceTaxRate = $this->getServiceTaxRate($websiteId);
        return $this->calculateRate(
            $total,
            self::CALCULATION_TYPE_PERCENTAGE,
            $serviceTaxRate
        );
    }

    /**
     * @param integer $websiteId
     * @return boolean
     */
    public function isVendorLiableForShipping($websiteId = 1)
    {
        $shippingLiableActor = (int)$this->scopeConfig->getValue(
            self::CONFIG_PATH_PO_SHIPPING_LIABILITY,
            ScopeInterface::SCOPE_WEBSITE,
            $websiteId
        );
        return ($shippingLiableActor === self::ACTOR_VENDOR);
    }

    /**
     * @param integer $websiteId
     * @return boolean
     */
    public function isVendorLiableForAdjustment($websiteId = 1)
    {
        $adjustmentLiableActor = (int)$this->scopeConfig->getValue(
            self::CONFIG_PATH_PO_ADJUSTMENT_LIABILITY,
            ScopeInterface::SCOPE_WEBSITE,
            $websiteId
        );
        return ($adjustmentLiableActor === self::ACTOR_VENDOR);
    }

    /**
     * @param integer $websiteId
     * @return boolean
     */
    protected function _isVendorLiableForTax($websiteId = 1)
    {
        $taxLiableActor = (int)$this->scopeConfig->getValue(
            self::CONFIG_PATH_PO_TAX_LIABILITY,
            ScopeInterface::SCOPE_WEBSITE,
            $websiteId
        );
        return ($taxLiableActor === self::ACTOR_VENDOR);
    }

    /**
     * generate pay orders for vendor orders.
     * @return void
     */
    public function processPayOrders()
    {
        $vendorOrders = $this->_getUnpaidVendorOrders();
        if (!$vendorOrders->getSize()) {
            return false;
        }
        $vendorPayments = [];
        foreach ($vendorOrders as $vendorOrder) {
            /** @var \Magedelight\Sales\Model\Order $vendorOrder */
            $vendorPayments[] = $this->generatePO($vendorOrder);
        }
        if (!empty($vendorPayments)) {
            $connection = $this->_getConnection();
            $connection->insertMultiple($connection->getTableName(
                self::VENDOR_PAYMENTS_TABLE
            ), $vendorPayments);
            if (!empty($vendorOrders->getAllIds())) {
                /*
                 * Mark vendor order as po_generated
                 */
                $connection->update(
                    $this->getResource()->getTable(self::VENDOR_ORDER_TABLE),
                    ['po_generated' => 1],
                    ["vendor_order_id IN(?)" => $vendorOrders->getAllIds()]
                );
            }
        }
    }

    /**
     *
     * @param VendorOrder $vendorOrder
     * @return array
     */
    public function generatePO(VendorOrder $vendorOrder)
    {
        $this->itemData = [];
        $websiteId = $vendorOrder->getStore()->getWebsiteId();
        $vendorPayment = [];
        $poIdPrefix = self::PO_ID_PREFIX;
        $invoiceIdPrefix = self::INVOICE_ID_PREFIX;
        /**
         * @todo create a global configuration fields to facilitate admin to define prefix.
         */
        $vendorPayment['purchase_order_id'] = $poIdPrefix . '-' . $vendorOrder->getData('increment_id');
        $vendorPayment['commission_invoice_id'] = $invoiceIdPrefix . '-' .
            $vendorOrder->getData('increment_id');
        $vendorPayment['vendor_order_id'] = $vendorOrder->getId();
        $vendorPayment['vendor_id'] = $vendorOrder->getData('vendor_id');
        $vendorPayment['website_id'] = $websiteId;

        if (!$this->checkIsTopupProduct($vendorOrder->getId())) {
            $commissionData = $this->calculateCommissionAmount($vendorOrder, $websiteId);
            $vendorPayment['total_commission'] = round($commissionData['commission'], 2);
            $vendorPayment['marketplace_fee']  = round($commissionData['marketplace_fee'], 2);

            $stAmount = $vendorPayment['total_commission'] + $vendorPayment['marketplace_fee'];
            $vendorPayment['service_tax'] = round($this->calculateServiceTax($stAmount, $websiteId), 2);
            $totalAmount = $vendorOrder->getData('grand_total');
            if ($this->canAdjustDiscount()) {
                /* Discount added to vendor net payable */
                $totalAmount += abs($vendorOrder->getData(
                    'discount_invoiced'
                )) - abs($vendorOrder->getData('discount_refunded'));
            }
            $vendorPayment['shipping_amount'] = $vendorOrder->getData(
                'shipping_amount'
            ) - $vendorOrder->getData("shipping_refunded");
            if (!$this->isVendorLiableForShipping($websiteId)) {
                /* shipping_amount will reverse from vendor net payable */
                $totalAmount -= $vendorPayment['shipping_amount'];
                $vendorPayment['shipping_amount'] = $vendorPayment['shipping_amount'] * -1;
            }
            if (!$this->_isVendorLiableForTax($websiteId)) {
                $vendorPayment['tax_amount'] = $vendorOrder->getData(
                    'tax_amount'
                )  - $vendorOrder->getData("tax_refunded");
                $totalAmount -= $vendorPayment['tax_amount'];
            }
            if ($this->isVendorLiableForAdjustment($websiteId)) {
                $vendorPayment['adjustment_amount'] = $vendorOrder->getData(
                    'adjustment_negative'
                ) - $vendorOrder->getData("adjustment_positive");
                $totalAmount += $vendorPayment['adjustment_amount'];
            }
            $totalAmount -= $stAmount;
            $totalAmount -= $vendorPayment['service_tax'];
            $totalAmount -= floatval($vendorOrder->getData("total_refunded"));
            $vendorPayment['total_amount'] = round($totalAmount, 2);
            $vendorPayment['transaction_summary']  =  $this->serializer->serialize($this->itemData);
            $vendorPayment['transaction_type'] = self::CREDIT_TRANSACTION_TYPE;
        }
        return $vendorPayment;
    }

    /**
     *
     * @param VendorOrder $vendorOrder
     * @return boolean
     */
    public function updatePO(VendorOrder $vendorOrder)
    {
        if ($vendorOrder->getData('po_generated') == 1) {
            $vendorPayment = $this->generatePO($vendorOrder);
            $connection    = $this->_getConnection();
            $connection->update(
                $connection->getTableName(self::VENDOR_PAYMENTS_TABLE),
                $vendorPayment,
                [
                "vendor_order_id=?" => $vendorOrder->getId()
                ]
            );
            return true;
        }
        return false;
    }

    /**
     * Check if order has Store credit topup product.
     * @return void
     */
    public function checkIsTopupProduct($vendorOrderId)
    {
        $connection = $this->_getConnection();
        $sql = "SELECT sales_order_item.Name FROM md_vendor_order LEFT JOIN sales_order_item ON 
sales_order_item.order_id=md_vendor_order.order_id WHERE md_vendor_order.vendor_order_id = " . $vendorOrderId;

        $result = $connection->fetchAll($sql);
        foreach ($result as $result) {
            if ($result['Name'] == "Store Credit Top up") {
                return true;
            }
        }
        return false;
    }

    /**
     * generate pay orders for cancelled vendor orders.
     * @return void
     */
    public function processCancelledPayOrders($vendorOrderIds)
    {
        $vendorOrders = $this->_getVendorOrdersByIds($vendorOrderIds);
        if (!$vendorOrders->getSize()) {
            return false;
        }
        $vendorPayments = [];
        foreach ($vendorOrders as $vendorOrder) {
            /** @var \Magedelight\Sales\Model\Order $vendorOrder */
            $vendorPayments[] = $this->generateCancelledPO($vendorOrder);
        }
        if (!empty($vendorPayments)) {
            $connection = $this->_getConnection();
            $connection->insertMultiple($connection->getTableName(
                self::VENDOR_PAYMENTS_TABLE
            ), $vendorPayments);
            if (!empty($vendorOrders->getAllIds())) {
                /*
                 * Mark vendor order as po_generated
                 */
                $connection->update(
                    $this->_getConnection()->getTableName(self::VENDOR_ORDER_TABLE),
                    ['po_generated' => 1],
                    ["vendor_order_id IN(?)" => $vendorOrders->getAllIds()]
                );
            }
        }
    }

    /**
     *
     * @param \Magento\Sales\Model\Order\Item $item $item
     * @param string $key
     * @param array $data
     * @return $this
     */
    public function setFeesCalcDataHistory($item, $key, $data)
    {
        $this->itemData[$item->getVendorOrderId()]['itemName'] = $item->getName();
        $this->itemData[$item->getVendorOrderId()][$key] = $data;
        $this->itemData[$item->getVendorOrderId()]['service_tax'] = $this->getServiceTaxRate(
            $this->_getWebsiteId($item)
        ); /* added service tax percentage in trans. summary */
        return $this;
    }

    /**
     *
     * @param VendorOrder $vendorOrder
     * @return array
     */
    public function generateCancelledPO(VendorOrder $vendorOrder)
    {
        $vendorPayment = [];
        $poIdPrefix = self::PO_ID_PREFIX;
        $invoiceIdPrefix = self::INVOICE_ID_PREFIX;
        /**
         * @todo create a global configuration fields to facilitate admin to define prefix.
         */
        $websiteId = $vendorOrder->getStore()->getWebsiteId();
        $vendorPayment['purchase_order_id'] = $poIdPrefix . '-' . $vendorOrder->getData('increment_id');
        $vendorPayment['commission_invoice_id'] = $invoiceIdPrefix . '-' . $vendorOrder->getData(
            'increment_id'
        );
        $vendorPayment['vendor_order_id'] = $vendorOrder->getId();
        $vendorPayment['vendor_id'] = $vendorOrder->getData('vendor_id');
        $vendorPayment['store_id'] = $websiteId;
        //$vendorPayment['cancellation_fee'] = $this->calculateCancellationFee($vendorOrder);
        $vendorPayment['cancellation_fee'] = $this->calculateCancellationAmount($vendorOrder);
        $vendorPayment['service_tax'] = $this->calculateServiceTax(
            $vendorPayment['cancellation_fee'],
            $websiteId
        );
        $vendorPayment['total_amount'] = ($vendorPayment['cancellation_fee'] + $vendorPayment['service_tax']) * -1;
        $vendorPayment['transaction_summary']  =  $this->serializer->serialize($this->itemData);
        $vendorPayment['transaction_type'] = self::DEBIT_TRANSACTION_TYPE;
        return $vendorPayment;
    }

    /**
     * Retrieve vendor.
     *
     * @return \Magedelight\Vendor\Api\Data\VendorInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException If vendor with the specified ID does not exist.
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getVendor()
    {
        return $this->vendorRepository->getById($this->getVendorId());
    }

    /**
     * @return void
     */
    public function afterPay()
    {
        $this->setTotalPaid($this->getTotalAmount())
        ->setPaidAt($this->dateTime->gmtTimestamp())
        ->setStatus(\Magedelight\Commissions\Model\Source\PaymentStatus::PAYMENT_STATUS_PAID)
        ->save();
    }
}
