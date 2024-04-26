<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Sales
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
/**
 * Observer to remove Magento's main order status from customer order details page.
 */
namespace Magedelight\Sales\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class RemoveOrderStatus implements ObserverInterface
{

    /**
     * @var \Magedelight\Sales\Helper\Data
     */
    protected $salesHelper;

    /**
     * @param \Magedelight\Sales\Helper\Data $salesHelper
     */
    public function __construct(
        \Magedelight\Sales\Helper\Data $salesHelper
    ) {
        $this->salesHelper = $salesHelper;
    }

    public function execute(Observer $observer)
    {
        /** @var \Magento\Framework\View\Layout $layout */
        $layout = $observer->getLayout();
        $block = $layout->getBlock('order.status');
        if ($block) {
            if (!$this->salesHelper->showMainOrderStatus()) {
                $layout->unsetElement('order.status');
            }
        }
    }
}
