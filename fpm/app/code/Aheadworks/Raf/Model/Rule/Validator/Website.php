<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Raf\Model\Rule\Validator;

use Aheadworks\Raf\Api\Data\RuleInterface;
use Aheadworks\Raf\Model\Service\RuleService;
use Aheadworks\Raf\Model\Source\Rule\Status as RuleStatus;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Validator\AbstractValidator;

/**
 * Class Website
 * @package Aheadworks\Raf\Model\ResourceModel\Rule\Validator
 */
class Website extends AbstractValidator
{
    /**
     * @var RuleService
     */
    private $ruleService;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param RuleService $ruleService
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        RuleService $ruleService,
        StoreManagerInterface $storeManager
    ) {
        $this->ruleService = $ruleService;
        $this->storeManager = $storeManager;
    }

    /**
     * Check whether website is already used in another rule
     *
     * @param RuleInterface $rule
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function isValid($rule)
    {
        $selectedWebsiteIds = $rule->getWebsiteIds();
        foreach ($selectedWebsiteIds as $websiteId) {
            $activeRule = $this->ruleService->getActiveRule($websiteId);
            if ($activeRule
                && $rule->getStatus() == RuleStatus::ENABLED
                && $rule->getId() != $activeRule->getId()
            ) {
                $ruleId = $activeRule->getId();
                $websiteName = $this->storeManager->getWebsite($websiteId)->getName();
                $message = __('Can not enable the rule, because another rule (ID: #%1) '
                . 'is already enabled for website <%2>. '
                . 'Only one rule can be enabled for earning per website.', $ruleId, $websiteName);
                $this->_addMessages([$message]);
            }
        }
        return empty($this->getMessages());
    }
}
