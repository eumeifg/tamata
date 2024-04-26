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
namespace Magedelight\Sales\Observer\Sales;

use Magento\Framework\Event\ObserverInterface;

class ConvertQuote implements ObserverInterface
{
    /**
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $quote=$observer->getQuote();
        $order=$observer->getOrder();
        foreach ($order->getAllItems() as $orderitem) {
            foreach ($quote->getAllItems() as $quoteitem) {
                if ($orderitem->getQuoteItemId()==$quoteitem->getId()) {
                    $orderitem->setData('vendor_id', $quoteitem->getData('vendor_id'));
                    $orderitem->setData('shipping_amount', $quoteitem->getData('shipping_amount'));
                    $orderitem->setData('vendor_sku', $quoteitem->getData('vendor_sku'));
                }
            }
        }
    }
}
