<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Raf\Model\Service;

use Aheadworks\Raf\Api\Data\RuleInterface;
use Aheadworks\Raf\Api\RuleManagementInterface;
use Aheadworks\Raf\Api\RuleRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;

/**
 * Class RuleService
 *
 * @package Aheadworks\Raf\Model\Service
 */
class RuleService implements RuleManagementInterface
{
    /**
     * @var RuleRepositoryInterface
     */
    private $ruleRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @param RuleRepositoryInterface $ruleRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        RuleRepositoryInterface $ruleRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->ruleRepository = $ruleRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * {@inheritdoc}
     */
    public function getActiveRule($websiteId)
    {
        $this->searchCriteriaBuilder->addFilter(RuleInterface::WEBSITE_IDS, $websiteId);
        $rules = $this->ruleRepository->getList($this->searchCriteriaBuilder->create())->getItems();

        return empty($rules) ? false : reset($rules);
    }
}
