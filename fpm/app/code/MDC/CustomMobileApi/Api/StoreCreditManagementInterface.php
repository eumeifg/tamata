<?php
declare(strict_types=1);
/**
 * Copyright © Magedelight, All rights reserved.
 */
namespace MDC\CustomMobileApi\Api;

/**
 * Interface for apply store credit 
 * to the quote for logged in users
 * @api
 */
interface StoreCreditManagementInterface
{
    /**
     * Returns quote totals data for a specified cart.
     *
     * @param int $cartId The cart ID.
     * @return \Magento\Quote\Api\Data\TotalsInterface Quote totals data.
     * @throws \Magento\Framework\Exception\NoSuchEntityException The specified cart does not exist.
     */
    public function addStoreCreditAmount(
        $cartId
    );

    /**
     * Returns quote totals data for a specified cart.
     *
     * @param int $cartId The cart ID.
     * @return \Magento\Quote\Api\Data\TotalsInterface Quote totals data.
     * @throws \Magento\Framework\Exception\NoSuchEntityException The specified cart does not exist.
     */

    public function removeStoreCreditAmount(
        $cartId
    );

    /**
     * Returns storecredit balance.
     *
     * @param int $customerId The Customer ID.
     * @param int $cartId The Cart ID.
    * @return \Magento\Quote\Api\Data\TotalsInterface Quote totals data.
     * @throws \Magento\Framework\Exception\NoSuchEntityException The specified cart does not exist.
    */

    public function displayStoreCreditAmount(
        $customerId,$cartId
    );

    /**
     * Returns search Term.
     *
     * @param string $searchTerm .
     * @return data.
     * @throws \Magento\Framework\Exception\NoSuchEntityException The specified cart does not exist.
    */
    public function searchTermData();
}
