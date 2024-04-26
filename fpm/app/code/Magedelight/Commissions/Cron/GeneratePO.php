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
namespace Magedelight\Commissions\Cron;

/**
 * @author Rocket Bazaar Core Team
 * Created at 12 May, 2016 05:05:27 PM
 */
class GeneratePO
{
    /**
     * @var \Magedelight\Commissions\Model\Commission\Payment
     */
    protected $_commissionPayment;

    /**
     *
     * @param \Magedelight\Commissions\Model\Commission\PaymentFactory $commissionPaymentFactory
     */
    public function __construct(\Magedelight\Commissions\Model\Commission\PaymentFactory $commissionPaymentFactory)
    {
        $this->_commissionPayment = $commissionPaymentFactory->create();
    }

    /**
     * Add products to changes list with price which depends on date
     *
     * @return void
     */
    public function execute()
    {
        $this->_commissionPayment->processPayOrders();
    }
}
