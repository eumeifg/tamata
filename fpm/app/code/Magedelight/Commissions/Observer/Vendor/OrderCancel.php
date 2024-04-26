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
namespace Magedelight\Commissions\Observer\Vendor;

use Magento\Framework\Event\ObserverInterface;

/**
 * @author Rocket Bazaar Core Team
 * Created at 18 June, 2016 11:48:10 PM
 */
class OrderCancel implements ObserverInterface
{
    /**
     * @var \Magedelight\Commissions\Model\Commission\Payment
     */
    protected $_commissionPayment;

    public function __construct(\Magedelight\Commissions\Model\Commission\PaymentFactory $commissionPaymentFactory)
    {
        $this->_commissionPayment = $commissionPaymentFactory->create();
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $vendorOrderIds = $observer->getVendorOrderIds();
        if (!empty($vendorOrderIds)) {
            $this->_commissionPayment->processCancelledPayOrders($vendorOrderIds);
        }
    }
}
