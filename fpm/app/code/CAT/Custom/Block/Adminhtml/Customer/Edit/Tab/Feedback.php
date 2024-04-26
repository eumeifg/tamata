<?php
namespace CAT\Custom\Block\Adminhtml\Customer\Edit\Tab;

use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use CAT\Custom\Model\ResourceModel\CustomerFeedback\CollectionFactory;

class Feedback extends Template
{

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @param Context $context
     * @param CollectionFactory $collectionFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        CollectionFactory $collectionFactory,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * @return string|null
     */
    public function getFeedback()
    {
        $customerId = $this->getRequest()->getParam('id');
        if($customerId) {
            $collection = $this->collectionFactory->create();
            $collection->addFieldToFilter('customer_id', $customerId);
            if($collection->getSize()) {
                return $collection->getFirstItem();
            }
            return false;
        }
        return false;
    }
}