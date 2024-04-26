<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Raf\Model\Advocate\Generator;

use Magento\Framework\Math\Random;

/**
 * Class ReferralLink
 *
 * @package Aheadworks\Raf\Model\Advocate\Generator
 */
class ReferralLink
{
    /**
     * Generate referral link
     *
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function generate()
    {
        return strtoupper(uniqid(dechex(Random::getRandomNumber())));
    }
}
