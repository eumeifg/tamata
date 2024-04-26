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

namespace Magedelight\Abandonedcart\Controller\Adminhtml\EmailQueue;
 
class Edit extends \Magedelight\Abandonedcart\Controller\Adminhtml\EmailQueue
{
    
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;
    
    /**
     * @var \Magedelight\Abandonedcart\Model\EmailQueueFactory
     */
    protected $emailqueueFactory;
    
    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magedelight\Abandonedcart\Model\EmailQueueFactory $emailqueueFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->emailqueueFactory = $emailqueueFactory;
        parent::__construct($context, $coreRegistry);
    }

    /**
     * Edit Associate Attribute
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        $model = $this->emailqueueFactory->create();

        if ($id) {
            $model->load($id);
            if (!$model->getId()) {
                $this->messageManager->addError(__('This email queue item no longer exists.'));
                /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
                $resultRedirect = $this->resultRedirectFactory->create();
                return $resultRedirect->setPath('*/*/');
            }
        }
        $this->_coreRegistry->register('magedelight_abandonedcart_emailqueue', $model);

        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $this->initPage($resultPage)->addBreadcrumb(
            $id ? __('Edit Email Queue') : __('New Email Queue'),
            $id ? __('Edit Email Queue') : __('New Email Queue')
        );
        $resultPage->getConfig()->getTitle()->prepend(__('Abandoned Cart EmailQueue'));
        $resultPage->getConfig()->getTitle()->prepend($model->getId() ? $model->getEmailQueue() :__('New Email Queue'));
        return $resultPage;
    }
}
