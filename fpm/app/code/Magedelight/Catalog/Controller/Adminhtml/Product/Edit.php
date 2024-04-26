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

class Edit extends \Magedelight\Catalog\Controller\Adminhtml\Product
{

    /**
     * @var \Magedelight\Vendor\Helper\Data
     */
    protected $vendorHelper;

    /**
     * @var \Magedelight\Catalog\Helper\Data
     */
    protected $helper;

    /**
     * Edit constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magedelight\Catalog\Model\ProductFactory $vendorProductFactory
     * @param \Magedelight\Catalog\Model\ResourceModel\Product $vendorProductResource
     * @param \Magedelight\Catalog\Model\ResourceModel\Product\CollectionFactory $collectionFactory
     * @param \Magento\Framework\App\CacheInterface $cacheManager
     * @param \Magedelight\Vendor\Helper\Data $vendorHelper
     * @param \Magedelight\Catalog\Helper\Data $helper
     * @param \Magedelight\Catalog\Api\ProductWebsiteRepositoryInterface $productWebsiteRepository
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magedelight\Catalog\Model\ProductFactory $vendorProductFactory,
        \Magedelight\Catalog\Model\ResourceModel\Product $vendorProductResource,
        \Magedelight\Catalog\Model\ResourceModel\Product\CollectionFactory $collectionFactory,
        \Magento\Framework\App\CacheInterface $cacheManager,
        \Magedelight\Vendor\Helper\Data $vendorHelper,
        \Magedelight\Catalog\Helper\Data $helper,
        \Magedelight\Catalog\Api\ProductWebsiteRepositoryInterface $productWebsiteRepository
    ) {
        $this->_cacheManager = $cacheManager;
        $this->vendorHelper = $vendorHelper;
        $this->helper = $helper;
        parent::__construct(
            $context,
            $coreRegistry,
            $resultPageFactory,
            $vendorProductFactory,
            $vendorProductResource,
            $collectionFactory,
            $cacheManager,
            $productWebsiteRepository
        );
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magedelight_Catalog::vendor_products_listed_edit');
    }

    /**
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');

        if ($id) {
            $this->vendorProduct->load($id);
            if (!$this->vendorProduct->getId()) {
                $this->messageManager->addError(__('This Product no longer exists. '));
            }
        }
        $this->_coreRegistry->register('vendor_product', $this->vendorProduct);
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->_initAction();
        $resultPage->addBreadcrumb(
            $id ? __('Edit Product') : __('New Product'),
            $id ? __('Edit Product') : __('New Product')
        );
        $resultPage->getConfig()->getTitle()->prepend(
            $this->getCustomPageTitle(
                $this->vendorProduct->getData('marketplace_product_id'),
                $this->vendorProduct->getData('vendor_id')
            )
        );

        return $resultPage;
    }

    /**
     *
     * @param string $productId
     * @param string $vendorId
     * @return \Magento\Catalog\Model\Product|string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function getCustomPageTitle($productId = '', $vendorId = '')
    {
        if ($productId && $vendorId) {
            $product = $this->helper->getCoreProduct($productId);
            $vendor = $this->vendorHelper->getVendorDetails($vendorId, ['email'], ['business_name']);
            return $product->getName() . ' (' . $vendor->getBusinessName() . ')';
        }
    }

    /**
     * Init actions
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    protected function _initAction()
    {
        // load layout, set active menu and breadcrumbs
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Magedelight_Catalog::product_request_manage')
                ->addBreadcrumb(__('Vendor'), __('Vendor'))
                ->addBreadcrumb(__('Manage Products'), __('Manage Products'));
        return $resultPage;
    }
}
