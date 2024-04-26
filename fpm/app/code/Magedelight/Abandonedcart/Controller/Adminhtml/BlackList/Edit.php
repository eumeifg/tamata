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

namespace Magedelight\Abandonedcart\Controller\Adminhtml\BlackList;
 
class Edit extends \Magedelight\Abandonedcart\Controller\Adminhtml\Blacklist
{
    
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;
    
    /**
     * @var \Magedelight\Abandonedcart\Model\BlacklistFactory
     */
    protected $blacklistFactory;
    
    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magedelight\Abandonedcart\Model\BlacklistFactory $blacklistFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->blacklistFactory = $blacklistFactory;
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
        $model = $this->blacklistFactory->create();

        if ($id) {

            $model->load($id);
            if (!$model->getId()) {
                $this->messageManager->addError(__('This blacklist no longer exists.'));
                /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
                $resultRedirect = $this->resultRedirectFactory->create();
                return $resultRedirect->setPath('*/*/');
            }
        }
        $this->_coreRegistry->register('magedelight_abandonedcart_blacklist', $model);

        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();

        $this->initPage($resultPage)->addBreadcrumb(
            $id ? __('Edit Blacklist') : __('New Blacklist'),
            $id ? __('Edit Blacklist') : __('New Blacklist')
        );

        $resultPage->getConfig()->getTitle()->prepend(__('Abandoned Cart Blacklist'));
        $resultPage->getConfig()->getTitle()->prepend($model->getId() ? __('Edit Blacklist') : __('New Blacklist'));
        return $resultPage;
    }
}
