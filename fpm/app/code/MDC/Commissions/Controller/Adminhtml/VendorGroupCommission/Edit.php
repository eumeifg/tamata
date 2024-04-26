<?php
 /**
  * KrishTechnolabs
  *
  * PHP version 7
  *
  * @category  KrishTechnolabs
  * @package   MDC_Commissions
  * @copyright 2019 (c) KrishTechnolabs (https://www.KrishTechnolabs.com/)
  * @license   https://www.krishtechnolabs.com/LICENSE.txt Krish License
  * @link      https://www.krishtechnolabs.com/
  */
namespace MDC\Commissions\Controller\Adminhtml\VendorGroupCommission;

class Edit extends \MDC\Commissions\Controller\Adminhtml\VendorGroupCommission
{
    public function execute()
    {
        $id = $this->getRequest()->getParam('vendor_group_commission_id');
        $model = $this->vendorGroupCommissionFactory->create();
        $registryObject = $this->registry;
        $this->_view->loadLayout();
        if ($id) {
            $model->load($id);
            if (!$model->getId()) {
                $this->messageManager->addError(__('This Vendor Group Commission type no longer exists.'));
                $this->_redirect('*/*/');
                return;
            }
            $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Edit Vendor Group Commission'));
        } else {
            $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Add New Vendor Group Commission'));
        }
        $data = $this->_getSession()->getFormData(true);
        if (!empty($data)) {
            $model->setData($data);
        }
        $registryObject->register('vendor_group_commission', $model);
        $this->_view->getLayout()->initMessages();
        $this->_view->renderLayout();
    }
}
