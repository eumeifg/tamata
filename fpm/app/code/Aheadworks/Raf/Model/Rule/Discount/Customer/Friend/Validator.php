<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Raf\Model\Rule\Discount\Customer\Friend;

use Aheadworks\Raf\Api\AdvocateManagementInterface;
use Aheadworks\Raf\Api\Data\RuleInterface;
use Magento\Framework\Validator\AbstractValidator;
use Magento\Quote\Model\Quote;
use Aheadworks\Raf\Api\FriendManagementInterface;
use Aheadworks\Raf\Model\Metadata\Friend\Builder as FriendMetadataBuilder;

/**
 * Class Validator
 *
 * @package Aheadworks\Raf\Model\Rule\Discount\Customer\Friend
 */
class Validator extends AbstractValidator
{
    /**
     * @var AdvocateManagementInterface
     */
    private $advocateManagement;

    /**
     * @var FriendManagementInterface
     */
    private $friendManagement;

    /**
     * @var FriendMetadataBuilder
     */
    private $friendMetadataBuilder;

    /**
     * @param FriendManagementInterface $friendManagement
     * @param FriendMetadataBuilder $friendMetadataBuilder
     * @param AdvocateManagementInterface $advocateManagement
     */
    public function __construct(
        AdvocateManagementInterface $advocateManagement,
        FriendManagementInterface $friendManagement,
        FriendMetadataBuilder $friendMetadataBuilder
    ) {
        $this->advocateManagement = $advocateManagement;
        $this->friendManagement = $friendManagement;
        $this->friendMetadataBuilder = $friendMetadataBuilder;
    }

    /**
     * Returns true if and only if ticket entity meets the validation requirements
     *
     * @param Quote $quote
     * @return bool
     */
    public function isValid($quote)
    {
        $this->_clearMessages();

        /** @var RuleInterface $rule */
        $rule = $quote->getAwRafRuleToApply();
        $customerId = $quote->getCustomerId();
        $websiteId = $quote->getStore()->getWebsiteId();
        if ($quote->getAwRafReferralLink()) {
            $isReferralLinkBelongsToAdvocate = $this->advocateManagement->isReferralLinkBelongsToAdvocate(
                $quote->getAwRafReferralLink(),
                $customerId,
                $websiteId
            );
            if ($customerId && $isReferralLinkBelongsToAdvocate) {
                $this->_addMessages(['Can\'t apply rule. Customer is advocate.']);
            }
            if ($rule->isRegistrationRequired() && !$quote->getCustomerId()) {
                $this->_addMessages(['Can\'t apply rule. Customer is not authorized.']);
            }
            if (!($rule->getFriendOff() > 0)) {
                $this->_addMessages(['Can\'t apply rule. Discount is not set.']);
            }
            if (!$this->friendManagement->canApplyDiscount($this->friendMetadataBuilder->build($quote))) {
                $this->_addMessages(['Can\'t apply rule to current invited friend.']);
            }
        } else {
            $this->_addMessages(['Can\'t apply rule to current customer.']);
        }

        return empty($this->getMessages());
    }
}
