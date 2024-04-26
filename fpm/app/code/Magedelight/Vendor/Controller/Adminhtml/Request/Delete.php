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

class Delete extends \Magedelight\Vendor\Controller\Adminhtml\Request
{
    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magedelight\Vendor\Model\VendorFactory $vendorFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magedelight\Vendor\Model\VendorFactory $vendorFactory
    ) {
        $this->_vendorFactory = $vendorFactory;
        parent::__construct($context);
    }

    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magedelight_Vendor::delete_request');
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     */
    public function execute()
    {
        $requestId = $this->getRequest()->getParam('id');

        try {
            $vendorRequest = $this->_objectManager->get(\Magedelight\Vendor\Model\Request::class)->load($requestId);
            $vendorModel = $this->_vendorFactory->create()->load($vendorRequest->getVendorId());
            $vendorModel->setData('vacation_request_status', null);
            $vendorModel->setData('vacation_message', null);
            $vendorModel->save();
            $vendorRequest->delete();
            $this->messageManager->addSuccess(
                __('Delete successfully !')
            );
        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
        }
        $this->_redirect('*/*/');
    }
}
