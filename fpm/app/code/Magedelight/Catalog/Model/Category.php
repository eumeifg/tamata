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
namespace Magedelight\Catalog\Model;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;

class Category implements \Magedelight\Catalog\Api\SubCategoryInterface
{
    const XML_PATH_DISPLAY_ATUTHORISED_CATEGORY_ENABLE = 'vendor_product/authorised_category/enable';

    const XML_PATH_DISPLAY_CATEGORY_COUNT_ENABLE = 'vendor_product/authorised_category/counts_enable';

    const FLAG_LOAD_ALL_CATEGORIES = true;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Catalog\Api\CategoryRepositoryInterface
     */
    protected $_categoryRepository;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    public $url;

    /**
     * @var \Magedelight\Backend\Model\Auth\Session
     */
    protected $authSession;

    /**
     * @var CollectionFactory
     */
    protected $categoryCollectionFactory;
    protected $_categoryCollection = null;

    /**
     * @var CategoryRepositoryInterface
     */
    protected $categoryRepositoryInterface;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * Category constructor.
     * @param CategoryRepositoryInterface $categoryRepositoryInterface
     * @param CollectionFactory $categoryCollectionFactory
     * @param \Magento\Framework\UrlInterface $url
     * @param \Magedelight\Backend\Model\Auth\Session $authSession
     * @param CategoryRepositoryInterface $categoryRepository
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param Category\Tree $categoryTree
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        CategoryRepositoryInterface $categoryRepositoryInterface,
        CollectionFactory $categoryCollectionFactory,
        \Magento\Framework\UrlInterface $url,
        \Magedelight\Backend\Model\Auth\Session $authSession,
        \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magedelight\Catalog\Model\Category\Tree $categoryTree,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->categoryRepositoryInterface = $categoryRepositoryInterface;
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        $this->url = $url;
        $this->_categoryRepository = $categoryRepository;
        $this->scopeConfig = $scopeConfig;
        $this->categoryTree = $categoryTree;
        $this->storeManager = $storeManager;
        $this->authSession = $authSession;
    }

    /**
     * @return array|string
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getItems()
    {
        /** @var \Magento\Catalog\Model\ResourceModel\Category\Collection $collection */
        $rootCategoryId = $this->storeManager->getStore()->getRootCategoryId();
        $collection = $this->getCategoryCollection()
                ->addAttributeToSelect('name')
                ->addAttributeToFilter('entity_id', $rootCategoryId)->addRootLevelFilter()->load();

        if (self::FLAG_LOAD_ALL_CATEGORIES) {
            foreach ($collection as $category) {
                return $this->getChildCategories($category->getId(), $category->getLevel());
            }
        } else {
            return $this->getChildCategories(
                $collection->getFirstItem()->getId(),
                $collection->getFirstItem()->getLevel()
            );
        }
    }

    /**
     * Retrieve Categories List
     * @return string
     */
    public function getCategories()
    {
        return $this->getItems();
    }

    /**
     * Retrieve Allowed Categories List
     * @param null $id
     * @param int $level
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getChildCategories($id = null, $level = 0)
    {
        $html = '';
        $seperatorFlag = true;
        if ($id) {
            $categories = $this->categoryRepositoryInterface->get($id);

            $html .= '<div id="category_browse_card_' . $level . '" class="a-box card" >';
            $html .= '<div  class="card-container" >';
            $html .= '<div class="a-box-inner"> ';
            $html .= '<div class="scrollable-browse-elements"> ';
            $html .= '<div class="a-box-group">';

            if ($categories->getChildren()) {
                $seperatorFlag = ($level == 1) ? false : true;
                foreach ($categories->getChildrenCategories() as $_category) {
                    if ($_category->getIsActive()) {
                        if ($this->isDisplayAuthorisedCategories()) {
                            $authorizedCategory = $this->getAuthorizedCategoriesPath();
                            if (!in_array($_category->getId(), $authorizedCategory)) {
                                continue;
                            }
                            if ($this->isAuthorisedCategoryPath($_category->getId())) {
                                $html .= '<a class="a-spacing-mini" category-id="' . $_category->getId() . '" ';
                                $html .= 'data-browse-path="' . $_category->getName() . '" ';
                                $html .= 'data-level="' . $_category->getLevel() . '" ';
                                $html .= 'data-label="' . $_category->getName() . '"> ';
                                $count = '';
                                if ($_category->hasChildren()) {
                                    $html .= '<span class="arrow_right sprites"> </span>';
                                    if ($this->isDisplayCategoryCounts()) {
                                        $count = ' (' . count(explode(',', $_category->getChildren())) . ')';
                                    }
                                }
                                $html .= '<span class="browse_path_label"> ';
                                $html .= $_category->getName() . $count;
                                $html .= '</span>';
                                $html .= ' </a>';
                            }
                        } else {
                            $html .= '<a class="a-spacing-mini" category-id="' . $_category->getId() . '" ';
                            $html .= 'data-browse-path="' . $_category->getName() . '" ';
                            $html .= 'data-level="' . $_category->getLevel() . '" ';
                            $html .= 'data-label="' . $_category->getName() . '">';
                            $count = '';
                            if ($_category->hasChildren()) {
                                $html .= '<span class="arrow_right sprites"> </span>';
                                if ($this->isDisplayCategoryCounts()) {
                                    $count = ' (' . count(explode(',', $_category->getChildren())) . ')';
                                }
                            }
                            $html .= '<span class="browse_path_label"> ' . $_category->getName() . $count . '</span> ';
                            $html .= '</a>';
                        }
                    }
                }
            } else {
                $html .= '<div class="cat-selection">';
                $html .= '<div class="category-name"> ' . $categories->getName() . ' </div>';
                $html .= '<div class="actions-toolbar">';
                if ($this->isAuthorisedCategory($categories->getId())) {
                    $html .= '<div class="primary">
                                    <a href="' . $this->url->getUrl('*/*/create/', ['cid' => $categories->getId(), 'tab' => "1,1"]) . '" class="action create primary button-l"><span>' . __('Select') . '</span></a>
                                 </div>';
                } else {
                    $html .= '<div class="message">
                                    <span>' . __(' You are not allowed to sell under this category.') . '</span>
                                 </div>';
                }
                $html .= '</div>';
                $html .= '</div>';
            }
            $html .= '</div>';
            $html .= '</div>';
            $html .= '</div>';
            if ($seperatorFlag) {
                $html .= '<div class="card-seperator">';
                $html .= '<span class="rbvt-icon rbvt-icon-arrow-long-right1"></span></div>';
            }
            $html .= '</div>';
            $html .= '</div>';
        } else {
            $html .= __('None');
        }
        return $html;
    }

    /**
     *
     * @param type $categoryId
     * @return boolean
     */
    public function isAuthorisedCategory($categoryId)
    {
        if ($this->authSession->isLoggedIn()) {
            if (in_array($categoryId, $this->authSession->getUser()->getCategoryIds())) {
                return true;
            }
        }
        return false;
    }

    /**
     * Retrieve Allowed Categories List
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getAllowedCategories()
    {
        /** @var \Magento\Catalog\Model\ResourceModel\Category\Collection $collection */
        $collection = $this->getCategoryCollection();

        $collection->addAttributeToSelect('name')->addRootLevelFilter()->load();

        foreach ($collection as $category) {
            return $this->getAllowedChildCategories($category->getId(), $category->getLevel());
        }
    }

    /**
     * @param null $id
     * @param int $level
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getAllowedChildCategories($id = null, $level = 0)
    {
        $html = '';
        $seperatorFlag = true;

        if ($id) {
            $categories = $this->categoryRepositoryInterface->get($id);

            $html .= '<select  multiple  name="attribute-name" id="attribute-name" class="attribute-name">';

            if ($categories->getChildren()) {
                ($level == 1) ? $seperatorFlag = false : $seperatorFlag = true;

                foreach ($categories->getChildrenCategories() as $_category) {
                    if ($_category->getIsActive()) {
                        $html .= '<option id="' . $_category->getId() . '" class="a-spacing-mini"  ';
                        $html .= 'data-browse-path="' . $_category->getName() . '" data-level="';
                        $html .= $_category->getLevel() . '" ';
                        $html .= 'data-label="' . $_category->getName() . '">' . $_category->getName() . '</option> ';
                        $count = '';
                    }
                }
            }
            $html .= '</select>';

            if ($seperatorFlag) {
                $html .= '<div class="card-seperator">';
                $html .= '<span class="rbvt-icon rbvt-icon-arrow-long-right1"></span></div>';
            }
        } else {
            $html .= __('None');
        }
        return $html;
    }

    /**
     * @param $categoryId
     * @return bool|int
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function isAuthorisedCategoryPath($categoryId)
    {
        $categoriesIds = [];
        foreach ($this->authSession->getUser()->getCategoryIds() as $cid) {
            $categoriesIds[] = $this->_categoryRepository->get($cid)->getPathIds();
        }

        $c = 0;
        foreach ($categoriesIds as $value) {
            if (false !== array_search($categoryId, $value)) {
                $c++;
            }
        }

        if ($categoriesIds) {
            $vendorCategories = array_unique(call_user_func_array('array_merge', $categoriesIds));
            if (in_array($categoryId, $vendorCategories)) {
                return $c;
            }
        }
        return false;
    }

    /**
     * @param null $id
     * @param int $level
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getAllowedTabChildCategories($id = null, $level = 0)
    {
        if ($id) {
            $root_cat_data = [];
            $categories = $this->categoryRepositoryInterface->get($id);
            if ($categories->getChildren()) {
                foreach ($categories->getChildrenCategories() as $_category) {
                    if ($_category->getIsActive()) {
                        $root_cat_data [$_category->getId()] = $_category->getName();
                    }
                }
            }
        }

        return $root_cat_data;
    }

    /**
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getAllowedTabCategories()
    {
        /** @var \Magento\Catalog\Model\ResourceModel\Category\Collection $collection */
        $collection = $this->getCategoryCollection();

        $collection->addAttributeToSelect('name')->addRootLevelFilter()->load();

        foreach ($collection as $category) {
            return $this->getAllowedTabChildCategories($category->getId(), $category->getLevel());
        }
    }

    /**
     * @param null $id
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getAllowedTabChildcategoriesnames($id = null)
    {
        $subchild_data = [];
        $categories = $this->categoryRepositoryInterface->get($id);
        if ($categories->getChildren()) {
            foreach ($categories->getChildrenCategories() as $_category) {
                if ($_category->getIsActive()) {
                    $subchild_data [$_category->getId()] = $_category->getName();
                }
            }
        }
        return $subchild_data;
    }

    /**
     *
     * @return boolean true | false
     */
    public function isDisplayAuthorisedCategories()
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        return $this->scopeConfig->getValue(self::XML_PATH_DISPLAY_ATUTHORISED_CATEGORY_ENABLE, $storeScope);
    }

    /**
     *
     * @return boolean true | false
     */
    public function isDisplayCategoryCounts()
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        return $this->scopeConfig->getValue(self::XML_PATH_DISPLAY_CATEGORY_COUNT_ENABLE, $storeScope);
    }

    /**
     * To option array
     *
     * @return array
     */
    public function getCategoryTreeArray($skipRoot = false)
    {
        $_categoryTree = $this->getCategoryTree($skipRoot);
        $_options = [];
        foreach ($_categoryTree['result']['children_data'] as $category) {
            if ($this->isAuthorisedCategory($category['id'])) {
                $_options[] = $this->toOptionIdArray($category);
            }
            foreach ($this->getChildren($category, '') as $_categoryChild) {
                if (!$this->isAuthorisedCategory($_categoryChild['value'])) {
                    continue;
                }
                $_options[] = $_categoryChild;
            }
        }

        return $_options;
    }

    /**
     * @param array $category
     * @param string $currentLevel
     * @return array
     */
    protected function getChildren($category, $currentLevel)
    {
        if (!empty($category['children_data'])) {
            $children = [];
            foreach ($category['children_data'] as $child) {
                $children[] = $this->toOptionIdArray($child, $currentLevel . '--');
                if (!empty($child['children_data'])) {
                    foreach ($this->getChildren($child, $currentLevel . '--') as $_child) {
                        $children[] = $_child;
                    }
                }
            }
            return $children;
        }
        return [];
    }

    /**
     * @param array $category
     * @param string $currentLevel
     * @return array
     */
    protected function toOptionIdArray($category, $currentLevel = '')
    {
        return [
            'label' => $currentLevel . $category['name'] . ' (ID:' . $category['entity_id'] . ') ',
            //' ('.$category['product_count'].')',
            'value' => $category['entity_id']
        ];
    }

    /**
     *
     * @param bool $skipRoot
     * @return \Magento\Catalog\Api\Data\CategoryTreeInterface
     */
    public function getCategoryTree($skipRoot = false)
    {
        return $this->categoryTree->getTree(
            $this->categoryTree->getRootNode(
                $this->getTopLevelCategory($skipRoot)
            ),
            null
        );
    }

    /**
     * Get top level hidden root category
     *
     * @return \Magento\Catalog\Model\Category|\Magento\Framework\DataObject
     */
    private function getTopLevelCategory($skipRoot = false)
    {
        $level = ($skipRoot) ? 1 : 0;
        $categoriesCollection = $this->getCategoryCollection();
        return $categoriesCollection->addFilter('level', ['eq' => $level])->getFirstItem();
    }

    /**
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getAuthorizedCategoriesPath()
    {
        $authorizedCategories = $this->authSession->getUser()->getCategoryIds();
        $categories = [];
        foreach ($authorizedCategories as $categoryId) {
            $categories =  array_merge(
                $categories,
                explode('/', $this->categoryRepositoryInterface->get($categoryId)->getPath())
            );
        }

        return array_unique($categories);
    }

    /**
     *
     * @return \Magento\Catalog\Model\ResourceModel\Category\Collection|null
     */
    public function getCategoryCollection()
    {
        if (!$this->_categoryCollection) {
            $this->_categoryCollection = $this->categoryCollectionFactory->create();
        }
        return $this->_categoryCollection;
    }
}
