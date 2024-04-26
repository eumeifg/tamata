<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Raf\Model\Advocate\Email\Processor\Amount;

/**
 * Class ExpirationProcessor
 *
 * @package Aheadworks\Raf\Model\Advocate\Email\Processor\Amount
 */
class ExpirationProcessor extends AbstractProcessor
{
    /**
     * {@inheritdoc}
     */
    protected function getTemplateId($storeId)
    {
        return $this->config->getExpirationTemplate($storeId);
    }
}
