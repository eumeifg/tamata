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
namespace Magedelight\Catalog\Controller\Sellerhtml\Product;

class Create extends \Magedelight\Catalog\Controller\Sellerhtml\Product\AbstractProduct
{

    /**
     * Vendor product landing page
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $this->design->applyVendorDesign();

        $categoryId = (int) $this->getRequest()->getParam('cid', false);

        if (!$categoryId) {
            return $this->_noProductRedirect();
        }

        try {
            if (!$this->_isAuthorisedCategory($categoryId)) {
                $this->messageManager->addError(__('Sorry, Unauthorised action perform.'));
                $this->resultRedirect->setPath('*/*/*/');
                return $this->resultRedirect;
            }
            $this->_initCategory();
        } catch (\Exception $e) {
            $this->messageManager->addError(__('No attribute set has added to selected category.'));
            $this->resultRedirect->setPath('*/*/*/');
            return $this->resultRedirect;
        }

        /** @var \Magento\Framework\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->set(__('New Product Request'));

        return $resultPage;
    }
    
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magedelight_Catalog::manage_products');
    }
}
