<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Raf\Model\Rule\Validator;

use Magento\Framework\Validator\AbstractValidator;

/**
 * Class Composite
 * @package Aheadworks\Raf\Model\Rule\Validator
 */
class Composite extends AbstractValidator
{
    /**
     * @var AbstractValidator[]
     */
    private $validators;

    /**
     * @param AbstractValidator[] $validators
     */
    public function __construct(array $validators = [])
    {
        $this->validators = $validators;
    }

    /**
     * {@inheritdoc}
     */
    public function isValid($rule)
    {
        foreach ($this->validators as $validator) {
            if (!$validator->isValid($rule)) {
                $this->_addMessages($validator->getMessages());
            }
        }
        return empty($this->getMessages());
    }
}
