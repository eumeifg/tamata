<?php

namespace CAT\Bnpl\Api;

interface BnplCustomerUpdateInterface
{
    /**
     * @api
     *
     * @param string $email
     * @param int $isBnpl
     * @param string $bnplCustomerId
     * @return Data\BnplCustomerUpdateDataInterface
     */
    public function updateCustomer($email, $isBnpl, $bnplCustomerId);
}
