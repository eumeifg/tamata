<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Raf\Model\Advocate\Account\Creator;

use Magento\Customer\Api\CustomerRepositoryInterface;

/**
 * Class CustomerInfo
 * @package Aheadworks\Raf\Model\Advocate\Account\Creator
 */
class CustomerInfo
{
    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @param CustomerRepositoryInterface $customerRepository
     */
    public function __construct(
        CustomerRepositoryInterface $customerRepository
    ) {
        $this->customerRepository = $customerRepository;
    }

    /**
     * Retrieve customer email
     *
     * @param $customerId
     * @return string
     */
    public function getCustomerEmail($customerId)
    {
        try {
            $customer = $this->getCustomer($customerId);
            return $customer->getEmail();
        } catch (\Exception $exception) {
            return '';
        }
    }

    /**
     * Get complete customer name from first name and last name
     *
     * @param int $customerId
     * @return string
     */
    public function getCustomerName($customerId)
    {
        try {
            $customer = $this->getCustomer($customerId);
            return $customer->getFirstname() . ' ' . $customer->getLastname();
        } catch (\Exception $exception) {
            return '';
        }
    }

    /**
     * Get customer entity
     *
     * @param $customerId
     * @return \Magento\Customer\Api\Data\CustomerInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getCustomer($customerId)
    {
        return $this->customerRepository->getById($customerId);
    }
}
