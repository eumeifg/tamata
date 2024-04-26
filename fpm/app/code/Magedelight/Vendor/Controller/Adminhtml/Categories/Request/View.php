<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Vendor
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Vendor\Controller\Adminhtml\Categories\Request;

class View extends \Magedelight\Vendor\Controller\Adminhtml\Categories\Request
{

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \Magedelight\Vendor\Api\CategoryRequestRepositoryInterface
     */
    protected $categoryRequestRepository;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magedelight\Vendor\Api\CategoryRequestRepositoryInterface $categoryRequestRepository
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magedelight\Vendor\Api\CategoryRequestRepositoryInterface $categoryRequestRepository
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->categoryRequestRepository = $categoryRequestRepository;
        parent::__construct($context, $coreRegistry);
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Page|\Magento\Backend\Model\View\Result\Redirect|\Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();

        $id = $this->getRequest()->getParam('request_id');
        if (!$id) {
            return $resultRedirect->setPath('*/*/');
        }

        $request = $this->categoryRequestRepository->getById($id);
        $this->_coreRegistry->register('category_request', $request);
        if (!$request->getId()) {
            $this->messageManager->addError(__('This category request no longer exists.'));
            return $resultRedirect->setPath('*/*/');
        }

        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $this->initPage($resultPage)->addBreadcrumb(
            $id ? __('View Category Request') . ' - ' . $id : '',
            $id ? __('View Category Request') . ' - ' . $id : ''
        );
        $resultPage->getConfig()->getTitle()->prepend(__('View Category Request'));
        return $resultPage;
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magedelight_Vendor::view_category_request');
    }
}
