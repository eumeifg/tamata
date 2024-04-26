<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Raf\Model\Advocate\Email\Processor\Amount;

use Aheadworks\Raf\Api\Data\AdvocateSummaryInterface;
use Aheadworks\Raf\Model\Email\EmailMetadataInterface;

/**
 * Interface AmountProcessorInterface
 *
 * @package Aheadworks\Raf\Model\Advocate\Email\Processor\Amount
 */
interface AmountProcessorInterface
{
    /**
     * Process amount email data
     *
     * @param AdvocateSummaryInterface $advocateSummary
     * @param float $amount
     * @param string $amountType
     * @param int $storeId
     * @return EmailMetadataInterface
     */
    public function process($advocateSummary, $amount, $amountType, $storeId);
}
