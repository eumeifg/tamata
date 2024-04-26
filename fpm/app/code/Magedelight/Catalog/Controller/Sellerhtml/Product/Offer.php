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

class Offer extends \Magedelight\Catalog\Controller\Sellerhtml\Product\AbstractProduct
{
    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $_productRepository;

    /**
     * Offer constructor.
     * @param Context $context
     * @param \Magedelight\Vendor\Model\Design $design
     * @param PageFactory $resultPageFactory
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magedelight\Catalog\Model\ProductRequestFactory $productRequestFactory
     * @param \Magento\Eav\Api\AttributeManagementInterface $attributeManagementInterface
     * @param \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepositoryInterface
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
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepositoryInterface
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
        $this->_productRepository = $productRepositoryInterface;
    }

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
        $pid =   (int) $this->getRequest()->getParam('pid', false);
        try {
            $fastfind = array_intersect($this->_getCategoryId($pid), $this->_getCategoryInVendor());

            if (empty($fastfind)) {
                return $this->_noProductRedirect();
            }

            $this->_initCategory();
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            $this->messageManager->addError(__($e->getMessage()));
            $this->resultRedirect->setPath('*/*/*/');
            return $this->resultRedirect;
        } catch (\Exception $e) {
            $this->messageManager->addError(__('No attribute set has added to selected category.'));
            $this->resultRedirect->setPath('*/*/*/');
            return $this->resultRedirect;
        }

        /** @var \Magento\Framework\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->set(__('Product Attributes - Vital Info'));

        return $resultPage;
    }

    /**
     * @return string
     */
    protected function _getCategoryInVendor()
    {
        if ($this->_auth->isLoggedIn()) {
            return $this->_auth->getUser()->getCategoryIds();
        }
        return '';
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function _getCategoryId()
    {
        $pid =   (int) $this->getRequest()->getParam('pid', false);
        $product = $this->_productRepository->getById($pid);
        $cats = $product->getCategoryIds();
        if (empty($cats)) {
            return '';
        } else {
            return $cats;
        }
    }

    /**
     * Vendor access rights checking
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magedelight_Catalog::manage_products_select_sell');
    }
}
