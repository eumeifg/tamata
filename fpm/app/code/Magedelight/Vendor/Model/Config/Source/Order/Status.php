<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Vendor
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Vendor\Model\Config\Source\Order;

class Status implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var \Magento\Sales\Model\Order\ConfigFactory
     */
    protected $orderConfigFactory;

    /**
     * @var \Magento\Framework\Model\Context
     */
    protected $context;

    /**
     * @param \Magento\Sales\Model\Order\ConfigFactory $orderConfigFactory
     * @param \Magento\Framework\Model\Context         $context
     */
    public function __construct(
        \Magento\Sales\Model\Order\ConfigFactory $orderConfigFactory,
        \Magento\Framework\Model\Context $context
    ) {
        $this->orderConfigFactory = $orderConfigFactory;
        $this->context = $context;
    }

    /**
     * @var array
     */
    protected $options;

    /**
     * @return array
     */
    public function toOptionArray()
    {
        if (!$this->options) {
            $this->options = [];
            $statuses = $this->orderConfigFactory->create()->getStatuses();
            foreach ($statuses as $id => $status) {
                $this->options[] = ['value' => $id, 'label' => $status];
            }
        }

        return $this->options;
    }
}
