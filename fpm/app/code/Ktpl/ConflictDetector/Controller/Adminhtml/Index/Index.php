<?php
/**
 * Copyright © Krish TechnoLabs, All rights reserved.
 */

namespace Ktpl\ConflictDetector\Controller\Adminhtml\Index;

/**
 * ConflictDetector list controller
 */
class Index extends \Magento\Backend\App\Action
{
     /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Ktpl_ConflictDetector::elements';

    /**
     * Action execute
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $this->_view->loadLayout();
        $this->_setActiveMenu('Ktpl_ConflictDetector::elements');
        $title = __('Conflict Detector (beta)');
        $this->_view->getPage()->getConfig()->getTitle()->prepend($title);
        $this->_addBreadcrumb($title, $title);
        $this->_view->renderLayout();
    }
}
