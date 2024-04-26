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

class Delete extends \MDC\Commissions\Controller\Adminhtml\VendorGroupCommission
{
    public function execute()
    {
        $group_commission_id = $this->getRequest()->getParam('vendor_group_commission_id');
        try {
            $groupCommission = $this->vendorGroupCommissionFactory->create()->load($group_commission_id);
            $groupCommission->delete();
            $this->messageManager->addSuccess(
                __('Record deleted successfully !')
            );
        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
        }
        $this->_redirect('*/*/');
    }
}
