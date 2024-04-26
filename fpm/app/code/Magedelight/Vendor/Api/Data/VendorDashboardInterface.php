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
namespace Magedelight\Vendor\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * @api
 */
interface VendorDashboardInterface extends ExtensibleDataInterface
{
    /**#@+
     * Constants defined for keys of the data array. Identical to the name of the getter in snake case
     */
    const LIFETIME_SALES = 'lifetime_sales';
    const AVG_ORDER = 'average_order';
    const TRANSACTION = 'transactions';
    const PRODUCTS_SOLD = 'products_sold';
    const AMT_PAID = 'amount_paid';
    const AMT_BAL = 'amount_balance';
    const AMT_BAL_TRANSACTION = 'amount_balance_transaction';
    const AMT_BAL_WO_FORMAT = 'amount_balance_without_format';
    const LAST_APPROVED_ITEM = 'last_approved_item';
    const AVG_RATING = 'avg_rating';
    const BEST_SELLING_COL = 'best_selling_items';
    const TRANS_SUM_URL = 'transaction_summary_url';
    const APPR_PROD_URL = 'approved_product_url';
    const STATUS = 'status';
    const VENDOR_ID = 'vendor_id';
    const DASHBOARD_OVERVIEW = 'overview';
    const STATUS_ID = 'status_id';
    const STATUS_MSG = 'status_msg';
    const VENDOR_EMAIL = 'email';
    const AVAILABLE_STORES = 'available_stores';
    const CURRENT_CURRENCY = 'current_currency';
    const CURRENT_CURRENCY_SYMBOL = 'current_currency_symbol';
    const PRICE_FORMAT = 'price_format';

    /**
     * @param string $status
     * @return $this
     */
    public function setStatus($status);

    /**
     * @return string
     */
    public function getStatus();

    /**
     * @param int $statusId
     * @return $this
     */
    public function setStatusId($statusId);

    /**
     * @return int
     */
    public function getStatusId();

    /**
     * @param string $statusMsg
     * @return $this
     */
    public function setStatusMsg($statusMsg);

    /**
     * @return string
     */
    public function getStatusMsg();

    /**
     * @param int $vendorId
     * @return $this
     */
    public function setVendorId($vendorId);

    /**
     * @return int
     */
    public function getVendorId();

    /**
     * @param string $email
     * @return $this
     */
    public function setVendorEmail($email);

    /**
     * @return string
     */
    public function getVendorEmail();

    /**
     * Get LifeTime Sales
     * @return float|string|null
     */
    public function getLifeTimeSales();

    /**
     * Set Life Time Sales
     * @param float|string|null $lifeTimeSales
     * @return $this
     */
    public function setLifeTimeSales($lifeTimeSales);

    /**
     * Get Average Order
     * @return float|string|null
     */
    public function getAverageOrder();

    /**
     * Set Average Order
     *
     * @param string|float|null $averageOrder
     * @return $this
     */
    public function setAverageOrder($averageOrder);

    /**
     * Get Order Count
     * @return int
     */
    public function getOrderCount();

    /**
     * Set email address
     * @param int $orderCount
     * @return $this
     */
    public function setOrderCount($orderCount);

    /**
     * Get product sold
     * @return int|null
     */
    public function getProductsSold();

    /**
     * Set product sold
     * @param int $productsSold
     * @return $this
     */
    public function setProductsSold($productsSold);

    /**
     * Get Amount Paid
     * @return string|float|null
     */
    public function getAmountPaid();

    /**
     * Set Amount Paid
     * @param string|float|null $amountPaid
     * @return $this
     */
    public function setAmountPaid($amountPaid);

    /**
     * Get Amount Balance
     * @return string|float|null
     */
    public function getAmountBalance();

    /**
     * Get Amount Balance
     * @param string|float|null $amountBalance
     * @return $this
     */
    public function setAmountBalance($amountBalance);

    /**
     * Get Amount Balance Transaction
     * @return int|null
     */
    public function getAmountBalanceTransaction();

    /**
     * Set Amount Balance Transaction
     * @param int $amtBalTransaction
     * @return $this
     */
    public function setAmountBalanceTransaction($amtBalTransaction);

    /**
     * Get Amount Balance Without Format
     * @return float|null
     */
    public function getAmountBalanceWithoutFormat();

    /**
     * Set Amount Balance Transaction
     * @param float|null $amtBalWoFormat
     * @return $this
     */
    public function setAmountBalanceWithoutFormat($amtBalWoFormat);

    /**
     * Get Last Approved Items
     * @return \Magedelight\Catalog\Api\Data\VendorProductSearchResultInterface[] $lastApprovedItems
     */
    public function getLastApprovedItems();

    /**
     * Set Last Approved Items
     * @param \Magedelight\Catalog\Api\Data\VendorProductSearchResultInterface[] $lastApprovedItems
     * @return $this
     */
    public function setLastApprovedItems($lastApprovedItems);

    /**
     * @param \Magedelight\Vendor\Api\Data\VendorReviewInterface[] $ratingAvg
     * @return $this
     */
    public function setRatingAvg($ratingAvg);

    /**
     * @return \Magedelight\Vendor\Api\Data\VendorReviewInterface[] $ratingAvg
     */
    public function getRatingAvg();

    /**
     * @param \Magento\Sales\Api\Data\OrderItemInterface[] $bestSellers
     * @return $this
     */
    public function setBestSellingItems($bestSellers);

    /**
     * @return \Magento\Sales\Api\Data\OrderItemInterface[] $bestSellers
     */
    public function getBestSellingItems();

    /**
     * @param string $transactionSummaryUrl
     * @return $this
     */
    public function setTransactionSummaryUrl($transactionSummaryUrl);

    /**
     * @return string
     */
    public function getTransactionSummaryUrl();

    /**
     * @param string $approvedProductUrl
     * @return $this
     */
    public function setApprovedProductUrl($approvedProductUrl);

    /**
     * @return string
     */
    public function getApprovedProductUrl();

    /**
     * @param Magedelight\Vendor\Api\Data\DashboardOverviewInterface[] $overViewData
     * @return $this
     */
    public function setDashboardOverview($overViewData);

    /**
     * @return Magedelight\Vendor\Api\Data\DashboardOverviewInterface[] $overViewData
     */
    public function getDashboardOverview();

    /**
     * Sets available stores.
     *
     * @param \Magedelight\Vendor\Api\Data\StoreDataInterface[] $availableStores
     * @return $this
     */
    public function setAvailableStores($availableStores);

    /**
     * Gets available stores.
     *
     * @return \Magedelight\Vendor\Api\Data\StoreDataInterface[]
     */
    public function getAvailableStores();

    /**
     * @param string $language
     * @return $this
     */
    public function setCurrentCurrency(string $currency);

    /**
     * @return string.
     */
    public function getCurrentCurrency();

    /**
     * @return string
     */
    public function getCurrentCurrencySymbol();

    /**
     * @param string $symbol
     * @return $this
     */
    public function setCurrentCurrencySymbol(string $symbol);

    /**
     * Sets Price Format.
     *
     * @param \Magedelight\MobileInit\Api\Data\MobilePriceFormatDataInterface[] array $priceFormat
     * @return $this
     */
    public function setPriceFormat(array $priceFormat);

    /**
     * Gets Price Format.
     *
     * @return \Magedelight\MobileInit\Api\Data\MobilePriceFormatDataInterface[] array $priceFormat
     */
    public function getPriceFormat();

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Magedelight\Vendor\Api\Data\VendorDashboardExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Magedelight\Vendor\Api\Data\VendorDashboardExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Magedelight\Vendor\Api\Data\VendorDashboardExtensionInterface $extensionAttributes
    );
}
