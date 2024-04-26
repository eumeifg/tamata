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
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\Serialize\Serializer\Json;

class CheckIfItemAddedToAbandoned implements ObserverInterface
{
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    protected $helper;

    protected $reportsCollectionFactory;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectmanager
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magedelight\Abandonedcart\Helper\Data $helper
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectmanager,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magedelight\Abandonedcart\Helper\Data $helper,
        \Magedelight\Abandonedcart\Model\ResourceModel\Report\CollectionFactory $reportsCollectionFactory,
        SerializerInterface $serializer
    ) {
        $this->_objectManager = $objectmanager;
        $this->_checkoutSession = $checkoutSession;
        $this->helper = $helper;
        $this->reportsCollectionFactory = $reportsCollectionFactory;
        $this->serializer = $serializer;
    }

    /**
     * check if quote was updated for abandoned cart
     * @return null
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if ($this->helper->isAbandonedcartEnabled()) {
            $quoteData = $this->_checkoutSession->getQuote();
            $queueCollection = $this->_objectManager->create(\Magedelight\Abandonedcart\Model\EmailQueue::class)
                ->getCollection()
                ->addFieldToFilter('quote_id', $quoteData->getId());

            if (count($queueCollection) > 0) {
                $quoteId = $quoteData->getId();
                $CustomerId = $quoteData->getCustomerId();
                $ReportsCollection = $this->reportsCollectionFactory->create();
                $ReportsCollection->addFieldToFilter('quote_id', $quoteId);
                $ReportsCollection->addFieldToFilter('customer_id', $CustomerId);

                if (count($ReportsCollection) > 0) {
                    foreach ($ReportsCollection as $res) {
                        $res->delete();
                    }
                }
                foreach ($queueCollection as $emailQueue) {
                    $queueObj = $this->_objectManager->create(\Magedelight\Abandonedcart\Model\EmailQueue::class)
                        ->load($emailQueue->getAbandonedcartEmailId());
                    $variableData = $this->serializer->unserialize($queueObj->getData('variables'));
                    $cancelConditions = explode(',', $variableData['cancel_condition']);
                    if (in_array('1', $cancelConditions)) {
                        $queueObj->setIsSent(\Magedelight\Abandonedcart\Ui\Component\Listing\Column\IsSentOptions::NO);
                        $queueObj->setStatus(\Magedelight\Abandonedcart\Model\Config\Source\EmailStatus::CART_UPDATED);

                        $historyObj = $this->_objectManager->create(\Magedelight\Abandonedcart\Model\History::class);
                        $historyObj->setData(
                            [
                                'first_name' => $queueObj->getFirstName(),
                                'last_name' => $queueObj->getLastName(),
                                'email' => $queueObj->getEmail(),
                                'template_id' => $queueObj->getTemplateId(),
                                'variables' => $queueObj->getVariables(),
                                'customer_id' => $queueObj->getCustomerId(),
                                'email_content' => $queueObj->getEmailContent(),
                                'schedule_id' => $queueObj->getScheduleId(),
                                'queue_id' => $queueObj->getAbandonedcartEmailId(),
                                'quote_id' => $queueObj->getQuoteId(),
                                'send_coupon' => $queueObj->getSendCoupon(),
                                'cartprice_rule_id' => $queueObj->getCartpriceRuleId(),
                                'schedule_at' => $queueObj->getScheduleAt(),
                                'reference_id' => $queueObj->getReferenceId(),
                                'status' => $queueObj->getStatus(),
                                'is_sent' => $queueObj->getIsSent(),
                                'is_restored' => $queueObj->getIsRestored(),
                                'is_ordered' => $queueObj->getIsOrdered(),
                                'store_id' => $queueObj->getStoreId(),
                            ]
                        );

                        $historyObj->save();
                        $queueObj->delete();
                    } else {
                        $queueObj->setStatus(\Magedelight\Abandonedcart\Model\Config\Source\EmailQueueStatus
                           ::CART_UPDATED);
                        $updateQuoteData = $this->helper->getUpdateQuoteItemsById($queueObj);
                        $queueObj->setvariables($updateQuoteData);
                        $queueObj->save();
                    }
                }
            }
        }
        return $this;
    }
}
