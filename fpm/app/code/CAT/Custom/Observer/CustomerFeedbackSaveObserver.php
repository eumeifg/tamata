<?php

namespace CAT\Custom\Observer;

use Magento\Framework\Event\ObserverInterface;
use CAT\Custom\Model\CustomerFeedbackFactory;

class CustomerFeedbackSaveObserver implements ObserverInterface
{

    /**
     * @var CustomerFeedbackFactory
     */
    protected $_customerFeedbackFactory;

    /**
     * @param CustomerFeedbackFactory $customerFeedbackFactory
     */
    public function __construct(
        CustomerFeedbackFactory $customerFeedbackFactory
    ) {
        $this->_customerFeedbackFactory = $customerFeedbackFactory;
    }

    /**
     * Customer balance update after save
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /* @var $request \Magento\Framework\App\RequestInterface */
        $request = $observer->getRequest();
        $customer = $observer->getCustomer();
        $data = $request->getPost('customerfeedback');
        /* @var $customer \Magento\Customer\Api\Data\CustomerInterface */
        $customer = $observer->getCustomer();
        if ($data && (!empty($data['score']) || !empty($data['comment']))) {
            $collection = $this->_customerFeedbackFactory->create()->getCollection()->addFieldToFilter('customer_id', $customer->getId());
            if($collection->getSize()) {
                $feedback = $collection->getFirstItem();
            } else {
                $feedback = $this->_customerFeedbackFactory->create();
                $feedback->setCustomerId($customer->getId());
            }
            if($data['score'] >= 0) {
                $feedback->setScore($data['score']);
            }
            if($data['comment']) {
                $feedback->setComment($data['comment']);
            }
            $feedback->save();
        }
    }
}