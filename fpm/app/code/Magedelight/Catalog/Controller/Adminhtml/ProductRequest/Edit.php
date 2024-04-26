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
namespace Magedelight\Catalog\Controller\Adminhtml\ProductRequest;

use Magedelight\Catalog\Model\ResourceModel\ProductRequestStore\CollectionFactory as ProductRequestStoreCollectionFactory;
use Magedelight\Catalog\Model\ResourceModel\ProductRequestWebsite\CollectionFactory as ProductRequestWebsiteCollectionFactory;

class Edit extends \Magedelight\Catalog\Controller\Adminhtml\ProductRequest
{

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \Magento\Catalog\Model\ProductRepository
     */
    protected $productRepository;

    protected $productRequest;

    /**
     * @var \Magento\Framework\Json\Decoder
     */
    protected $jsonDecoder;

    /**
     * @var \Magedelight\Vendor\Helper\Data
     */
    protected $vendorhelper;

    /**
     * @var \Magedelight\Catalog\Model\ResourceModel\ProductRequest\CollectionFactory
     */
    protected $_productRequestCollectionFactory;

    /**
     * @var ProductRequestStoreCollectionFactory
     */
    protected $_productRequestStoreCollectionFactory;

    /**
     * @var ProductRequestWebsiteCollectionFactory
     */
    protected $_productRequestWebsiteCollectionFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magedelight\Catalog\Model\ResourceModel\ProductWebsite\CollectionFactory
     */
    protected $_productWebsiteCollectionFactory;

    /**
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Catalog\Model\ProductRepository $productRepository
     * @param \Magento\Framework\Json\Decoder $jsonDecoder
     * @param \Magedelight\Vendor\Helper\Data $vendorhelper
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magedelight\Catalog\Model\ResourceModel\ProductRequest\CollectionFactory $productRequestCollectionFactory
     * @param ProductRequestWebsiteCollectionFactory $productRequestWebsiteCollectionFactory
     * @param ProductRequestStoreCollectionFactory $productRequestStoreCollectionFactory
     * @param \Magedelight\Catalog\Model\ResourceModel\ProductWebsite\CollectionFactory $productWebsiteCollectionFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        \Magento\Framework\Json\Decoder $jsonDecoder,
        \Magedelight\Vendor\Helper\Data $vendorhelper,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magedelight\Catalog\Model\ResourceModel\ProductRequest\CollectionFactory $productRequestCollectionFactory,
        ProductRequestWebsiteCollectionFactory $productRequestWebsiteCollectionFactory,
        ProductRequestStoreCollectionFactory $productRequestStoreCollectionFactory,
        \Magedelight\Catalog\Model\ResourceModel\ProductWebsite\CollectionFactory $productWebsiteCollectionFactory
    ) {
        $this->_coreRegistry = $coreRegistry;
        $this->resultPageFactory = $resultPageFactory;
        $this->productRepository = $productRepository;
        $this->jsonDecoder = $jsonDecoder;
        $this->vendorhelper = $vendorhelper;
        $this->_productRequestWebsiteCollectionFactory = $productRequestWebsiteCollectionFactory;
        $this->_productRequestStoreCollectionFactory = $productRequestStoreCollectionFactory;
        $this->_productRequestCollectionFactory = $productRequestCollectionFactory;
        $this->_storeManager = $storeManager;
        $this->_productWebsiteCollectionFactory = $productWebsiteCollectionFactory;
        parent::__construct($context);
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magedelight_Catalog::vendor_products_disapproved_edit');
    }

    /**
     * Edit Brand Page
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        $store = $this->getRequest()->getParam('store');

        if ($id) {
            $collection = $this->_productRequestCollectionFactory->create();
            $collection->getSelect()->joinLeft(
                ['mvprsl' => 'md_vendor_product_request_super_link'],
                'mvprsl.product_request_id = main_table.product_request_id',
                ['parent_id']
            );
            $this->productRequest = $collection->addFieldToFilter('main_table.product_request_id', $id)
                ->getFirstItem();

            $productData = $this->productRequest->getData();
            if (!$this->productRequest->getId()) {
                $this->messageManager->addError(__('This Product Request no longer exists.'));
                $this->_redirect('*/*/', ['existing' => $this->getRequest()->getParam('existing')]);
                return;
            }
            $collection = $this->_productRequestStoreCollectionFactory->create();
            $collection->addFieldToFilter('product_request_id', $this->productRequest->getProductRequestId())
            ->addFieldToFilter('store_id', $store);
            if ($store != 0) {
                $collection->addFieldToFilter(
                    'website_id',
                    $this->_storeManager->getStore($store)->getWebsiteId()
                );
            }
            $productRequestStore = $collection->getFirstItem();
            $this->productRequest->setData('store_id', $store);
            $attributesJSON = $productRequestStore->getData('attributes');
            if (is_string($attributesJSON)) {
                $attrValues = json_decode($attributesJSON, true);
                foreach ($attrValues as $key => $value) {
                    $this->productRequest->setData($key, $value);
                }
            }

            $storeValues = $productRequestStore->getData();
            foreach ($storeValues as $key => $value) {
                $this->productRequest->setData($key, $value);
            }

            $collection = $this->_productRequestWebsiteCollectionFactory->create();
            $productRequestWebsite = $collection->addFieldToFilter(
                'product_request_id',
                $this->productRequest->getProductRequestId()
            );
            if ($store != 0) {
                $collection->addFieldToFilter(
                    'website_id',
                    $this->_storeManager->getStore($store)->getWebsiteId()
                );
            }

            $productRequestWebsite = $collection->getFirstItem();
            $websiteValues = $productRequestWebsite->getData();
            foreach ($websiteValues as $key => $value) {
                $this->productRequest->setData($key, $value);
            }
        }
        $vendor = $this->vendorhelper->getVendorDetails(
            $this->productRequest->getData('vendor_id'),
            ['email'],
            ['business_name']
        );
        $this->productRequest->setEmail($vendor->getEmail());
        $this->productRequest->setBusinessName($vendor->getBusinessName());

        $productWebsitecollection = $this->_productWebsiteCollectionFactory->create();
        $productWebsite = $productWebsitecollection->addFieldToFilter(
            'vendor_product_id',
            $this->productRequest->getVendorProductId()
        );
        if ($store != 0) {
            $productWebsite->addFieldToFilter(
                'website_id',
                $this->_storeManager->getStore($store)->getWebsiteId()
            );
        }
        if ($productWebsite) {
            $productWebsite = $productWebsite->getFirstItem();
            $this->productRequest->setCanList($productWebsite->getStatus());
        }

        $this->_coreRegistry->register('vendor_product_request', $this->productRequest);

        $pname = $this->productRequest->getData('name');
        $pid = $pid = $this->productRequest->getData('marketplace_product_id', false);

        try {
            /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
            $resultPage = $this->_initAction();

            $resultPage->addBreadcrumb(
                $id ? __('Edit Product Request') : __('New Product Request'),
                $id ? __('Edit Product Request') : __('New Request')
            );
            if ($pid) {
                $product = $this->productRepository->getById($pid);
                $pname = $product->getName();
            }
            $resultPage->getConfig()->getTitle()->prepend(__($pname . ' (' . $vendor->getBusinessName() . ')'));
        } catch (\Exception $e) {
            $this->_objectManager->get(\Psr\Log\LoggerInterface::class)->critical($e);
            $this->messageManager->addError($e->getMessage());
            $this->_redirect('*/*/', ['existing' => $this->getRequest()->getParam('existing')]);
        }
        return $resultPage;
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
                ->addBreadcrumb(__('Vendor Product'), __('Vendor Product'))
                ->addBreadcrumb(__('Manage Request'), __('Manage Request'));
        return $resultPage;
    }
}
