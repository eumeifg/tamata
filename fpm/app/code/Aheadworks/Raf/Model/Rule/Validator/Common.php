<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Raf\Model\Rule\Validator;

use Aheadworks\Raf\Api\Data\RuleInterface;
use Magento\Framework\Validator\AbstractValidator;

/**
 * Class Common
 * @package Aheadworks\Raf\Model\Rule
 */
class Common extends AbstractValidator
{
    /**
     * Returns true if and only if entity meets the validation requirements
     *
     * @param RuleInterface $rule
     * @return bool
     * @throws \Exception
     */
    public function isValid($rule)
    {
        $this->_clearMessages();

        if (empty($rule->getName())) {
            $this->_addMessages(['Rule name is required.']);
        }

        if (empty($rule->getAdvocateOff())) {
            $this->_addMessages(['Advocate off amount is required.']);
        }

        if (!is_numeric($rule->getAdvocateOff())) {
            $this->_addMessages(['Advocate off amount is not a valid number.']);
        }

        if (is_numeric($rule->getAdvocateOff()) && ($rule->getAdvocateOff() < 0)) {
            $this->_addMessages(['Advocate off amount must be greater than 0.']);
        }

        if (empty($rule->getFriendOff())) {
            $this->_addMessages(['Friend off amount is required.']);
        }

        if (!is_numeric($rule->getFriendOff())) {
            $this->_addMessages(['Friend off amount is not a valid number.']);
        }

        if (is_numeric($rule->getFriendOff()) && ($rule->getFriendOff() < 0)) {
            $this->_addMessages(['Friend off amount must be greater than 0.']);
        }

        return empty($this->getMessages());
    }
}
