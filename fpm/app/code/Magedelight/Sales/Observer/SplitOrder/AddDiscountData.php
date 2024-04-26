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
namespace Magedelight\Sales\Observer\SplitOrder;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magedelight\Sales\Model\Order\SplitOrder\DiscountProcessor;

class AddDiscountData implements ObserverInterface
{
    /**
     * @var DiscountProcessor
     */
    protected $discountProcessor;

    /**
     * AddDicountData constructor.
     * @param DiscountProcessor $discountProcessor
     */
    public function __construct(
        DiscountProcessor $discountProcessor
    ) {
        $this->discountProcessor = $discountProcessor;
    }

    public function execute(Observer $observer)
    {
        /** @var \Magento\Framework\View\Layout $layout */
        $subOrderData = $observer->getSubOrderData();
        $item = $observer->getItem();
        $vendorId = $observer->getVendorId();

        $subOrderData->setDiscountAmount($this->discountProcessor->calculateVendorDiscountAmount(
            $item,
            $vendorId
        ));
        $subOrderData->setBaseDiscountAmount($this->discountProcessor->calculateVendorDiscountAmount(
            $item,
            $vendorId,
            true
        ));

        $subOrderData->setDiscountDescription($this->discountProcessor->getDiscountDescription(
            $item,
            $vendorId
        ));

        $subOrderData->setDiscountTaxCompensationAmount($item->getDiscountTaxCompensationAmount());
        $subOrderData->setBaseDiscountTaxCompensationAmount($item->getBaseDiscountTaxCompensationAmount());
    }
}
