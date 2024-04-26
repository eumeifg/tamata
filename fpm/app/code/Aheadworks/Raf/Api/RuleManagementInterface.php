<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Raf\Api;

/**
 * Interface RuleManagementInterface
 * @api
 */
interface RuleManagementInterface
{
    /**
     * Retrieve active rule on website
     *
     * @param int $websiteId
     * @return bool|\Aheadworks\Raf\Api\Data\RuleInterface
     */
    public function getActiveRule($websiteId);
}
