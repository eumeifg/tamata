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
use Magento\Framework\App\RequestInterface;

class CheckIfQuoteWasAbandoned implements ObserverInterface
{
    /**
     * Param Values
     *
     * @var Magedelight\Abandonedcart\Observer
     */
    protected $request;

    /**
     * @var Magedelight\Abandonedcart\Model\HistoryFactory
     */
    protected $historyFactory;

    /**
     * @param \Magedelight\Abandonedcart\Model\HistoryFactory $historyFactory
     * @param \Magedelight\Abandonedcart\Model\EmailQueueFactory $emailQueueFactory
     * @param \Magedelight\Abandonedcart\Helper\Data $helper
     * @param \Magento\Framework\App\Request\Http $request
     */
    public function __construct(
        \Magedelight\Abandonedcart\Model\HistoryFactory $historyFactory,
        \Magedelight\Abandonedcart\Model\EmailQueueFactory $emailQueueFactory,
        \Magedelight\Abandonedcart\Helper\Data $helper,
        \Magento\Framework\App\Request\Http $request
    ) {
        $this->historyFactory = $historyFactory;
        $this->emailQueueFactory = $emailQueueFactory;
        $this->helper = $helper;
        $this->request = $request;
    }

    /**
     * check if quote was converted to order
     * return null
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if ($this->helper->isAbandonedcartEnabled()) {
            $order = $observer->getEvent()->getOrder();
            $historyCollection = $this->historyFactory->create()->getCollection()
            ->addFieldToFilter('quote_id', $order->getQuoteId());

            if (count($historyCollection) > 0) {
                foreach ($historyCollection as $history) {
                    $this->historyFactory->create()->load($history->getHistoryId())
                        ->setIsOrdered(1)
                        ->setstatus(7)
                        ->save();
                }
            }

            $queueCollection = $this->emailQueueFactory->create()->getCollection()
            ->addFieldToFilter('quote_id', $order->getQuoteId());
            if (count($queueCollection) > 0) {
                foreach ($queueCollection as $queue) {
                    $queueObj = $this->emailQueueFactory->create()
                    ->load($queue->getAbandonedcartEmailId())
                    ->setStatus(\Magedelight\Abandonedcart\Model\Config\Source\EmailStatus::ORDERED);
                    $this->helper->saveToHistory($queueObj);
                }
            }
        }
        return $this;
    }
}
