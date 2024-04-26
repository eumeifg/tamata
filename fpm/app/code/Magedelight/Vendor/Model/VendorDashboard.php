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

use Magedelight\Vendor\Api\Data\VendorDashboardInterface;
use Magento\Framework\Model\AbstractExtensibleModel;

class VendorDashboard extends AbstractExtensibleModel implements VendorDashboardInterface
{
    /**
     * @inheritdoc
     */
    public function setStatus($status)
    {
        return $this->setData(VendorDashboardInterface::STATUS, $status);
    }

    /**
     * @inheritdoc
     */
    public function getStatus()
    {
        return $this->getData(VendorDashboardInterface::STATUS);
    }

    /**
     * @inheritdoc
     */
    public function setStatusId($statusId)
    {
        return $this->setData(VendorDashboardInterface::STATUS_ID, $statusId);
    }

    /**
     * @inheritdoc
     */
    public function getStatusId()
    {
        return $this->getData(VendorDashboardInterface::STATUS_ID);
    }

    /**
     * @inheritdoc
     */
    public function setStatusMsg($statusMsg)
    {
        return $this->setData(VendorDashboardInterface::STATUS_MSG, $statusMsg);
    }

    /**
     * @inheritdoc
     */
    public function getStatusMsg()
    {
        return $this->getData(VendorDashboardInterface::STATUS_MSG);
    }

    /**
     * @inheritdoc
     */
    public function setVendorId($vendorId)
    {
        return $this->setData(VendorDashboardInterface::VENDOR_ID, $vendorId);
    }

    /**
     * @inheritdoc
     */
    public function getVendorId()
    {
        return $this->getData(VendorDashboardInterface::VENDOR_ID);
    }

    /**
     * @inheritdoc
     */
    public function setVendorEmail($email)
    {
        return $this->setData(VendorDashboardInterface::VENDOR_EMAIL, $email);
    }

    /**
     * @inheritdoc
     */
    public function getVendorEmail()
    {
        return $this->getData(VendorDashboardInterface::VENDOR_EMAIL);
    }

    /**
     * @inheritdoc
     */
    public function getLifeTimeSales()
    {
        return $this->getData('lifetime_sales');
    }

    /**
     * @inheritdoc
     */
    public function setLifeTimeSales($lifeTimeSales)
    {
        return $this->setData('lifetime_sales', $lifeTimeSales);
    }

    /**
     * @inheritdoc
     */
    public function getAverageOrder()
    {
        return $this->getData('average_order');
    }

    /**
     * @inheritdoc
     */
    public function setAverageOrder($averageOrder)
    {
        return $this->setData('average_order', $averageOrder);
    }

    /**
     * @inheritdoc
     */
    public function getOrderCount()
    {
        return $this->getData('orders_count');
    }

    /**
     * @inheritdoc
     */
    public function setOrderCount($orderCount)
    {
        return $this->setData('orders_count', $orderCount);
    }

    /**
     * @inheritdoc
     */
    public function getProductsSold()
    {
        return $this->getData('products_sold');
    }

    /**
     * @inheritdoc
     */
    public function setProductsSold($productsSold)
    {
        return $this->setData('products_sold', $productsSold);
    }

    /**
     * @inheritdoc
     */
    public function getAmountPaid()
    {
        return $this->getData('amount_paid');
    }

    /**
     * @inheritdoc
     */
    public function setAmountPaid($amountPaid)
    {
        return $this->setData('amount_paid', $amountPaid);
    }

    /**
     * @inheritdoc
     */
    public function getAmountBalance()
    {
        return $this->getData('amount_balance');
    }

    /**
     * @inheritdoc
     */
    public function setAmountBalance($amountBalance)
    {
        return $this->setData('amount_balance', $amountBalance);
    }

    /**
     * @inheritdoc
     */
    public function getAmountBalanceTransaction()
    {
        return $this->getData('amount_balance_transaction');
    }

    /**
     * @inheritdoc
     */
    public function setAmountBalanceTransaction($amtBalTransaction)
    {
        return $this->setData('amount_balance_transaction', $amtBalTransaction);
    }

    /**
     * @inheritdoc
     */
    public function getAmountBalanceWithoutFormat()
    {
        return $this->getData('amount_bal_wo_format');
    }

    /**
     * @inheritdoc
     */
    public function setAmountBalanceWithoutFormat($amtBalWoFormat)
    {
        return $this->setData('amount_bal_wo_format', $amtBalWoFormat);
    }

    /**
     * @inheritdoc
     */
    public function getLastApprovedItems()
    {
        return $this->getData('last_approved_items');
    }

    /**
     * @inheritdoc
     */
    public function setLastApprovedItems($lastApprovedItems)
    {
        return $this->setData('last_approved_items', $lastApprovedItems);
    }

    /**
     * @inheritdoc
     */
    public function setRatingAvg($ratingAvg)
    {
        return $this->setData('rating_avg', $ratingAvg);
    }

    /**
     * @inheritdoc
     */
    public function getRatingAvg()
    {
        return $this->getData('rating_avg');
    }

    /**
     * @inheritdoc
     */
    public function setBestSellingItems($bestSellers)
    {
        return $this->setData('best_sellers', $bestSellers);
    }

    /**
     * @inheritdoc
     */
    public function getBestSellingItems()
    {
        return $this->getData('best_sellers');
    }

    /**
     * @inheritdoc
     */
    public function setTransactionSummaryUrl($transactionSummaryUrl)
    {
        return $this->setData('transaction_summary_url', $transactionSummaryUrl);
    }

    /**
     * @inheritdoc
     */
    public function getTransactionSummaryUrl()
    {
        return $this->getData('transaction_summary_url');
    }

    /**
     * @inheritdoc
     */
    public function setApprovedProductUrl($approvedProductUrl)
    {
        return $this->setData('approved_product_url', $approvedProductUrl);
    }

    /**
     * @return string
     */
    public function getApprovedProductUrl()
    {
        return $this->getData('approved_product_url');
    }

    /**
     * @inheritdoc
     */
    public function setDashboardOverview($overViewData)
    {
        return $this->setData('overview', $overViewData);
    }

    /**
     * @inheritdoc
     */
    public function getDashboardOverview()
    {
        return $this->getData('overview');
    }

    /**
     * {@inheritDoc}
     */
    public function setAvailableStores($availableStores)
    {
        return $this->setData(VendorDashboardInterface::AVAILABLE_STORES, $availableStores);
    }

    /**
     * {@inheritDoc}
     */
    public function getAvailableStores()
    {
        return $this->getData(VendorDashboardInterface::AVAILABLE_STORES);
    }

    /**
     * {@inheritDoc}
     */
    public function setCurrentCurrency(string $currency)
    {
        return $this->setData(VendorDashboardInterface::CURRENT_CURRENCY, $currency);
    }

    /**
     * {@inheritDoc}
     */
    public function getCurrentCurrency()
    {
        return $this->getData(VendorDashboardInterface::CURRENT_CURRENCY);
    }

    /**
     * {@inheritDoc}
     */
    public function setCurrentCurrencySymbol(string $symbol)
    {
        return $this->setData(VendorDashboardInterface::CURRENT_CURRENCY_SYMBOL, $symbol);
    }

    /**
     * {@inheritDoc}
     */
    public function getCurrentCurrencySymbol()
    {
        return $this->getData(VendorDashboardInterface::CURRENT_CURRENCY_SYMBOL);
    }

    /**
     * {@inheritDoc}
     */
    public function setPriceFormat(array $priceFormat)
    {
        return $this->setData(VendorDashboardInterface::PRICE_FORMAT, $priceFormat);
    }

    /**
     * {@inheritDoc}
     */
    public function getPriceFormat()
    {
        return $this->getData(VendorDashboardInterface::PRICE_FORMAT);
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
     * @param \Magedelight\Vendor\Api\Data\VendorDashboardExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Magedelight\Vendor\Api\Data\VendorDashboardExtensionInterface $extensionAttributes
    ) {
        return $this->_setExtensionAttributes($extensionAttributes);
    }
}
