<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Raf\Model\Rule\Discount\Customer\Advocate;

use Aheadworks\Raf\Api\AdvocateManagementInterface;
use Magento\Framework\Validator\AbstractValidator;
use Magento\Quote\Model\Quote;

/**
 * Class Validator
 *
 * @package Aheadworks\Raf\Model\Rule\Discount\Customer\Advocate
 */
class Validator extends AbstractValidator
{
    /**
     * @var AdvocateManagementInterface
     */
    private $advocateManagement;

    /**
     * @param AdvocateManagementInterface $advocateManagement
     */
    public function __construct(
        AdvocateManagementInterface $advocateManagement
    ) {
        $this->advocateManagement = $advocateManagement;
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

        $customerId = $quote->getCustomerId();
        $websiteId = $quote->getStore()->getWebsiteId();
        if (!$customerId
            || ($customerId
                && (!$this->advocateManagement->isParticipantOfReferralProgram($customerId, $websiteId)
                    || !$this->advocateManagement->canUseReferralProgramAndSpend($customerId, $websiteId)
                )
            )
        ) {
            $this->_addMessages(['Can\'t apply rule. Current customer is not advocate.']);
        }

        return empty($this->getMessages());
    }
}
