<?php

namespace CAT\GiftCard\Controller\Adminhtml\Rules\Coupon;

use Magento\Backend\App\Action;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\App\ResponseInterface;
use CAT\GiftCard\Model\ResourceModel\Coupon\CollectionFactory as CouponCollectionFactory;

class CouponsMassDelete extends \Magento\Backend\App\Action
{
    /**
     * @var Filter
     */
    protected $filter;

    /**
     * @var CouponCollectionFactory
     */
    protected $couponCollectionFactory;

    /**
     * CouponsMassDelete constructor.
     * @param Action\Context $context
     * @param Filter $filter
     * @param CouponCollectionFactory $couponCollectionFactory
     */
    public function __construct(
        Action\Context $context,
        Filter $filter,
        CouponCollectionFactory $couponCollectionFactory
    ) {
        $this->filter = $filter;
        $this->couponCollectionFactory = $couponCollectionFactory;
        parent::__construct($context);
    }

    /**
     * @return ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     */
    public function execute()
    {
        $ruleId = $this->getRequest()->getParam('id');
        //echo "<pre>"; print_r($ruleId); echo "</pre>"; die('==>');
        if ($ruleId) {
            $collection = $this->filter->getCollection($this->couponCollectionFactory->create());
            echo "======>".$collectionSize = $collection->getSize();
            echo "<pre>"; print_r($collection->getData()); echo "</pre>"; die('=====>');
            if ($collectionSize) {
                foreach ($collection as $item) {
                    $item->delete();
                }
                $this->messageManager->addSuccess(__('A total of %1 coupon(s) have been deleted.', $collectionSize));
            }
            /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            return $resultRedirect->setPath('giftcardrule/rules/edit/',['id' => $ruleId]);
        }
    }
}