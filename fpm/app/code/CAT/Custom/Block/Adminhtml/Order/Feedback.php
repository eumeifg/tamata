<?php
namespace CAT\Custom\Block\Adminhtml\Order;

use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Sales\Api\OrderRepositoryInterface;
use CAT\Custom\Model\ResourceModel\CustomerFeedback\CollectionFactory;

class Feedback extends Template
{

    protected $orderRepository;

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
        OrderRepositoryInterface $orderRepository,
        CollectionFactory $collectionFactory,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->collectionFactory = $collectionFactory;
        $this->orderRepository = $orderRepository;
    }

    /**
     * @return string|null
     */
    public function getFeedback()
    {
        $orderId = $this->getRequest()->getParam('order_id');
        if($orderId) {
            $order = $this->orderRepository->get($orderId);
            if($order->getCustomerId()) {
                $collection = $this->collectionFactory->create();
                $collection->addFieldToFilter('customer_id', $order->getCustomerId());
                if($collection->getSize()) {
                    return $collection->getFirstItem();
                }
                return false;
            }
            return false;
        }
        return false;
    }
}