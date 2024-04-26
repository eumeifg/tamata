<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Raf\Api;

/**
 * Interface AdvocateExpirationManagementInterface
 * @api
 */
interface AdvocateExpirationManagementInterface
{
    /**
     * Expire balance
     *
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function expireBalance();

    /**
     * Send expiration reminder
     *
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function sendExpirationReminder();
}
