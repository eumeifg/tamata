<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Raf\Model\Transaction\Processor\Resolver;

use Aheadworks\Raf\Model\Config;
use Magento\Framework\Stdlib\DateTime as StdlibDateTime;

/**
 * Class HoldingPeriodExpiration
 *
 * @package Aheadworks\Raf\Model\Transaction\Processor\Resolver
 */
class HoldingPeriodExpiration
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var StdlibDateTime
     */
    private $dateTime;

    /**
     * @param Config $config
     * @param StdlibDateTime $dateTime
     */
    public function __construct(
        Config $config,
        StdlibDateTime $dateTime
    ) {
        $this->config = $config;
        $this->dateTime = $dateTime;
    }

    /**
     * Resolve expiration date
     *
     * @param int $websiteId
     * @return string|null
     * @throws \Exception
     */
    public function resolveExpirationDate($websiteId)
    {
        $holdingPeriodInDays = $this->config->getNumberOfDaysForHoldingPeriod($websiteId);
        $expirationDate = null;
        if ($holdingPeriodInDays > 0) {
            $expirationDate = new \DateTime('now');
            $expirationDate->add(new \DateInterval('P' . $holdingPeriodInDays . 'D'));
            $expirationDate = $this->dateTime->formatDate($expirationDate);
        }

        return $expirationDate;
    }
}
