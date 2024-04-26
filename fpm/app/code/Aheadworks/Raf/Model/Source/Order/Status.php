<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Raf\Model\Source\Order;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\Sales\Model\ResourceModel\Order\Status\CollectionFactory as OrderStatusCollectionFactory;
use Magento\Sales\Model\ResourceModel\Order\Status\Collection as OrderStatusCollection;

/**
 * Class Status
 * @package Aheadworks\Raf\Model\Source\Order
 */
class Status implements OptionSourceInterface
{
    /**
     * @var OrderStatusCollection
     */
    private $orderStatusCollection;

    /**
     * @var array
     */
    private $options;

    /**
     * @param OrderStatusCollectionFactory $orderStatusCollectionFactory
     */
    public function __construct(OrderStatusCollectionFactory $orderStatusCollectionFactory)
    {
        $this->orderStatusCollection = $orderStatusCollectionFactory->create();
    }

    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        if (null === $this->options) {
            $this->options = $this->orderStatusCollection->toOptionArray();
        }

        return $this->options;
    }
}
