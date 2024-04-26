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
namespace Magedelight\Commissions\Api\Data;

interface TransactionSummarySearchResultInterface extends \Magento\Framework\Api\SearchResultsInterface
{
    const AMOUNT_BAL = 'amount_balance';

    /**
     * Get transaction summmary list.
     *
     * @return \Magedelight\Commissions\Api\Data\CommissionPaymentInterface[] $items
     */
    public function getItems();

    /**
     * Set transaction summmary list.
     *
     * @param \Magedelight\Commissions\Api\Data\CommissionPaymentInterface[] $items
     * @return $this
     */
    public function setItems(array $items);

    /**
     * Get Amount Balance
     *
     * @return string|float
     */
    public function getAmountBalance();

    /**
     * Set Amount Balance
     *
     * @param string|float $amountBalance
     * @return $this
     */
    public function setAmountBalance($amountBalance);
}
