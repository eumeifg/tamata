<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Raf\Model\Advocate\Email\Processor\Amount\VariableProcessor;

use Aheadworks\Raf\Api\Data\AdvocateSummaryInterface;
use Aheadworks\Raf\Model\Advocate\Email\Processor\VariableProcessor\VariableProcessorInterface;
use Aheadworks\Raf\Model\Config;
use Aheadworks\Raf\Model\Source\Customer\Advocate\Email\BaseAmountVariables;

/**
 * Class ExpiredInDays
 *
 * @package Aheadworks\Raf\Model\Advocate\Email\Processor\Amount\VariableProcessor
 */
class ExpiredInDays implements VariableProcessorInterface
{
    /**
     * {@inheritdoc}
     */
    public function prepareVariables($variables)
    {
        /** @var AdvocateSummaryInterface $advocateSummary */
        $advocateSummary = $variables[BaseAmountVariables::ADVOCATE_SUMMARY];

        $today = new \DateTime('today', new \DateTimeZone('UTC'));
        $expiredDate = new \DateTime($advocateSummary->getExpirationDate(), new \DateTimeZone('UTC'));
        $expiredInDays = $today->diff($expiredDate);
        $expiredInDays = (int)$expiredInDays->format('%a');

        if ($expiredInDays > 0) {
            $variables[BaseAmountVariables::EXPIRED_IN_DAYS] = $expiredInDays;
        }

        return $variables;
    }
}
