<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Commissions
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Commissions\Controller\Adminhtml\Categorycommission;

use Magedelight\Commissions\Controller\Adminhtml\Commissions;

class Delete extends Commissions
{
    /**
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $id = $this->getRequest()->getParam('commission_id');
        if ($id) {
            try {
                /** @var \RB\Commission\Model\Commission $commission */
                $commission = $this->categoryCommissionRepository->deleteByIdentifier($id);
                /*$commission->load($id);
                $commission->delete();*/

                if ($commission) {
                    $this->messageManager->addSuccess(__('The commission has been deleted.'));
                }
                $resultRedirect->setPath('*/*/');

                return $resultRedirect;
            } catch (\Exception $e) {
                // display error message
                $this->messageManager->addError($e->getMessage());
                // go back to edit form
                $resultRedirect->setPath('*/*/edit', ['commission_id' => $id]);
                return $resultRedirect;

                //$this->messageManager->addError($e->getMessage());
            }
        }

        $this->_redirect('*/*/');
    }
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magedelight_Commissions::manage_delete');
    }
}
