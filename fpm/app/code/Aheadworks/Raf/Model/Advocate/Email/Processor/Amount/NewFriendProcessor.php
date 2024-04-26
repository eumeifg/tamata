<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Raf\Model\Advocate\Email\Processor\Amount;

/**
 * Class NewFriendProcessor
 *
 * @package Aheadworks\Raf\Model\Advocate\Email\Processor\Amount
 */
class NewFriendProcessor extends AbstractProcessor
{
    /**
     * {@inheritdoc}
     */
    protected function getTemplateId($storeId)
    {
        return $this->config->getNewFriendNotificationTemplate($storeId);
    }
}
