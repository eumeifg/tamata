<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Raf\Model\Transaction\Comment\Metadata;

use Magento\Framework\DataObject;

/**
 * Class CommentMetadata
 *
 * @package Aheadworks\Raf\Model\Transaction\Comment\Metadata
 */
class CommentMetadata extends DataObject implements CommentMetadataInterface
{
    /**
     * {@inheritdoc}
     */
    public function getPlaceholder()
    {
        return $this->getData(self::PLACEHOLDER);
    }

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return $this->getData(self::TYPE);
    }
}
