<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_VendorPromotion
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\VendorPromotion\Controller\Sellerhtml\SalesRule;

class CategoriesJson extends \Magedelight\Backend\App\Action
{
    /**
     * @var \Magento\Framework\Controller\Result\RawFactory
     */
    protected $resultRawFactory;

    /**
     * @var \Magento\Catalog\Model\CategoryFactory
     */
    protected $categoryFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;

    /**
     * @var \Magento\Store\Api\StoreManagementInterface
     */
    protected $storeManager;

    /**
     * @var \Magedelight\Vendor\Helper\Data
     */
    protected $helperVendor;

    /**
     *
     * @param \Magedelight\Backend\App\Action\Context $context
     * @param \Magento\Store\Api\StoreManagementInterface $storeManager
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Catalog\Model\CategoryFactory $categoryFactory
     * @param \Magento\Framework\Controller\Result\RawFactory $resultRawFactory
     */
    public function __construct(
        \Magedelight\Backend\App\Action\Context $context,
        \Magento\Store\Api\StoreManagementInterface $storeManager,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        \Magento\Framework\Controller\Result\RawFactory $resultRawFactory
    ) {
        $this->storeManager = $storeManager;
        $this->coreRegistry = $coreRegistry;
        $this->categoryFactory = $categoryFactory;
        $this->resultRawFactory = $resultRawFactory;
        
        parent::__construct($context);
    }
    
    public function execute()
    {
        $this->_vendorHelper->setDesignStore(0, 'adminhtml');
        $categoryId = (int) $this->getRequest()->getPost('id');
        if ($categoryId) {
            $this->getRequest()->setParam('id', $categoryId);

            if (!$category = $this->_initCategory()) {
                return;
            }
            return $this->resultRawFactory->create()->setContents(
                $this->_view->getLayout()->createBlock('\Magento\Catalog\Block\Adminhtml\Category\Tree')
                    ->getTreeJson($category)
            );
        }
    }
    
    protected function _initCategory()
    {
        $categoryId = (int) $this->getRequest()->getParam('id', false);
        $storeId    = (int) $this->getRequest()->getParam('store');

        $category   = $this->categoryFactory->create();
        $category->setStoreId($storeId);

        if ($categoryId) {
            $category->load($categoryId);
            if ($storeId) {
                $rootId = $this->storeManager->getStore($storeId)->getRootCategoryId();
                if (!in_array($rootId, $category->getPathIds())) {
                    $this->_redirect('*/*/', ['_current'=>true, 'id'=>null]);
                    return false;
                }
            }
        }

        $this->coreRegistry->register('category', $category);
        $this->coreRegistry->register('current_category', $category);

        return $category;
    }
    
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magedelight_Vendor::promotion');
    }
}
