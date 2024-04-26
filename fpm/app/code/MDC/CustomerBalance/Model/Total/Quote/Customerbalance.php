<?php

namespace MDC\CustomerBalance\Model\Total\Quote;

use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Quote\Api\Data\ShippingAssignmentInterface;
use Magento\Quote\Model\Quote\Address\Total;
use Magento\Quote\Model\Quote\Address;

class Customerbalance extends \Magento\CustomerBalance\Model\Total\Quote\Customerbalance
{

	 /**
     * Customer balance data
     *
     * @var \Magento\CustomerBalance\Helper\Data
     */
    protected $_customerBalanceData = null;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\CustomerBalance\Model\BalanceFactory
     */
    protected $_balanceFactory;

    /**
     * @var PriceCurrencyInterface
     */
    protected $priceCurrency;

    /**
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\CustomerBalance\Model\BalanceFactory $balanceFactory
     * @param \Magento\CustomerBalance\Helper\Data $customerBalanceData
     * @param PriceCurrencyInterface $priceCurrency
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\CustomerBalance\Model\BalanceFactory $balanceFactory,
        \Magento\CustomerBalance\Helper\Data $customerBalanceData,
        PriceCurrencyInterface $priceCurrency,
        \Magento\Framework\Registry $registry
    ) {

    	parent::__construct($storeManager,$balanceFactory,$customerBalanceData,$priceCurrency);

        $this->priceCurrency = $priceCurrency;
        $this->_storeManager = $storeManager;
        $this->_balanceFactory = $balanceFactory;
        $this->_customerBalanceData = $customerBalanceData;
        $this->registry = $registry;
        $this->setCode('customerbalance');
    }

    /**
     * Collect customer balance totals for specified address
     *
     * @param \Magento\Quote\Model\Quote $quote
     * @param ShippingAssignmentInterface $shippingAssignment
     * @param Total $total
     * @return Customerbalance
     */
    public function collect(
        \Magento\Quote\Model\Quote $quote,
        \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment,
        \Magento\Quote\Model\Quote\Address\Total $total
    ) {
        if (!$this->_customerBalanceData->isEnabled()) {
            return $this;
        }

        if ($shippingAssignment->getShipping()->getAddress()->getAddressType() == Address::TYPE_SHIPPING
            && $quote->isVirtual()
        ) {
            return $this;
        }

        $baseTotalUsed = $totalUsed = $baseUsed = $used = 0;

        $baseBalance = $balance = 0;
        if ($quote->getCustomer()->getId()) {
            if ($quote->getUseCustomerBalance()) {

            $customerInputBalance  = (float)$quote->getCustomerCustomBalanceAmountUsed();

                $store = $this->_storeManager->getStore($quote->getStoreId());

                if($customerInputBalance > 0){
            		$baseBalance = $customerInputBalance;
            	}else{
            		$baseBalance = $this->_balanceFactory->create()->setCustomer(
                    $quote->getCustomer()
		                )->setCustomerId(
		                    $quote->getCustomer()->getId()
		                )->setWebsiteId(
		                    $store->getWebsiteId()
		                )->loadByCustomer()->getAmount();
            	}

                $balance = $this->priceCurrency->convert($baseBalance, $quote->getStore());
            }
        }

        $baseAmountLeft = $baseBalance - $quote->getBaseCustomerBalAmountUsed();
        $amountLeft = $balance - $quote->getCustomerBalanceAmountUsed();

        if ($baseAmountLeft >= $total->getBaseGrandTotal()) {
            $baseUsed = $total->getBaseGrandTotal();
            $used = $total->getGrandTotal();

            $total->setBaseGrandTotal(0);
            $total->setGrandTotal(0);
        } else {
            $baseUsed = $baseAmountLeft;
            $used = $amountLeft;

            $total->setBaseGrandTotal($total->getBaseGrandTotal() - $baseAmountLeft);
            $total->setGrandTotal($total->getGrandTotal() - $amountLeft);
        }

        $baseTotalUsed = $quote->getBaseCustomerBalAmountUsed() + $baseUsed;
        $totalUsed = $quote->getCustomerBalanceAmountUsed() + $used;

        $quote->setBaseCustomerBalAmountUsed($baseTotalUsed);
        $quote->setCustomerBalanceAmountUsed($totalUsed);

        $total->setBaseCustomerBalanceAmount($baseUsed);
        $total->setCustomerBalanceAmount($used);

        return $this;
    }

    /**
     * Return shopping cart total row items
     *
     * @param \Magento\Quote\Model\Quote $quote
     * @param Total $total
     * @return array|null
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function fetch(\Magento\Quote\Model\Quote $quote, \Magento\Quote\Model\Quote\Address\Total $total)
    {
        if ($this->_customerBalanceData->isEnabled() && $quote->getCustomer()->getId()) {

            $balancModel = $this->_balanceFactory->create()->setCustomerId($quote->getCustomer()->getId())->loadByCustomer();

            $remianingAmount = ( (float)$balancModel->getAmount() - (float) $total->getCustomerBalanceAmount() );

            // return [
            //     'code' => $this->getCode(),
            //     'title' => __('Store Credit'),
            //     'value' => -$total->getCustomerBalanceAmount()
            // ];

            $customerBalanceUpdate = array(
                array('code' => $this->getCode(),
                'title' => __('Store Credit'),
                'value' => -$total->getCustomerBalanceAmount()),

                array('code' => "store_credit",
                'title' => __('Remaining Credit'),
                'value' => $remianingAmount)
                );

            return $customerBalanceUpdate;
        }

        return null;
    }
}
