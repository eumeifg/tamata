<?php

namespace CAT\Custom\Controller\Adminhtml\Customerfeedback;

class Form extends \CAT\Custom\Controller\Adminhtml\Customerfeedback
{
    /**
     * Customer feedback form
     *
     * @return void
     */
    public function execute()
    {
        $this->initCurrentCustomer();
        $this->_view->loadLayout();
        $this->_view->renderLayout();
    }
}
