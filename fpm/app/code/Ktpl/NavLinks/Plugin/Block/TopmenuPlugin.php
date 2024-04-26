<?php
/*
 * Copyright © 2018 Krish Technolabs. All rights reserved.
 * See COPYING.txt for license details
 */

namespace Ktpl\NavLinks\Plugin\Block;

use Magento\Framework\Data\Tree;
use Magento\Framework\Data\Tree\Node;
use Magento\Framework\Data\Tree\NodeFactory;
use Magento\Framework\Data\TreeFactory;
use Ktpl\NavLinks\Helper\Data;

class TopmenuPlugin
{

    /**
     * @var Data
     */
    private $helper;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var NodeFactory
     */
    protected $nodeFactory;

    /**
     * @var TreeFactory
     */
    protected $treeFactory;

    /**
     * Topmenu constructor.
     * @param NodeFactory $nodeFactory
     * @param TreeFactory $treeFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        NodeFactory $nodeFactory,
        TreeFactory $treeFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        Data $helper
    ) {
        $this->nodeFactory = $nodeFactory;
        $this->treeFactory = $treeFactory;
        $this->storeManager = $storeManager;
        $this->helper = $helper;
    }

    /**
     * 
     * @param \Magento\Theme\Block\Html\Topmenu $subject
     * @param type $outermostClass
     * @param type $childrenWrapClass
     * @param type $limit
     */
    public function beforeGetHtml(
        \Magento\Theme\Block\Html\Topmenu $subject,
        $outermostClass = 'parent-class-test',
        $childrenWrapClass = 'custom-children',
        $limit = 0
    ) {
        /** @var Node $menu */
        $menu = $subject->getMenu();

        /** @var Tree $menuTree */
        $menuTree = $menu->getTree();

        /** move categories under second level ***/
        $categoriesNode = $this->getCategoriesNode($menuTree);

        $menuNodesCollection = $menu->getChildren();
        /** @var Node $categoryMenuItemNode */
        foreach ($menuNodesCollection as $categoryMenuItemNode) {
            $categoriesNode->addChild(clone $categoryMenuItemNode);
            $menuNodesCollection->delete($categoryMenuItemNode);
        }
        $menu = $this->addBeforeNodes($menu, $menuTree);

        foreach ($categoriesNode->getChildren() as $categoriesItemNode) {
            $menu->addChild(clone $categoriesItemNode);
        }
        $menu = $this->addAfterNodes($menu, $menuTree);
    }

    /**
     * Add before menu items.
     * @param Node $menu
     * @param Tree $tree
     * @return Node
     */
    protected function addBeforeNodes($menu, Tree $tree)
    {
        $beforeMenuArray = $this->getBeforeMenuArray();
        ksort($beforeMenuArray);
        $menu = $this->addNodes($menu, $tree, $beforeMenuArray);
        return $menu;
    }

    /**
     * Add After menu items.
     * @param Node $menu
     * @param Tree $tree
     * @return Node
     */
    protected function addAfterNodes($menu, Tree $tree)
    {
        $afterMenuArray = $this->getAfterMenuArray();
        ksort($afterMenuArray);
        $menu = $this->addNodes($menu, $tree, $afterMenuArray);
        return $menu;
    }

    /**
     * Recursively add menu items to menu.
     * @param Node $menu
     * @param Tree $tree
     * @param array $menuArray
     * @param Node|NULL $parent
     * @return Node
     */
    protected function addNodes($menu, Tree $tree, $menuArray = [], $parent = null)
    {
        foreach ($menuArray as $menuElement) {
            if (is_array($menuElement) && !empty($menuElement)) {
                $node = $this->nodeFactory->create($this->getMenuData($tree, $menuElement, $parent));
                if (!$parent) {
                    $menu->addChild($node);
                }
                $tree->addNode($node, $parent);
                if (array_key_exists('children', $menuElement)) {
                    $this->addNodes($menu, $tree, $menuElement['children'], $node);
                }
            }
        }
        return $menu;
    }

    /**
     * Create array to add menu items before magento category menu
     * @return array
     */
    protected function getBeforeMenuArray()
    {
        return $this->helper->getMenuItems(Data::BEFORE_MENU_ITEMS);
    }

    /**
     * Create array to add menu items after magento category menu
     * @return array
     */
    protected function getAfterMenuArray()
    {
        return $this->helper->getMenuItems(Data::AFTER_MENU_ITEMS);
    }

    /**
     * @param Tree $tree
     * @return Node
     */
    private function getCategoriesNode(Tree $tree)
    {
        return $this->nodeFactory->create(
            [
                'data' => [
                    'name' => __('Category'),
                    'id' => 'categories',
                    'url' => '/categories/',
                    'has_active' => false,
                    'is_active' => false // (expression to determine if menu item is selected or not)
                ],
                'idField' => 'id',
                'tree' => $tree
            ]
        );
    }

    /**
     * Create array for menu item
     * @param Tree $tree
     * @param array $menuElement
     * @return array
     */
    protected function getMenuData(Tree $tree, $menuElement = [], $parent = null)
    {
        $class = 'menu-top-category ';
        $class .= $menuElement['class'];
        return [
            'data' => [
                'name' => __($menuElement['name']),
                'id' => $menuElement['id'],
                'url' => ($menuElement['url'] != '#') ? rtrim($this->storeManager->getStore()->getUrl($menuElement['url']), "/") : '#',
                'has_active' => false,
                'is_active' => false,
                'class' =>  $class,
            ],
            'idField' => 'id',
            'tree' => $tree
        ];
    }
}
