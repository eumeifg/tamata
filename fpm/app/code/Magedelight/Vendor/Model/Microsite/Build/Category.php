<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Vendor
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Vendor\Model\Microsite\Build;

use Magedelight\Vendor\Api\Data\VendorInterface;
use Magedelight\Vendor\Api\Data\VendorProfileInterface;
use Magento\Framework\Data\Collection;
use Magento\Framework\Data\Tree\Node;
use Magento\Framework\Data\Tree\NodeFactory;
use Magento\Framework\Data\TreeFactory;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Build category data
 */
class Category extends \Magento\Framework\DataObject
{

    /**
     * Top menu data tree
     *
     * @var \Magento\Framework\Data\Tree\Node
     */
    protected $_menu;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magedelight\MobileInit\Model\MobileInitData
     */
    protected $mobileInitData;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory
     */
    protected $categoryCollectionFactory;

    /**
     * @var \Magedelight\MobileInit\Api\Data\MobileCategoryDataInterfaceFactory
     */
    protected $mobileCategoryDataFactory;

    /**
     * @var TreeFactory
     */
    protected $treeFactory;

    /**
     * @var NodeFactory
     */
    protected $nodeFactory;

    /**
     * Category constructor.
     * @param StoreManagerInterface $storeManager
     * @param \Magedelight\MobileInit\Model\MobileInitData $mobileInitData
     * @param \Magedelight\MobileInit\Api\Data\MobileCategoryDataInterfaceFactory $mobileCategoryDataFactory
     * @param \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory
     * @param NodeFactory $nodeFactory
     * @param TreeFactory $treeFactory
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        \Magedelight\MobileInit\Model\MobileInitData $mobileInitData,
        \Magedelight\MobileInit\Api\Data\MobileCategoryDataInterfaceFactory $mobileCategoryDataFactory,
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory,
        NodeFactory $nodeFactory,
        TreeFactory $treeFactory
    ) {
        $this->storeManager = $storeManager;
        $this->mobileInitData = $mobileInitData;
        $this->mobileCategoryDataFactory = $mobileCategoryDataFactory;
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        $this->nodeFactory = $nodeFactory;
        $this->treeFactory = $treeFactory;
    }

    /**
     * @param $vendor
     * @param int $storeId
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function build($vendor, $storeId)
    {
        /* Set categories data. */
        if ($vendor) {
            $this->buildCategoryTree($vendor, $storeId);
        }
    }

    /**
     * Retrieve Categories List
     * @param VendorInterface $vendor
     * @return array []
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getVendorCategories($vendor)
    {
        $categories = [];
        $vendorCategories = $vendor->getCategoryIds();
        $vendorCategoryCollection = $this->categoryCollectionFactory->create();
        $vendorCategoryCollection->addAttributeToSelect('path')
            ->addAttributeToFilter('entity_id', ['in' => $vendorCategories])
            ->load();
        foreach ($vendorCategoryCollection as $vendorCategory) {
            $categories = array_merge($categories, explode('/', $vendorCategory->getPath()));
        }
        return $categories;
    }

    /**
     * Get menu object.
     *
     * Creates \Magento\Framework\Data\Tree\Node root node object.
     * The creation logic was moved from class constructor into separate method.
     *
     * @return Node
     */
    public function getMenu()
    {
        if (!$this->_menu) {
            $this->_menu = $this->nodeFactory->create(
                [
                    'data' => [],
                    'idField' => 'root',
                    'tree' => $this->treeFactory->create()
                ]
            );
        }

        return $this->_menu;
    }

    /**
     * @param VendorInterface|VendorProfileInterface $vendor
     * @param $storeId
     * @param bool $skipContinue
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function buildCategoryTree($vendor, $storeId, $skipContinue = false)
    {
        $vendorCategories = $this->getVendorCategories($vendor);
        $data = [];

        $rootId = $this->storeManager->getStore()->getRootCategoryId();
        /** @var \Magento\Catalog\Model\ResourceModel\Category\Collection $collection */
        $collection = $this->getCategoryTree($storeId, $rootId);
        $mapping = [$rootId => $this->getMenu()];  /* use nodes stack to avoid recursion */

        foreach ($collection as $category) {
            if (!isset($mapping[$category->getParentId()])) {
                continue;
            }
            /** @var Node $parentCategoryNode */
            $parentCategoryNode = $mapping[$category->getParentId()];

            $categoryNode = new Node(
                $this->getCategoryAsArray($category),
                'id',
                $parentCategoryNode->getTree(),
                $parentCategoryNode
            );
            $parentCategoryNode->addChild($categoryNode);

            $mapping[$category->getId()] = $categoryNode; /* add node in stack */
        }
        $menuTree = $this->getMenu();

        foreach ($menuTree->getChildren() as $category) {
            if (!in_array($category->getId(), $vendorCategories) && !$skipContinue) {
                continue;
            }

            if ($category->getIsActive() && $category->getIncludeInMenu()) {
                $data[] = $this->getTree(
                    $category,
                    null,
                    0,
                    $vendorCategories,
                    $storeId,
                    $skipContinue
                );
            }
        }
        if ($skipContinue) {
            return $data;
        }
        $vendor->addCategoryItem($data);
    }

    /**
     * Get Category Tree
     *
     * @param int $storeId
     * @param int $rootId
     * @return \Magento\Catalog\Model\ResourceModel\Category\Collection
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function getCategoryTree($storeId, $rootId)
    {
        /** @var \Magento\Catalog\Model\ResourceModel\Category\Collection $collection */
        $collection = $this->categoryCollectionFactory->create();
        $collection->setStoreId($storeId);
        $collection->addAttributeToSelect(['name','small_image']);
        $collection->addAttributeToFilter('include_in_menu', '1');
        $collection->addFieldToFilter('path', ['like' => '1/' . $rootId . '/%']); /* load only from store root */
        $collection->addIsActiveFilter();
        $collection->addUrlRewriteToResult();
        $collection->addOrder('level', Collection::SORT_ORDER_ASC);
        $collection->addOrder('position', Collection::SORT_ORDER_ASC);
        $collection->addOrder('parent_id', Collection::SORT_ORDER_ASC);
        $collection->addOrder('entity_id', Collection::SORT_ORDER_ASC);

        return $collection;
    }

    /**
     * Convert category to array
     *
     * @param \Magento\Catalog\Model\Category $category
     * @return array
     */
    protected function getCategoryAsArray($category)
    {
        return [
            'name' => $category->getName(),
            'id' => $category->getId(),
            'is_active' => $category->getIsActive(),
            'small_image' => $category->getSmallImage(),
            'include_in_menu' => $category->getIncludeInMenu()
        ];
    }

    /**
     * @param $node
     * @param null $depth
     * @param int $currentLevel
     * @param array $vendorCategories
     * @param $storeId
     * @param bool $skipContinue
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getTree(
        $node,
        $depth = null,
        $currentLevel = 0,
        $vendorCategories = [],
        $storeId,
        $skipContinue = false
    ) {
        $children = $this->getChildren($node, $depth, $currentLevel, $vendorCategories, $storeId, $skipContinue);
        $tree = $this->mobileCategoryDataFactory->create();
        $store = $this->storeManager->getStore($storeId);
        $mediaUrl = $store->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'catalog/category/';
        $categoryIcon = $node->getSmallImage() ? $mediaUrl . $node->getSmallImage() : null;
        $tree->setCategoryId($node->getId())
            ->setCategoryLabel($node->getName())
            ->setCategoryIcon($categoryIcon)
            ->setChildrenData($children);
        if (in_array($node->getId(), $vendorCategories) && $skipContinue) {
            $tree->setIsSelected(true);
        }
        return $tree->getData();
    }

    /**
     * @param $node
     * @param $depth
     * @param $currentLevel
     * @param $vendorCategories
     * @param $storeId
     * @param bool $skipContinue
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function getChildren($node, $depth, $currentLevel, $vendorCategories, $storeId, $skipContinue = false)
    {
        if ($node->hasChildren()) {
            $children = [];
            foreach ($node->getChildren() as $child) {
                if (!in_array($child->getId(), $vendorCategories) && !$skipContinue) {
                    continue;
                }
                if ($depth !== null && $depth <= $currentLevel) {
                    break;
                }
                if ($child->getIsActive() && $child->getIncludeInMenu()) {
                    $children[] = $this->getTree(
                        $child,
                        $depth,
                        $currentLevel + 1,
                        $vendorCategories,
                        $storeId,
                        $skipContinue
                    );
                }
            }
            return $children;
        }

        return [];
    }
}
