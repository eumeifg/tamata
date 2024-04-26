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

class CheckIfItemDeleteToAbandoned implements ObserverInterface
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
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    /*
     * @var \Magento\Quote\Model\QuoteRepository
     */
    protected $quoteRepository;

    /**
     * @var Magedelight\Abandonedcart\Model\HistoryFactory
     */
    protected $historyFactory;

    protected $reportsCollectionFactory;

    /**
     * @param \Magedelight\Abandonedcart\Model\EmailQueueFactory $emailQueueFactory
     * @param \Magedelight\Abandonedcart\Helper\Data $helper
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Quote\Model\QuoteRepository $quoteRepository
     * @param \Magedelight\Abandonedcart\Model\HistoryFactory $historyFactory
     * @param \Magento\Framework\ObjectManagerInterface $objectmanager
     *
     */
    public function __construct(
        \Magedelight\Abandonedcart\Model\EmailQueueFactory $emailQueueFactory,
        \Magedelight\Abandonedcart\Helper\Data $helper,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Quote\Model\QuoteRepository $quoteRepository,
        \Magedelight\Abandonedcart\Model\HistoryFactory $historyFactory,
        \Magedelight\Abandonedcart\Model\ResourceModel\Report\CollectionFactory $reportsCollectionFactory
    ) {
        $this->emailQueueFactory = $emailQueueFactory;
        $this->helper = $helper;
        $this->_checkoutSession = $checkoutSession;
        $this->quoteRepository = $quoteRepository;
        $this->historyFactory = $historyFactory;
        $this->reportsCollectionFactory = $reportsCollectionFactory;
    }

    /**
     * check if quote was updated for abandoned cart
     * @return null
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if ($this->helper->isAbandonedcartEnabled()) {
            $quoteData = $this->_checkoutSession->getQuote();
            if ($quoteData->getItemsSummaryQty() == false) {
                $quoteId = $quoteData->getId();
                $CustomerId = $quoteData->getCustomerId();
                $this->_checkoutSession->setQuoteId(null);
                $quoteData->setIsActive(false);
                //$this->quoteRepository->save($quoteData);
                $queueCollection = $this->emailQueueFactory->create()->getCollection()
                    ->addFieldToFilter('quote_id', $quoteId)
                    ->addFieldToFilter('customer_id', $CustomerId);

                if (count($queueCollection) > 0) {
                    foreach ($queueCollection as $emailQueue) {
                        $emailQueue->delete();
                    }
                }

                $ReportsCollection = $this->reportsCollectionFactory->create();
                $ReportsCollection->addFieldToFilter('quote_id', $quoteId);
                $ReportsCollection->addFieldToFilter('customer_id', $CustomerId);

                if (count($ReportsCollection) > 0) {
                    foreach ($ReportsCollection as $res) {
                        $res->delete();
                    }
                }

                $historyCollection = $this->historyFactory->create()->getCollection()
                    ->addFieldToFilter('quote_id', $quoteId)
                    ->addFieldToFilter('customer_id', $CustomerId)
                    ->addFieldToFilter('is_sent', 0);

                if (count($historyCollection) > 0) {
                    foreach ($historyCollection as $history) {
                        $history->setstatus(3);
                        $history->save();
                    }
                }
            }
        }
        return $this;
    }
}
