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
namespace Magedelight\Sales\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Checkout\Model\Session;

class CartSaveAfter implements ObserverInterface
{

    /**
     * @var Session
     */
    private $checkoutSession;

    /**
     *
     * @param \Magento\Framework\Model\Context $context
     * @param Session $checkoutSession
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        Session $checkoutSession
    ) {
        $this->context = $context;
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * Set Cart Item data for promocode condition for vendor specific
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $quote = $observer->getEvent()->getQuote();
        $data = [];
        $this->checkoutSession->unsQuoteItemsQtyData();
        foreach ($quote->getAllItems() as $quoteitem) {
            $item = $quoteitem->getData();
            if ($item['product_type'] == 'simple') {
                $data[$quoteitem->getId()][0] = $item['product_id'];
                if (isset($item['vendor_id'])) {
                    $data[$quoteitem->getId()][1] = $item['vendor_id'];
                }
            }
        }
        $this->checkoutSession->setQuoteItemsQtyData($data);
    }
}
