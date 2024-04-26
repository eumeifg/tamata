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

use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Eav\Api\AttributeManagementInterface;
use Magento\Framework\View\Result\PageFactory;

abstract class AbstractProduct extends \Magedelight\Backend\App\Action
{

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    const XML_PATH_DEFAULT_ATTRIBUTE_SET = 'vendor_product/vital_info/attributeset';

    /**
     * @var AttributeManagementInterface
     */
    protected $attributeManagementInterface;

    /**
     * @var \Magento\Catalog\Api\CategoryRepositoryInterface
     */
    protected $categoryRepository;

    protected $_attributeSetId;

    /**
     * @var \Magento\Catalog\Api\Data\CategoryInterface
     */
    protected $_category;

    /**
     * @var \Magedelight\Catalog\Model\ProductRequest
     */
    protected $productRequest;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;

    /**
     * @var \Magedelight\Vendor\Model\Design
     */
    protected $design;

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    protected $resultRedirect;

    /**
     * AbstractProduct constructor.
     * @param \Magedelight\Backend\App\Action\Context $context
     * @param \Magedelight\Vendor\Model\Design $design
     * @param PageFactory $resultPageFactory
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magedelight\Catalog\Model\ProductRequestFactory $productRequestFactory
     * @param AttributeManagementInterface $attributeManagementInterface
     * @param \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        \Magedelight\Backend\App\Action\Context $context,
        \Magedelight\Vendor\Model\Design $design,
        PageFactory $resultPageFactory,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magedelight\Catalog\Model\ProductRequestFactory $productRequestFactory,
        AttributeManagementInterface $attributeManagementInterface,
        \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->design = $design;
        $this->coreRegistry = $coreRegistry;
        $this->scopeConfig = $scopeConfig;
        parent::__construct($context);
        $this->resultRedirect =  $this->resultRedirectFactory->create();
        $this->productRequest = $productRequestFactory->create();
        $this->categoryRepository = $categoryRepository;
        $this->attributeManagementInterface = $attributeManagementInterface;
        $this->_storeManager = $storeManager;
    }

    /**
     * Initialize requested category object
     *
     * @param null $categoryId
     * @return null
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function _initCategory($categoryId = null)
    {
        if ($categoryId === null) {
            $categoryId = (int) $this->getRequest()->getParam('cid', false);
        }
        $storeId = ($this->getRequest()->getParam('store', false)) ?
            $this->getRequest()->getParam('store') : $this->_storeManager->getStore()->getId();

        $this->_category = $category = $this->categoryRepository->get($categoryId, $storeId);
        if ($this->coreRegistry->registry('vendor_current_category')) {
            $this->coreRegistry->unregister('vendor_current_category');
            $this->coreRegistry->register('vendor_current_category', $category);
        } else {
            $this->coreRegistry->register('vendor_current_category', $category);
        }

        $attributeSetId = $this->_getCategoryAttriButeSet();

        $this->coreRegistry->registry('vendor_current_category')->setMdCategoryAttributeSetId($attributeSetId);
    }

    /**
     * Redirect if product failed to load
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    protected function _noProductRedirect()
    {
        if (!$this->getResponse()->isRedirect()) {
            $this->resultRedirect->setPath('*/*/category', ['tab'=>$this->getRequest()->getParam('tab')]);
            return $this->resultRedirect;
        }
    }

    /**
     * Retrive all attributes as per obtain attribute set of current select category.
     * Store attribute collection into registry
     */
    protected function _getAttributesByAttributeSet()
    {
        /** @var  $coll \Magento\Eav\Model\ResourceModel\Entity\Attribute\Collection */
        $this->attributeManagementInterface->getAttributes(
            ProductAttributeInterface::ENTITY_TYPE_CODE,
            $this->_attributeSetId
        );
        $this->coreRegistry->register('current_category_attributes', $this->attributeManagementInterface);
        return $this;
    }

    /**
     *
     * @return Category Default Attributeset Id
     */
    protected function _getCategoryDefaultAttributeSetId()
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;

        return $this->scopeConfig->getValue(self::XML_PATH_DEFAULT_ATTRIBUTE_SET, $storeScope);
    }

    /**
     *
     * @param type $categoryId
     * @return boolean
     */
    protected function _isAuthorisedCategory($categoryId)
    {
        if ($this->_auth->isLoggedIn()) {
            if (in_array($categoryId, $this->_auth->getUser()->getCategoryIds())) {
                return true;
            }
        }
        return false;
    }

    /**
     *
     * @param int $setId
     * @param int $categoryId
     * @return int $setId
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function _getAttributeSetIdRecursive($setId, $categoryId)
    {
        if (($setId === null) || $this->_storeManager->getStore()->getRootCategoryId() == $categoryId) {
            return $this->_getCategoryDefaultAttributeSetId();
        }
        if ((int)$setId > 0) {
            return $setId;
        } else {
            $this->_category =  $this->categoryRepository->get($categoryId);
            $setId = $this->categoryRepository->get($this->_category->getParentId())->getMdCategoryAttributeSetId();
            return $this->_getAttributeSetIdRecursive($setId, $this->_category->getParentId());
        }
    }

    /**
     * @return int
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function _getCategoryAttriButeSet()
    {
        return  $this->_attributeSetId = $this->_getAttributeSetIdRecursive(
            $this->_category->getMdCategoryAttributeSetId(),
            $this->_category->getId()
        );
    }
}
