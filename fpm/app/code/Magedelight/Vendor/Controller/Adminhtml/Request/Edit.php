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
namespace Magedelight\Vendor\Controller\Adminhtml\Request;

class Edit extends \Magedelight\Vendor\Controller\Adminhtml\Request
{
    /**
     * @var \Magedelight\Vendor\Api\VendorRepositoryInterface
     */
    protected $vendorRepository;

    /**
     * @var \Magedelight\Vendor\Helper\View
     */
    protected $viewHelper;

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magedelight\Vendor\Helper\View $viewHelper
     * @param \Magedelight\Vendor\Api\VendorRepositoryInterface $vendorRepository
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magedelight\Vendor\Helper\View $viewHelper,
        \Magedelight\Vendor\Api\VendorRepositoryInterface $vendorRepository,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
        $this->_coreRegistry = $coreRegistry;
        $this->resultPageFactory = $resultPageFactory;
        $this->viewHelper = $viewHelper;
        $this->vendorRepository = $vendorRepository;
        parent::__construct($context);
    }

    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magedelight_Vendor::view_detail_request');
    }

    /**
     * Edit Brand Page
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');

        if ($id) {
            $model = $this->_objectManager->create(\Magedelight\Vendor\Model\Request::class);
            $model->load($id);
            if (!$model->getId()) {
                $this->messageManager->addError(__('This Request no longer exits. '));
                /** \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
                $resultRedirect = $this->resultRedirectFactory->create();

                return $resultRedirect->setPath('*/*/');
            }

            $vendor = $this->vendorRepository->getById($model->getVendorId());
            $model->setVendorName($this->viewHelper->getVendorFullBusinessName($vendor));

            $data = $this->_objectManager->get(\Magento\Backend\Model\Session::class)->getFormData(true);
            if (!empty($data)) {
                $model->setData($data);
            }

            $this->_coreRegistry->register('vendor_status_request', $model);
        }

        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->_initAction();

        $resultPage->addBreadcrumb(
            $id ? __('Edit Request') : __('New Request'),
            $id ? __('Edit Request') : __('New Request')
        );

        if ($id) {
            $resultPage->getConfig()->getTitle()->prepend($this->_coreRegistry->registry('vendor_status_request')
                ->getVendorName());
        } else {
            $resultPage->getConfig()->getTitle()->prepend(__('New Request'));
        }

        return $resultPage;
    }

    /**
     * Init actions
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    protected function _initAction()
    {
        // load layout, set active menu and breadcrumbs
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Magedelight_Vendor::request')
                ->addBreadcrumb(__('Request'), __('Request'))
                ->addBreadcrumb(__('Manage Request'), __('Manage Request'));
        return $resultPage;
    }
}
