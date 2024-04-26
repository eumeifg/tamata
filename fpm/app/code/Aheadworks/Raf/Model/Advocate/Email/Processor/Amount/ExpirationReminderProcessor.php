<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Raf\Model\Advocate\Email\Processor\Amount;

/**
 * Class ExpirationReminderProcessor
 *
 * @package Aheadworks\Raf\Model\Advocate\Email\Processor\Amount
 */
class ExpirationReminderProcessor extends AbstractProcessor
{
    /**
     * {@inheritdoc}
     */
    protected function getTemplateId($storeId)
    {
        return $this->config->getExpirationReminderTemplate($storeId);
    }
}
