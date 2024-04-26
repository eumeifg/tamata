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
namespace Magedelight\Vendor\Controller\Adminhtml\Review\Vendorrating;

class Edit extends \Magedelight\Vendor\Controller\Adminhtml\Review\Vendorrating
{
    protected $_vendorratingFactory;
    protected $resultPageFactory;
    protected $vendorRating;

    /**
     * @var  \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Backend\Model\Session
     */
    private $backendSession;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magedelight\Vendor\Model\VendorratingFactory $vendorratingFactory
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magedelight\Vendor\Model\Vendorrating $vendorRating
     * @param \Magento\Framework\Registry $registry
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magedelight\Vendor\Model\VendorratingFactory $vendorratingFactory,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magedelight\Vendor\Model\Vendorrating $vendorRating,
        \Magento\Framework\Registry $registry
    ) {
        parent::__construct($context, $vendorratingFactory, $resultPageFactory);
        $this->_vendorratingFactory = $vendorratingFactory;
        $this->resultPageFactory = $resultPageFactory;
        $this->vendorRating = $vendorRating;
        $this->registry = $registry;
        $this->backendSession = $context->getSession();
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magedelight_Vendor::view_vendorrating');
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('vendor_rating_id');
        $model = $this->vendorRating;
        $registryObject = $this->registry;
        if ($id) {
            $model->load($id);
            if (!$model->getId()) {
                $this->messageManager->addError(__('This Vendor Rating no longer exists.'));
                $this->_redirect('*/*/');
                return;
            }
        }
        $data = $this->backendSession->getFormData(true);
        if (!empty($data)) {
            $model->setData($data);
        }
        $registryObject->register('vendorrating', $model);
        $this->_view->loadLayout();
        $this->_view->getLayout()->initMessages();
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Vendor Rating Information'));
        $this->_view->renderLayout();
    }
}
