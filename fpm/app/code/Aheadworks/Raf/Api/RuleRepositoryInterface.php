<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Raf\Api;

/**
 * Rule CRUD interface
 * @api
 */
interface RuleRepositoryInterface
{
    /**
     * Save rule
     *
     * @param \Aheadworks\Raf\Api\Data\RuleInterface $rule
     * @return \Aheadworks\Raf\Api\Data\RuleInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(\Aheadworks\Raf\Api\Data\RuleInterface $rule);

    /**
     * Retrieve rule by ID
     *
     * @param int $ruleId
     * @return \Aheadworks\Raf\Api\Data\RuleInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function get($ruleId);

    /**
     * Retrieve rules matching the specified criteria
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Aheadworks\Raf\Api\Data\RuleSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);

    /**
     * Delete rule
     *
     * @param \Aheadworks\Raf\Api\Data\RuleInterface $rule
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(\Aheadworks\Raf\Api\Data\RuleInterface $rule);

    /**
     * Delete rule by ID
     *
     * @param int $ruleId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($ruleId);
}
