<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Raf\Plugin\Model\Account;

use Magento\Customer\Model\AccountManagement;
use Aheadworks\Raf\Model\Friend\Quote\GuestSaver;

/**
 * Class AccountManagementPlugin
 * @package Aheadworks\Raf\Plugin\Model\Account
 */
class AccountManagementPlugin
{
    /**
     * @var GuestSaver
     */
    private $guestSaver;

    /**
     * @param GuestSaver $guestSaver
     */
    public function __construct(
        GuestSaver $guestSaver
    ) {
        $this->guestSaver = $guestSaver;
    }

    /**
     * Try to add customer email to guest quote if email is available
     *
     * @param AccountManagement $subject
     * @param bool $result
     * @param string $customerEmail
     * @return bool
     */
    public function afterIsEmailAvailable(AccountManagement $subject, $result, $customerEmail)
    {
        if ($result) {
            $this->guestSaver->addCustomerEmailToGuestQuote($customerEmail);
        }

        return $result;
    }
}
