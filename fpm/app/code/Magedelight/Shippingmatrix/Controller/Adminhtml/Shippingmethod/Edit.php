<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Shippingmatrix
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Shippingmatrix\Controller\Adminhtml\Shippingmethod;

class Edit extends \Magedelight\Shippingmatrix\Controller\Adminhtml\Shippingmethod
{

    /**
     * @var \Magedelight\Shippingmatrix\Model\ShippingMethodFactory
     */
    protected $_shippingMethodFactory;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magedelight\Shippingmatrix\Model\ShippingMethodFactory $shippingMethodFactory
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Framework\Registry $coreRegistry
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magedelight\Shippingmatrix\Model\ShippingMethodFactory $shippingMethodFactory,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Registry $coreRegistry
    ) {
        $this->_shippingMethodFactory = $shippingMethodFactory;
        $this->resultPageFactory = $resultPageFactory;
        $this->_coreRegistry = $coreRegistry;
        parent::__construct($context);
    }

    /**
     * Check Grid List Permission.
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magedelight_Shippingmatrix::view_detail');
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
        $resultPage->setActiveMenu('Magedelight_Shippingmatrix::shipping_methods')
            ->addBreadcrumb(__('Shipping Method'), __('Shipping Method'));
        return $resultPage;
    }
    
    public function execute()
    {
        // Get ID and create model
        $id = $this->getRequest()->getParam('shipping_method_id');
        $model = $this->_shippingMethodFactory->create();
        
        // Initial checking
        if ($id) {
            $model->load($id);
            if (!$model->getId()) {
                $this->messageManager->addError(__('This shipping method no longer exists.'));
                /** \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
                $resultRedirect = $this->resultRedirectFactory->create();
                return $resultRedirect->setPath('*/*/');
            }
        }
        
        $this->_coreRegistry->register('shipping_method', $model);
        
        $resultPage = $this->_initAction();
        // Build edit form
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage->getConfig()->getTitle()->prepend(__('Shipping Methods'));
        $resultPage->getConfig()->getTitle()
            ->prepend($model->getId() ? $model->getTitle() : __('New Shipping Method'));

        return $resultPage;
    }
}
