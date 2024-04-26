<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Catalog
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Catalog\Controller\Adminhtml\Product;

class EditPost extends \Magedelight\Catalog\Controller\Adminhtml\Product
{

    /**
     * Edit Brand Page
     */
    public function execute()
    {
        $data = $this->getRequest()->getPost();

        if ($data) {
            $id = $this->getRequest()->getParam('vendor_product_id');
            $this->vendorProduct->load($id);
            if (!$this->vendorProduct->getId()) {
                $this->messageManager->addError(__('This Product no longer exists. '));
                $this->_redirect('*/*/');
                return;
            }

            /**
             * @todo Vendor product update data
             */

            try {
                $this->vendorProduct->save();
                $this->messageManager->addSuccess(__('The Product request has been disapproved.'));
            } catch (\Magento\Framework\Model\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\RuntimeException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addException($e, __('Something went wrong while saving the product request.'));
            }
            if ($this->getRequest()->getParam('back')) {
                $this->_objectManager->get(\Magento\Backend\Model\Session::class)->setFormData($data);
                $this->_redirect('*/*/edit', ['id' => $this->vendorProduct->getId(), '_current' => true]);
                return;
            }
            $this->_objectManager->get(\Magento\Backend\Model\Session::class)->setFormData(false);
        }
        $this->_redirect('*/*/');
    }
}
