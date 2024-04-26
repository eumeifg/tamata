<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Raf\Api;

/**
 * Interface QuoteManagementInterface
 *
 * @package Aheadworks\Raf\Api
 */
interface QuoteManagementInterface
{
    /**
     * Update referral link in quote
     *
     * @param int $quoteId
     * @param string $referralLink
     * @return bool
     */
    public function updateReferralLink($quoteId, $referralLink);
}
