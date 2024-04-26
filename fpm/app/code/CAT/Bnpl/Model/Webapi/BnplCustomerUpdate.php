<?php

namespace CAT\Bnpl\Model\Webapi;

use CAT\Bnpl\Api\Data\BnplCustomerUpdateDataInterface;
use CAT\Bnpl\Api\Data\BnplCustomerUpdateDataInterfaceFactory;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\LocalizedException;


class BnplCustomerUpdate implements \CAT\Bnpl\Api\BnplCustomerUpdateInterface
{
    /**
     * @var BnplCustomerUpdateDataInterfaceFactory
     */
    protected $bnplCustomerUpdateDataInterfaceFactory;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @param BnplCustomerUpdateDataInterfaceFactory $bnplCustomerUpdateDataInterfaceFactory
     * @param CustomerRepositoryInterface $customerRepository
     */
    public function __construct(
        BnplCustomerUpdateDataInterfaceFactory $bnplCustomerUpdateDataInterfaceFactory,
        CustomerRepositoryInterface $customerRepository
    ) {
        $this->bnplCustomerUpdateDataInterfaceFactory = $bnplCustomerUpdateDataInterfaceFactory;
        $this->customerRepository = $customerRepository;
    }

    /**
     * @param $email
     * @param $isBnpl
     * @param $bnplCustomerId
     * @return BnplCustomerUpdateDataInterface
     * @throws LocalizedException
     */
    public function updateCustomer($email, $isBnpl, $bnplCustomerId)
    {
        $status = false;
        $customerId = $this->getCustomerIdByEmail($email);

        if ($customerId) {
            $customer = $this->getCustomerById($customerId);
            if ($customer) {
                try {
                    $_bnplCustomerId = null;
                    $status = true;
                    $message = __('Nothing to update');
                    $_isBnpl = null;
                    if ($bnpl = $customer->getCustomAttribute('is_bnpl')) {
                        $_isBnpl = $bnpl->getValue();
                    }
                    if ($_isBnpl != $isBnpl) {
                        $customer->setCustomAttribute('is_bnpl', $isBnpl);
                        $this->customerRepository->save($customer);
                        $message = __('Customer has been updated.');
                    }
                    if ($bnplCustId = $customer->getCustomAttribute('bnpl_customer_id')) {
                        $_bnplCustomerId = $bnplCustId->getValue();
                    }
                    if ($_bnplCustomerId != $bnplCustomerId) {
                        $customer->setCustomAttribute('bnpl_customer_id', $bnplCustomerId);
                        $this->customerRepository->save($customer);
                        $message = __('Customer has been updated.');
                    }
                } catch (LocalizedException $exception) {
                    $message = __($exception->getMessage());
                }
            } else {
                $message = __('Customer object not found.');
            }
        } else {
            $message = __('No such customer available');
        }
        $dataFactory = $this->bnplCustomerUpdateDataInterfaceFactory->create();
        $dataFactory->setMessage($message);
        $dataFactory->setStatus($status);
        return $dataFactory;
    }

    /**
     * @param string $email
     * @return int|null
     * @throws LocalizedException
     */
    public function getCustomerIdByEmail(string $email)
    {
        $customerId = null;
        try {
            $customerData = $this->customerRepository->get($email);
            $customerId = (int)$customerData->getId();
        }catch (NoSuchEntityException $noSuchEntityException){
        }
        return $customerId;
    }

    /**
     * @param int $customerId
     * @return CustomerInterface|null
     */
    private function getCustomerById(int $customerId): ? CustomerInterface
    {
        try {
            $customer = $this->customerRepository->getById($customerId);
        } catch (LocalizedException $exception) {
            $customer = null;
        }

        return $customer;
    }
}
