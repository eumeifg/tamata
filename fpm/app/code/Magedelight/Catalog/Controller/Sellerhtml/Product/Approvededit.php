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

class Approvededit extends \Magedelight\Catalog\Controller\Sellerhtml\Product\AbstractProduct
{

    /**
     * @var \Magento\Framework\UrlFactory
     */
    protected $urlFactory;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var \Magedelight\Catalog\Model\ProductFactory
     */
    protected $_vendorProductFactory;

    /**
     *
     * @param \Magedelight\Backend\App\Action\Context $context
     * @param \Magedelight\Vendor\Model\Design $design
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magedelight\Catalog\Model\ProductRequestFactory $productRequestFactory
     * @param \Magento\Eav\Api\AttributeManagementInterface $attributeManagementInterface
     * @param \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magedelight\Catalog\Model\ProductFactory $vendorProductFactory
     * @param \Magedelight\Catalog\Model\ProductWebsiteFactory $vendorWebsiteProductFactory
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Magento\Framework\UrlFactory $urlFactory
     */
    public function __construct(
        \Magedelight\Backend\App\Action\Context $context,
        \Magedelight\Vendor\Model\Design $design,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magedelight\Catalog\Model\ProductRequestFactory $productRequestFactory,
        \Magento\Eav\Api\AttributeManagementInterface $attributeManagementInterface,
        \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magedelight\Catalog\Model\ProductFactory $vendorProductFactory,
        \Magedelight\Catalog\Model\ProductWebsiteFactory $vendorWebsiteProductFactory,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Framework\UrlFactory $urlFactory
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
        $this->urlFactory = $urlFactory->create();
        $this->productRepository = $productRepository;
        $this->_vendorProductFactory = $vendorProductFactory;
        $this->_vendorProductWebsiteFactory = $vendorWebsiteProductFactory;
    }

    /**
     * Vendor product landing page
     *
     * @return \Magento\Framework\View\Result\Page
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute()
    {
      
        $this->design->applyVendorDesign();
        $vendorProductId = (int) $this->getRequest()->getParam('id', false);
        $isSuper = (int) $this->getRequest()->getParam('super', false);
        $storeId = $this->_storeManager->getStore()->getId();

        if ($this->_request->getParam('store') && !in_array(
            $this->_request->getParam('store'),
            [$this->_storeManager->getStore()->getId()]
        )) {
            $this->_redirect('rbcatalog/listing', ['_current' => true]);
        }

        if ($vendorProductId) {
            $categoryId = false;
            $attributeSetId = false;
            $vendorProduct = $this->_vendorProductFactory->create()->load($vendorProductId);
            if (!$vendorProduct->getId() || $vendorProduct->getVendorId() != $this->_auth->getUser()->getVendorId()) {
                $this->messageManager->addError(__('This Product no longer exists.'));
                $this->_redirect('rbcatalog/listing', ['_current' => true]);
                return;
            }

            if ($requestId = $this->_getDuplicateRequestId(
                $vendorProduct->getVendorId(),
                $vendorProduct->getVendorSku(),
                $storeId
            )) {
                $queryParams = [
                    'p' => $this->getRequest()->getParam('p', 1),
                    'sfrm' => $this->getRequest()->getParam('sfrm', 'l'),
                    'limit' => $this->getRequest()->getParam('limit', 10),
                ];
                $editUrl = $this->urlFactory->getUrl(
                    'rbcatalog/product/edit',
                    ['id' => $requestId, 'tab' => '1,0', 'store' => $storeId, '_query' => $queryParams]/* Added here store id so that when it load the data the correct store data is loaded. */
                );
                $this->messageManager->addError(__('An edit request for this product is already exist.
                        <a href="%1"> Click here to edit existing request.</a>', $editUrl));
                $this->_redirect('rbcatalog/listing', ['_current' => true]);
                return;
            }

            $marketplaceProductId = ($isSuper) ? $vendorProduct->getParentId() :
            $vendorProduct->getMarketplaceProductId();
            $productModel = $this->productRepository->getById($marketplaceProductId, true, $storeId);
            $vendorProductWebsite = $this->_vendorProductWebsiteFactory->create()->getCollection()
                ->addFieldToFilter('vendor_product_id', $vendorProduct->getId())
                ->addFieldToFilter('website_id', $this->_storeManager->getStore()->getWebsiteId())
                ->getFirstItem();
            $categoryId = $vendorProductWebsite->getCategoryId();
            $category = $this->categoryRepository->get($categoryId, $this->_storeManager->getStore()->getId());
            $this->_attributeSetId = $attributeSetId = $category->getAttributeSetId();
            $this->coreRegistry->register('vendor_current_product_core', $productModel);
            $this->coreRegistry->register('vendor_current_product', $vendorProduct);

            if (empty($categoryId) || $categoryId < 1) {
                $this->messageManager->addError('No Category assigned to this product.');
                return $this->_noProductRedirect();
            }

            try {
                if (!$this->_isAuthorisedCategory($categoryId) || !$attributeSetId) {
                    throw new \Exception('Unauthorised action perform.');
                }
                $this->_initCategory($categoryId);

                if ($this->coreRegistry->registry('vendor_current_category')
                    ->getCategoryAttributeSetId() === null) {
                    $this->coreRegistry->registry('vendor_current_category')
                        ->setCategoryAttributeSetId($attributeSetId);
                }

                $this->_getAttributesByAttributeSetById();
            } catch (\Exception $e) {
                $this->messageManager->addError('No attribute set has added to selected category.');
                $this->resultRedirect->setPath('*/*/*/');
                return $this->resultRedirect;
            }
        } else {
            $this->resultRedirect->setPath('rbcatalog/listing/index/tab/1,0/');
            return $this->resultRedirect;
        }

        $storeAndWebsiteName = '';
        if ($this->_request->getParam('store')) {
            $storeAndWebsiteName = '(';
            $storeAndWebsiteName .= $this->_storeManager->getWebsite(
                $this->_storeManager->getStore($this->_storeManager->getStore()->getId())->getWebsiteId()
            )->getName();
            $storeAndWebsiteName .= '/' . $this->_storeManager->getStore(
                $this->_storeManager->getStore()->getId()
            )->getName();
            $storeAndWebsiteName .= ')';
        }

        /** @var \Magento\Framework\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->set($vendorProduct->getVendorSku() . ' ' . $storeAndWebsiteName);

        return $resultPage;
    }

    /**
     * Retrive all attributes as per obtain attribute set of current select category.
     * Store attribute collection into registry
     * @return Object
     */
    protected function _getAttributesByAttributeSetById()
    {
        $eavAttributeCollection = $this->_objectManager->create(
            \Magento\Eav\Model\ResourceModel\Entity\Attribute\Collection::class
        );
        $eavAttributeCollection->setAttributeSetFilter($this->_attributeSetId);
        $attributeCollection = $eavAttributeCollection->load()->getItems();
        $this->coreRegistry->register('current_category_attributes', $attributeCollection);
        return $this;
    }

    /**
     * @param $vendorId
     * @param $vendorSku
     * @param int $storeId
     * @param null $marketplaceProductId
     * @param bool $isSuper
     * @return bool
     */
    protected function _getDuplicateRequestId(
        $vendorId,
        $vendorSku,
        $storeId = 0,
        $marketplaceProductId = null,
        $isSuper = false
    ) {
        if ($isSuper) {
            $collection = $this->productRequest->getCollection()
                ->addFieldToFilter('vendor_id', $vendorId)
                ->addFieldToFilter('marketplace_product_id', $marketplaceProductId);
            if (count($collection)) {
                return $collection->getFirstItem()->getId();
            }
        } else {
            $collection = $this->productRequest->getCollection()
                ->addFieldToFilter('vendor_id', $vendorId)
                ->addFieldToFilter('vendor_sku', $vendorSku);
            if (count($collection)) {
                return $collection->getFirstItem()->getId();
            }
        }

        return false;
    }

     /**
     * Vendor access rights checking
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magedelight_Catalog::manage_products_live_view_edit');
    }
}
