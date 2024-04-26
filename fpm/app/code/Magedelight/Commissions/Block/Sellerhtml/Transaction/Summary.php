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
namespace Magedelight\Commissions\Block\Sellerhtml\Transaction;

class Summary extends \Magedelight\Commissions\Block\Sellerhtml\AbstractPayment
{
    protected $_amountBalance = [];

    public function getAmountBalance()
    {
        if (empty($this->_amountBalance)) {
            $this->_amountBalance = $this->getPaymentCollection()
                ->calculateAmountBalanceForVendor()->getFirstItem()->convertToArray();
        }
        return $this->_amountBalance['amount_balance'];
    }

    public function getPaymentCollection()
    {
        $collection = $this->_paymentReportCollection->create();
        return $collection;
    }

    public function getFormatPrice()
    {
        $formatPrice = $this->priceHelper->currency(floatval($this->getAmountBalance()));
        return $formatPrice;
    }
}
