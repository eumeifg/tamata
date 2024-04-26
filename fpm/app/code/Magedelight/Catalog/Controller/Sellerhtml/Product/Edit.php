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

class Edit extends \Magedelight\Catalog\Controller\Sellerhtml\Product\AbstractProduct
{
    /**
     * Vendor product landing page
     *
     * @return \Magento\Framework\Controller\Result\Redirect|\Magento\Framework\View\Result\Page
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $this->design->applyVendorDesign();
        $productRequestId = (int) $this->getRequest()->getParam('id', false);
        if ($productRequestId) {
            $this->productRequest->load($productRequestId);
            if (!$this->productRequest->getId() ||
                $this->productRequest->getVendorId() != $this->_auth->getUser()->getVendorId()) {
                $this->messageManager->addError(__('This Product Request no longer exists. '));
                $this->_redirect('rbcatalog/listing', ['_current' => true]);
                return;
            }
            $categoryId = $this->productRequest->getData(('main_category_id'));
            $this->coreRegistry->register('vendor_current_product_request', $this->productRequest);
        } else {
            $categoryId = (int) $this->getRequest()->getParam('cid', false);
        }

        if (!$categoryId) {
            return $this->_noProductRedirect();
        }

        try {
            if (!$this->_isAuthorisedCategory($categoryId)) {
                throw new \Exception('Unauthorised action perform.');
            }
            $this->_initCategory($categoryId);

            $this->_getAttributesByAttributeSet();
        } catch (\Exception $e) {
            $this->messageManager->addError(__('No attribute set has added to selected category.'));
            return $resultRedirect->setPath('*/*/*');
        }

        if ($this->_request->getParam('store') &&
            !in_array($this->_request->getParam('store'), [$this->_storeManager->getStore()->getId()])) {
            return $resultRedirect->setPath(
                'rbcatalog/listing/index',
                ['tab' => '1,0', 'sfrm' => 'nl', 'vpro' => 'pending', '_secure' => true]
            );
        }

        $storeAndWebsiteName = '';
        if ($this->_request->getParam('store')) {
            $storeAndWebsiteName = '(';
            $storeAndWebsiteName .=  $this->_storeManager->getWebsite(
                $this->_storeManager->getStore($this->_storeManager->getStore()->getId())->getWebsiteId()
            )->getName();
            $storeAndWebsiteName .= '/' . $this->_storeManager->getStore(
                $this->_storeManager->getStore()->getId()
            )->getName();
            $storeAndWebsiteName .= ')';
        }

        /** @var \Magento\Framework\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()
            ->set(__($this->productRequest->getVendorSku() . ' ' . $storeAndWebsiteName));

        return $resultPage;
    }

    protected function _isAllowed()
    {
        $productRequestId = (int) $this->getRequest()->getParam('id', false);

        if ($productRequestId) {
            $this->productRequest->load($productRequestId);

            $requestStatus = (int)$this->productRequest->getData('status');           
        } 

        if($requestStatus === 0 && $this->_authorization->isAllowed('Magedelight_Catalog::manage_products_pending_view_edit')){
            return true;
        }
        if($requestStatus === 2 && $this->_authorization->isAllowed('Magedelight_Catalog::manage_products_disapproved_view_edit')){

            return true;

        }
        // return $this->_authorization->isAllowed('Magedelight_Catalog::manage_products_pending_view_edit');
    }
}
