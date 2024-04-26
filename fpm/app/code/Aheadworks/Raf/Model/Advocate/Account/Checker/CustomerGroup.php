<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Raf\Model\Advocate\Account\Checker;

use Aheadworks\Raf\Model\Config;
use Magento\Customer\Api\CustomerRepositoryInterface;

/**
 * Class CustomerGroup
 * @package Aheadworks\Raf\Model\Advocate\Account\Checker
 */
class CustomerGroup
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @param Config $config
     * @param CustomerRepositoryInterface $customerRepository
     */
    public function __construct(
        Config $config,
        CustomerRepositoryInterface $customerRepository
    ) {
        $this->config = $config;
        $this->customerRepository = $customerRepository;
    }

    /**
     * Check if customer is in referral program group
     *
     * @param $customerId
     * @param $websiteId
     * @return bool
     */
    public function isCustomerInReferralProgramGroup($customerId, $websiteId)
    {
        $referralProgramGroups = explode(',', $this->config->getCustomerGroupsToJoinReferralProgram($websiteId));
        try {
            $customer = $this->customerRepository->getById($customerId);
            return (is_array($referralProgramGroups) && in_array($customer->getGroupId(), $referralProgramGroups));
        } catch (\Exception $e) {
        }
        return false;
    }
}
