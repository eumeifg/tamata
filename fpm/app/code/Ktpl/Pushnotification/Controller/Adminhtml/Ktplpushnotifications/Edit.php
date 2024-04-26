<?php declare(strict_types=1);


namespace Ktpl\Pushnotification\Controller\Adminhtml\Ktplpushnotifications;


class Edit extends \Ktpl\Pushnotification\Controller\Adminhtml\Ktplpushnotifications
{

    protected $resultPageFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context, $coreRegistry);
    }

    /**
     * Edit action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        // 1. Get ID and create model
        $id = $this->getRequest()->getParam('id');
        $model = $this->_objectManager->create(\Ktpl\Pushnotification\Model\KtplPushnotifications::class);

        // 2. Initial checking
        if ($id) {
            $model->load($id);
            if (!$model->getId()) {
                $this->messageManager->addErrorMessage(__('This Pushnotifications no longer exists.'));
                /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
                $resultRedirect = $this->resultRedirectFactory->create();
                return $resultRedirect->setPath('*/*/');
            }
        }
       //echo "<pre>";print_r($model->getData());die;
        $this->_coreRegistry->register('ktpl_pushnotification', $model);

        // 3. Build edit form
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $this->initPage($resultPage)->addBreadcrumb(
            $id ? __('Edit Pushnotifications') : __('New Pushnotifications'),
            $id ? __('Edit Pushnotifications') : __('New Pushnotifications')
        );
        $resultPage->getConfig()->getTitle()->prepend(__('Ktpl Pushnotifications'));
        $resultPage->getConfig()->getTitle()->prepend($model->getId() ? __('Edit Pushnotifications %1', $model->getId()) : __('New Pushnotifications'));
        return $resultPage;
    }
}

