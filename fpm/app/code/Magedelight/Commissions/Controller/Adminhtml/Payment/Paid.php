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
namespace Magedelight\Commissions\Controller\Adminhtml\Payment;

class Paid extends \Magento\Backend\App\Action
{
    /**
     * @return \Magento\Framework\View\Result\PageFactory
     */
    public function execute()
    {
        if ($this->getRequest()->getQuery('ajax')) {
            $this->_forward('grid');
            return;
        }
        $this->_view->loadLayout();

        /**
         * Set active menu item
         */
        $this->_setActiveMenu('Magedelight_Commissions::payment_paid');
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Vendor Payments - Paid'));

        /**
         * Add breadcrumb item
         */
        $this->_addBreadcrumb(__('Commissions'), __('Vendor Payments - Paid'));

        $this->_view->renderLayout();
    }
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magedelight_Commissions::paid');
    }
}
