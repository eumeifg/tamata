<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Raf\Api;

/**
 * Interface GuestQuoteManagementInterface
 * @api
 */
interface GuestQuoteManagementInterface
{
    /**
     * Update referral link in quote
     *
     * @param string $maskedId
     * @param string $referralLink
     * @return bool
     */
    public function updateReferralLink($maskedId, $referralLink);
}
