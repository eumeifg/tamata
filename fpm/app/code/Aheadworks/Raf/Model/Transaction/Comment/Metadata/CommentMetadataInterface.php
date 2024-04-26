<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Raf\Model\Transaction\Comment\Metadata;

/**
 * Interface ActionMetadataInterface
 *
 * @package Aheadworks\Raf\Model\Transaction\Comment\Metadata
 */
interface CommentMetadataInterface
{
    /**#@+
     * Constants defined for keys of the data array.
     * Identical to the name of the getter in snake case
     */
    const TYPE = 'type';
    const PLACEHOLDER = 'placeholder';
    /**#@-*/

    /**
     * Get comment type
     *
     * @return string
     */
    public function getType();

    /**
     * Get comment placeholder
     *
     * @return string
     */
    public function getPlaceholder();
}
