<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Raf\Api;

/**
 * Interface FriendManagementInterface
 * @api
 */
interface FriendManagementInterface
{
    /**
     * Check if can apply discount
     *
     * @param \Aheadworks\Raf\Api\Data\FriendMetadataInterface $friendMetadata
     * @return bool
     */
    public function canApplyDiscount($friendMetadata);
}
