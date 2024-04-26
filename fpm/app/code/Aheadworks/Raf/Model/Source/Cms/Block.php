<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Raf\Model\Source\Cms;

use Magento\Framework\Option\ArrayInterface;
use Magento\Cms\Model\ResourceModel\Block\Collection as BlockCollection;
use Magento\Cms\Model\ResourceModel\Block\CollectionFactory as BlockCollectionFactory;

/**
 * Class Block
 *
 * @package Aheadworks\Raf\Model\Source\Cms
 */
class Block implements ArrayInterface
{
    /**
     * @var int
     */
    const DONT_DISPLAY = 0;

    /**
     * @var BlockCollection
     */
    private $blockCollection;

    /**
     * @var array
     */
    private $options;

    /**
     * @param BlockCollectionFactory $blockCollectionFactory
     */
    public function __construct(
        BlockCollectionFactory $blockCollectionFactory
    ) {
        $this->blockCollection = $blockCollectionFactory->create();
    }

    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        if (!$this->options) {
            $this->options = array_merge(
                [self::DONT_DISPLAY => __('Don\'t Display')],
                $this->blockCollection->toOptionArray()
            );
        }

        return $this->options;
    }
}
