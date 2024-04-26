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
namespace Magedelight\Commissions\Model\ResourceModel\Reports\Commission\Payment;

use Magedelight\Commissions\Model\Source\PaymentStatus as VendorPaymentStatus;

/**
 * Reports commission payment collection
 *
 * @author Rocket Bazaar Core Team
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Collection extends \Magedelight\Commissions\Model\ResourceModel\Commission\Payment\Collection
{

    /**
     * @var \Magedelight\Backend\Model\Auth\Session
     */
    protected $authSession;

    /**
     *
     * @param \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magedelight\Backend\Model\Auth\Session $authSession
     * @param type $connection
     * @param \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource
     */
    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magedelight\Backend\Model\Auth\Session $authSession,
        \Magento\Authorization\Model\UserContextInterface $userContext,
        $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
    ) {
        $this->authSession = $authSession;
        $this->userContext = $userContext;
        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $storeManager,
            $connection,
            $resource
        );
    }

    public function calculateAmountPaidToVendor($vendorId = null)
    {
        if (empty($vendorId)) {
            /** checked to make sure vendor id is not null when accessed from API */
            if ($this->authSession->getUser() == null) {
                $vendorId = $this->userContext->getUserId();
            } else {
                $vendorId = $this->authSession->getUser()->getVendorId();
            }
        }

        $this->setMainTable('md_vendor_commission_payment');
        $this->removeAllFieldsFromSelect();

        $this->getSelect()->columns(
            ['amount_paid' => "SUM(main_table.total_paid)"]
        )->where(
            'main_table.vendor_id = ?',
            $vendorId
        )->where(
            'main_table.status = ?',
            VendorPaymentStatus::PAYMENT_STATUS_PAID
        );
        return $this;
    }

    public function calculateAmountBalanceForVendor($vendorId = null)
    {
        if (empty($vendorId)) {
            /** checked to make sure vendor id is not null when accessed from API */
            if ($this->authSession->getUser() == null) {
                $vendorId = $this->userContext->getUserId();
            } else {
                $vendorId = $this->authSession->getUser()->getVendorId();
            }
        }

        $this->setMainTable('md_vendor_commission_payment');
        $this->removeAllFieldsFromSelect();

        $this->getSelect()->columns(
            ['amount_balance' => "SUM(main_table.total_amount)"]
        )->where(
            'main_table.vendor_id = ?',
            $vendorId
        )->where(
            'main_table.status != ?',
            VendorPaymentStatus::PAYMENT_STATUS_PAID
        );
        return $this;
    }

    public function calculateTransactionNotPaid()
    {
        if (empty($vendorId)) {
            /** checked to make sure vendor id is not null when accessed from API */
            if ($this->authSession->getUser() == null) {
                $vendorId = $this->userContext->getUserId();
            } else {
                $vendorId = $this->authSession->getUser()->getVendorId();
            }
        }
        $this->setMainTable('md_vendor_commission_payment');
        $this->removeAllFieldsFromSelect();

        $this->getSelect()
        ->where(
            'main_table.vendor_id = ?',
            $vendorId
        )->where(
            'main_table.status != ?',
            VendorPaymentStatus::PAYMENT_STATUS_PAID
        );
        return $this;
    }
}
