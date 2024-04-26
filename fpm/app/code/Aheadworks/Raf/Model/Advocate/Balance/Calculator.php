<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Raf\Model\Advocate\Balance;

/**
 * Class Calculator
 *
 * @package Aheadworks\Raf\Model\Advocate\Balance
 */
class Calculator
{
    /**
     * Calculate new cumulative amount
     *
     * @param float $currentCumulativeAmount
     * @param float $amount
     * @return float
     */
    public function calculateNewCumulativeAmount($currentCumulativeAmount, $amount)
    {
        $newCumulativeAmount = $currentCumulativeAmount + $amount;

        return $newCumulativeAmount > 0 ? $newCumulativeAmount : 0;
    }
}
