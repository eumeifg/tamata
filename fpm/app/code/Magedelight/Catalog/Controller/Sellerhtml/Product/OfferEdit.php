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

use Magedelight\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class OfferEdit extends \Magedelight\Catalog\Controller\Sellerhtml\Product\AbstractProduct
{
    /**
     *
     * @param Context $context
     * @param \Magedelight\Vendor\Model\Design $design
     * @param PageFactory $resultPageFactory
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magedelight\Catalog\Model\ProductRequestFactory $productRequestFactory
     * @param \Magento\Eav\Api\AttributeManagementInterface $attributeManagementInterface
     * @param \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        Context $context,
        \Magedelight\Vendor\Model\Design $design,
        PageFactory $resultPageFactory,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magedelight\Catalog\Model\ProductRequestFactory $productRequestFactory,
        \Magento\Eav\Api\AttributeManagementInterface $attributeManagementInterface,
        \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        parent::__construct(
            $context,
            $design,
            $resultPageFactory,
            $coreRegistry,
            $scopeConfig,
            $productRequestFactory,
            $attributeManagementInterface,
            $categoryRepository,
            $storeManager
        );
    }

    /**
     * Vendor product landing page
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $this->design->applyVendorDesign();

        $productRequestId = (int) $this->getRequest()->getParam('id', false);
        if (!$productRequestId) {
            $this->messageManager->addError('Product Request not found.');
            $this->resultRedirect->setPath('rbcatalog/listing/', ['_current' => true]);
            return $this->resultRedirect;
        }

        $this->productRequest->load($productRequestId);

        if (!$this->productRequest->getId() ||
            $this->productRequest->getData('vendor_id') != $this->_auth->getUser()->getVendorId()) {
            $this->messageManager->addError('Product Request not found.');
            $this->resultRedirect->setPath('rbcatalog/listing/', ['_current' => true]);
            return $this->resultRedirect;
        }

        try {
            $this->_initCategory($this->productRequest->getData('main_category_id'));
        } catch (\Exception $e) {
            $this->messageManager->addError('No attribute set has added to selected category.');
            $this->resultRedirect->setPath('*/*/*/');
            return $this->resultRedirect;
        }
        $this->coreRegistry->register('vendor_current_product_request', $this->productRequest);
        /** @var \Magento\Framework\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->set(__('Product Attributes - Vital Info'));

        return $resultPage;
    }

    /**
     * Vendor access rights checking
     *
     * @return bool
     */
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
