<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Raf\Model\Service;

use Aheadworks\Raf\Api\FriendManagementInterface;
use Aheadworks\Raf\Model\Friend\Checker;

/**
 * Class FriendService
 *
 * @package Aheadworks\Raf\Model\Service
 */
class FriendService implements FriendManagementInterface
{
    /**
     * @var Checker
     */
    private $checker;

    /**
     * @param Checker $checker
     */
    public function __construct(
        Checker $checker
    ) {
        $this->checker = $checker;
    }

    /**
     * {@inheritdoc}
     */
    public function canApplyDiscount($friendMetadata)
    {
        return $this->checker->canApplyDiscount($friendMetadata);
    }
}
