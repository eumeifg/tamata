<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Raf\Model\Advocate\Account\Rule;

use Aheadworks\Raf\Model\Advocate\Account\Rule\Viewer\ActiveRuleResolver;
use Aheadworks\Raf\Model\Advocate\Account\Rule\Viewer\PriceFormatResolver;

/**
 * Class Viewer
 * @package Aheadworks\Raf\Model\Advocate\Account\Rule
 */
class Viewer
{
    /**
     * @var PriceFormatResolver
     */
    private $priceFormatResolver;

    /**
     * @var ActiveRuleResolver
     */
    private $ruleResolver;

    /**
     * @param ActiveRuleResolver $ruleResolver
     * @param PriceFormatResolver $priceFormatResolver
     */
    public function __construct(
        ActiveRuleResolver $ruleResolver,
        PriceFormatResolver $priceFormatResolver
    ) {
        $this->ruleResolver = $ruleResolver;
        $this->priceFormatResolver = $priceFormatResolver;
    }

    /**
     * Retrieve advocate off in rule
     *
     * @param int $storeId
     * @return string
     */
    public function getAdvocateOffFormatted($storeId)
    {
        $rule = $this->ruleResolver->getRule($storeId);
        return $this->priceFormatResolver->resolve(
            $rule->getAdvocateOff(),
            $rule->getAdvocateOffType(),
            $storeId
        );
    }

    /**
     * Retrieve friend off in rule
     *
     * @param int $storeId
     * @return string
     */
    public function getFriendOffFormatted($storeId)
    {
        $rule = $this->ruleResolver->getRule($storeId);
        return $this->priceFormatResolver->resolve(
            $rule->getFriendOff(),
            $rule->getFriendOffType(),
            $storeId
        );
    }

    /**
     * Check if registration is required
     *
     * @param $storeId
     * @return bool
     */
    public function checkIfRegistrationIsRequired($storeId)
    {
        $rule = $this->ruleResolver->getRule($storeId);
        return (bool)$rule->isRegistrationRequired();
    }
}
