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
namespace Magedelight\Commissions\Controller\Adminhtml\Vendorcommission;

class Index extends \Magento\Backend\App\Action
{
    public function execute()
    {
        if ($this->getRequest()->getQuery('ajax')) {
            $this->_forward('grid');
            return;
        }
        $this->_view->loadLayout();
        $this->_setActiveMenu('Magedelight_Commissions::vendorcommission');
        $this->_addBreadcrumb(__('Vendor commission'), __('Vendor commission'));
        $this->_addBreadcrumb(__('Manage Vendor commission'), __('Manage Vendor commission'));
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Vendor commission'));
        $this->_view->renderLayout();
    }
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magedelight_Commissions::manage');
    }
}
