<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Raf\Api;

/**
 * Transaction CRUD interface
 * @api
 */
interface TransactionRepositoryInterface
{
    /**
     * Save transaction data
     *
     * @param \Aheadworks\Raf\Api\Data\TransactionInterface $transaction
     * @return \Aheadworks\Raf\Api\Data\TransactionInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(\Aheadworks\Raf\Api\Data\TransactionInterface $transaction);

    /**
     * Retrieve transaction data by id
     *
     * @param  int $transactionId
     * @return \Aheadworks\Raf\Api\Data\TransactionInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function get($transactionId);

    /**
     * Retrieve transactions matching the specified criteria
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Aheadworks\Raf\Api\Data\TransactionSearchResultsInterface;
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);
}
