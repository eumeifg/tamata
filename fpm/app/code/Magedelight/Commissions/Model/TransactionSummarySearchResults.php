<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magedelight\Commissions\Model;

use Magedelight\Commissions\Api\Data\TransactionSummarySearchResultInterface;
use Magento\Framework\Api\SearchResults;

class TransactionSummarySearchResults extends SearchResults implements TransactionSummarySearchResultInterface
{
    /**
     * Get Amount Balance
     *
     * @return string|float
     */
    public function getAmountBalance()
    {
        return $this->_get(TransactionSummarySearchResultInterface::AMOUNT_BAL);
    }

    /**
     * Set Amount Balance
     *
     * @param string|float $amountBalance
     * @return $this
     */
    public function setAmountBalance($amountBalance)
    {
        return $this->setData(TransactionSummarySearchResultInterface::AMOUNT_BAL, $amountBalance);
    }
}
