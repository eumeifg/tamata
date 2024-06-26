<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Raf\Block\Adminhtml\Sales\Order;

use Magento\Framework\DataObject\Factory;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

/**
 * Class Total
 *
 * @package Aheadworks\Raf\Block\Adminhtml\Sales\Order
 */
class Total extends Template
{
    /**
     * @var Factory
     */
    private $factory;

    /**
     * @param Context $context
     * @param Factory $factory
     * @param array $data
     */
    public function __construct(
        Context $context,
        Factory $factory,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->factory = $factory;
    }

    /**
     * Init totals
     *
     * @return $this
     */
    public function initTotals()
    {
        $source = $this->getSource();
        if (!$source) {
            return $this;
        }

        if ($source->getBaseAwRafAmount()) {
            $this->getParentBlock()->addTotal(
                $this->factory->create(
                    [
                        'code'   => 'aw_raf_amount',
                        'strong' => false,
                        'label'  => __('Referral Discount'),
                        'value'  => $source->getAwRafAmount(),
                        'base_value' => $source->getBaseAwRafAmount(),
                    ]
                )
            );
        }

        return $this;
    }

    /**
     * Retrieve totals source object
     *
     * @return Order|null
     */
    private function getSource()
    {
        $parentBlock = $this->getParentBlock();
        if ($parentBlock) {
            return $parentBlock->getSource();
        }
        return null;
    }
}
