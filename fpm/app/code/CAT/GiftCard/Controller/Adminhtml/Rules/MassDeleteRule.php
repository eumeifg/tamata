<?php

namespace CAT\GiftCard\Controller\Adminhtml\Rules;

use Magento\Backend\App\Action;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\App\ResponseInterface;
use CAT\GiftCard\Model\ResourceModel\GiftCardRule\CollectionFactory;
use CAT\GiftCard\Model\ResourceModel\Coupon\CollectionFactory as CouponCollectionFactory;

class MassDeleteRule extends \Magento\Backend\App\Action
{
    /**
     * @var Filter
     */
    protected $filter;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var CouponCollectionFactory
     */
    protected $couponCollectionFactory;

    /**
     * MassDeleteRule constructor.
     * @param Action\Context $context
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     * @param CouponCollectionFactory $couponCollectionFactory
     */
    public function __construct(
        Action\Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory,
        CouponCollectionFactory $couponCollectionFactory
    ) {
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        $this->couponCollectionFactory = $couponCollectionFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        $collection = $this->filter->getCollection($this->collectionFactory->create());
        $collectionSize = $collection->getSize();
        if ($collectionSize) {
            foreach ($collection as $item) {
                $couponCollection = $this->couponCollectionFactory->create()->addFieldtoFilter('rule_id', ['eq' => $item->getRuleId()]);
                $couponCollectionSize = $couponCollection->getSize();
                if ($couponCollectionSize) {
                    foreach ($couponCollection as $couponItem) {
                        $couponItem->delete();
                    }
                }
                $item->delete();
            }
            $this->messageManager->addSuccess(__('A total of %1 rule(s) have been deleted.', $collectionSize));
        }
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('*/*/');
    }
}