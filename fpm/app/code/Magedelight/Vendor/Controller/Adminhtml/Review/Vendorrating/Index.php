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

class Index extends \Magento\Backend\App\Action
{
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magedelight_Vendor::vendorrating');
    }
    
    public function execute()
    {
        if ($this->getRequest()->getQuery('ajax')) {
            $this->_forward('grid');
            return;
        }
        $this->_view->loadLayout();
        $this->_setActiveMenu('Magedelight_Vendor::items');
        $this->_addBreadcrumb(__('Banner'), __('Banner'));
        $this->_addBreadcrumb(__('Manage Vendor Rating'), __('Manage Vendor Rating'));
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Vendor Rating'));
        $this->_view->renderLayout();
    }
}
