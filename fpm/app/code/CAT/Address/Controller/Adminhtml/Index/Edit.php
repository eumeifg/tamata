<?php

namespace CAT\Address\Controller\Adminhtml\Index;

use CAT\Address\Controller\Adminhtml\City;

class Edit extends City
{
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');

        $model = $this->_objectManager->create('CAT\Address\Model\RomCity');

        if ($id) {
            $model->load($id);
            if (!$model->getId()) {
                $this->messageManager->addError(__('This item no longer exists.'));
                $this->_redirect('manage_city/index/*');
                return;
            }
        }
        // set entered data if was error when we do save
        $data = $this->_objectManager->get('Magento\Backend\Model\Session')->getPageData(true);
        if (!empty($data)) {
            $model->addData($data);
        }
        $this->_coreRegistry->register('current_address_city', $model);
        $this->_initAction();
        $this->_view->getLayout()->getBlock('items_items_edit');
        $this->_view->renderLayout();
    }
}
