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

class Save extends \MDC\Commissions\Controller\Adminhtml\VendorGroupCommission
{
    
    public function execute()
    {
        $data = $this->getRequest()->getPostValue();
        if ($data) {
            $model = $this->vendorGroupCommissionFactory->create();
            $id = $this->getRequest()->getParam('group_commission_id');
            $oldCommValue = '';
            if ($id) {
                $model->load($id);
                $oldCommValue =  $model->getCommissionValue();
            }
            
            $model->setData($data);
            try {
                $model->save();
                $vcomm = $model->load($id);
                $eventParams = [
                    'old_commissions' => $oldCommValue,
                    'calculation_type' => $data['calculation_type'],
                    'commission_value' => $data['commission_value']
                ];
                $this->_eventManager->dispatch('vendor_group_commission_save', $eventParams);
                $this->messageManager->addSuccess(__('Vendor Group Commission has been saved.'));
                $this->_getSession()->setFormData(false);
                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', ['vendor_group_commission_id' => $model->getId(), '_current' => true]);
                    return;
                }
                $this->_redirect('*/*/');
                return;
            } catch (\Magento\Framework\Exception\AlreadyExistsException $e) {
                $this->messageManager->addException(
                    $e,
                    __('Commision with this vendor group already exists. Please try another vendor group.')
                );
            } catch (\RuntimeException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            }

            $this->_getSession()->setFormData($data);
            $this->_redirect(
                '*/*/edit',
                ['vendor_group_commission_id' => $this->getRequest()->getParam('vendor_group_commission_id')]
            );
            return;
        }
        $this->_redirect('*/*/');
    }
}
