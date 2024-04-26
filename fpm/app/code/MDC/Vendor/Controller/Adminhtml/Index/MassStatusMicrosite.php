<?php
/**
 * KrishTechnolabs
 *
 * PHP version 7
 *
 * @category  KrishTechnolabs
 * @package   MDC_Vendor
 * @copyright 2019 (c) KrishTechnolabs (https://www.KrishTechnolabs.com/)
 * @license   https://www.krishtechnolabs.com/LICENSE.txt Krish License
 * @link      https://www.krishtechnolabs.com/
 */
namespace MDC\Vendor\Controller\Adminhtml\Index;

use Magento\Framework\Controller\ResultFactory;
use Magedelight\Vendor\Api\VendorRepositoryInterface;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class MassStatusMicrosite extends \Magento\Backend\App\Action
{

    /**
     * @var \Magedelight\Vendor\Model\ResourceModel\VendorWebsite\CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var \Magento\Ui\Component\MassAction\Filter
     */
    protected $filter;
    
    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Ui\Component\MassAction\Filter $filter
     * @param \Magedelight\Vendor\Model\ResourceModel\VendorWebsite\CollectionFactory $collectionFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Ui\Component\MassAction\Filter $filter,
        \Magedelight\Vendor\Model\ResourceModel\VendorWebsite\CollectionFactory $collectionFactory
    ) {
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        try {
            $collection = $this->filter->getCollection($this->collectionFactory->create());
            return $this->massAction($collection);
        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
            /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            return $resultRedirect->setPath('vendor/*/index');
        }
    }
    
    /**
     * Vendor mass status action
     *
     * @param AbstractCollection $collection
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    protected function massAction(AbstractCollection $collection)
    {
       
        $status = (int) $this->getRequest()->getParam('enable_microsite');
        $request = $this->getRequest();
        foreach ($collection as $item) {
            $item->setData('enable_microsite', $status);
            $item->save();
        }
        
        $this->messageManager->addSuccess(__('A total of %1 record(s) were updated.', $collection->getSize()));
        
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setPath($this->getComponentRefererUrl());

        return $resultRedirect;
    }
    
    protected function getComponentRefererUrl()
    {
        return $this->filter->getComponentRefererUrl()?: 'vendor/*/index';
    }
}
