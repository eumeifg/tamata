<?php
/**
 * Magedelight
 * Copyright (C) 2017 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Abandonedcart
 * @copyright Copyright (c) 2017 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Abandonedcart\Observer;

use Magento\Framework\Event\ObserverInterface;

class CheckIfAbandonedQuoteWasUpdated implements ObserverInterface
{
    /**
     * @var Magedelight\Abandonedcart\Model\EmailQueueFactory
     */
    protected $emailQueueFactory;

    /**
     * @var \Magedelight\Abandonedcart\Helper\Data
     */
    protected $helper;

    /**
     * @param \Magedelight\Abandonedcart\Model\EmailQueueFactory $emailQueueFactory
     * @param \Magedelight\Abandonedcart\Helper\Data $helper
     */
    public function __construct(
        \Magedelight\Abandonedcart\Model\EmailQueueFactory $emailQueueFactory,
        \Magedelight\Abandonedcart\Helper\Data $helper
    ) {
        $this->emailQueueFactory = $emailQueueFactory;
        $this->helper = $helper;
    }

    /**
     * check if quote was updated for abandoned cart
     * @return null
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if ($this->helper->isAbandonedcartEnabled()) {
            $quoteData = $observer->getEvent()->getCart()->getQuote();
            $quoteId = $quoteData->getId();
            $queueCollection = $this->emailQueueFactory->create()->getCollection()
            ->addFieldToFilter('quote_id', $quoteId);
            if (count($queueCollection) > 0) {
                foreach ($queueCollection as $emailQueue) {
                    $queueObj = $this->emailQueueFactory->create()->load($emailQueue->getAbandonedcartEmailId());
                    $this->helper->getAbandonedCartUpdatedOnQuoteUpdate($queueObj);
                }
            }
        }
        return $this;
    }
}
