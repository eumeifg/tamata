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
namespace Magedelight\Catalog\Controller\Sellerhtml\Bulkimport;

use Magedelight\Catalog\Helper\Bulkimport\Data as DataHelper;

abstract class Category extends \Magedelight\Backend\App\Action
{
    const XML_PATH_EXCLUDE_ATTRIBUTES = 'vendor_product/vital_info/attributes';
    const XML_PATH_DEFAULT_ATTRIBUTE_SET = 'vendor_product/vital_info/attributeset';

    /**
     * @var \Magento\Catalog\Api\Data\CategoryInterface
     */
    protected $_category;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;

    /**
     * @var DataHelper
     */
    protected $dataHelper;

    /**
     * @var \Magento\Eav\Model\ResourceModel\Entity\Attribute
     */
    protected $eavAttribute;

    /**
     * @var \Magento\Catalog\Api\CategoryRepositoryInterface
     */
    protected $categoryRepository;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Eav\Model\Config
     */
    protected $eavConfig;

    /**
     * @var \Magedelight\Vendor\Model\Design
     */
    protected $design;

    /**
     * @var \Magento\Framework\App\Response\Http\FileFactory
     */
    protected $fileFactory;

    /**
     * @param \Magedelight\Backend\App\Action\Context $context
     * @param \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param DataHelper $dataHelper
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute $eavAttribute
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Framework\App\Response\Http\FileFactory $fileFactory
     */
    public function __construct(
        \Magedelight\Backend\App\Action\Context $context,
        \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Eav\Model\Config $eavConfig,
        DataHelper $dataHelper,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute $eavAttribute,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory
    ) {
        $this->categoryRepository = $categoryRepository;
        $this->_storeManager = $storeManager;
        $this->eavConfig = $eavConfig;
        $this->_eavAttribute = $eavAttribute;
        $this->_bulkimportHelper = $dataHelper;
        $this->coreRegistry = $coreRegistry;
        $this->_objectManager = $context->getObjectManager();
        $this->fileFactory = $fileFactory;
        parent::__construct($context);
    }

    /**
     * Initialize requested category object
     *
     * @param null $categoryId
     * @return void
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function _initCategory($categoryId = null)
    {
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

    /**
     *
     * @param int $setId
     * @param int $categoryId
     * @return int $setId
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function _getAttributeSetIdRecursive($setId, $categoryId)
    {
        if ($setId === null) {
            return $this->_getCategoryDefaultAttributeSetId();
        }
        if (intval($setId) > 0) {
            return $setId;
        } else {
            $this->_category =  $this->categoryRepository->get($categoryId);
            $setId = $this->categoryRepository->get($this->_category->getParentId())->getMdCategoryAttributeSetId();
            return $this->_getAttributeSetIdRecursive($setId, $this->_category->getParentId());
        }
    }

    /**
     *
     * @return Category Default Attributeset Id
     */
    protected function _getCategoryDefaultAttributeSetId()
    {
        return $this->_bulkimportHelper->getConfigValue(self::XML_PATH_DEFAULT_ATTRIBUTE_SET);
    }

    /**
     * @param null $attribute_set_id
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function getAttributesByAttributeSet($attribute_set_id = null)
    {
        $categoryAttributes = [];
        /** @var  $coll \Magento\Eav\Model\ResourceModel\Entity\Attribute\Collection */

        $eavAttributeCollection = $this->eavConfig->getEntityType(
            \Magento\Catalog\Model\Product::ENTITY
        )->getAttributeCollection();

        /*$eavAttributeCollection->addFieldToFilter('is_visible', ['eq' => '1']);*/
        /* $eavAttributeCollection->addFieldToFilter('is_user_defined', ['eq' => '1']); */

        $excludeAttributeList = $this->getExcludeAttributeList();
        $eavAttributeCollection->addFieldToFilter('main_table.attribute_id', ['nin' => $excludeAttributeList]);

        if ($attribute_set_id) {
            $eavAttributeCollection->setAttributeSetFilter($attribute_set_id);
        } else {
            $eavAttributeCollection->setAttributeSetFilter(
                $this->coreRegistry->registry('vendor_current_category')->getMdCategoryAttributeSetId()
            );
        }
        /*eavAttributes = $eavAttributeCollection->load();*/
        $eavAttributeCollection->load();
        foreach ($eavAttributeCollection as $eavAttributes) {
            $categoryAttributes[$eavAttributes->getAttributeId()] = $eavAttributes->getAttributeCode();
        }

        return $categoryAttributes;
    }

    /**
     * @return array
     */
    public function getExcludeAttributeList()
    {
        $collection = $this->_bulkimportHelper->getConfigValue(self::XML_PATH_EXCLUDE_ATTRIBUTES);
        return explode(',', $collection);
    }

    /**
     * @param $attributeCode
     * @return int
     */
    protected function getAttriButeIdByCode($attributeCode)
    {
        return $attributeId = $this->_eavAttribute->getIdByCode('catalog_product', $attributeCode);
    }

    /**
     * Vendor access rights checking
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magedelight_Catalog::manage_products');
    }
}
