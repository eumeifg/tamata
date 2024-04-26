<?php
/**
 * KrishTechnolabs
 *
 * PHP version 7
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the KrishTechnolabs.com license that is
 * available through the world-wide-web at this URL:
 * https://www.KrishTechnolabs.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category  KrishTechnolabs
 * @package   Ktpl_MegaMenu
 * @author    Jaimin Sutariya <jaimin.sutariya@krishtechnolabs.com>
 * @copyright 2019 (c) KrishTechnolabs (https://www.KrishTechnolabs.com/)
 * @license   https://www.KrishTechnolabs.com/LICENSE.txt Krish License
 * @link      https://www.KrishTechnolabs.com/
 */

namespace Ktpl\MegaMenu\Block\Html;

use Magento\Framework\Data\Tree\NodeFactory;
use Magento\Framework\Data\TreeFactory;
use Magento\Framework\View\Element\Template;

/**
 * Html page top menu block
 *
 * @api
 * @since 100.0.2
 */
class Topmenu extends \Magento\Theme\Block\Html\Topmenu
{
    /**
     * @var \Magento\Cms\Model\Template\FilterProvider
     */
    private $filterProvider;

    /**
     * @var \Magento\Catalog\Model\CategoryFactory
     */
    private $categoryFactory;

    /**
     * Top menu data tree
     *
     * @var \Magento\Framework\Data\Tree\Node
     */
    protected $_menu;

    /**
     * @var NodeFactory
     */
    private $nodeFactory;

    /**
     * @var TreeFactory
     */
    private $treeFactory;

    /**
     * @var \Magento\Catalog\Model\CategoryRepository
     */
    private $categoryRepository;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectmanager;

    /**
     * @var null
     */
    protected $topPartnersHtml = null;

    /**
     * Topmenu constructor.
     * @param Template\Context $context
     * @param NodeFactory $nodeFactory
     * @param TreeFactory $treeFactory
     * @param \Magento\Catalog\Model\CategoryFactory $categoryFactory
     * @param \Magento\Cms\Model\Template\FilterProvider $filterProvider
     * @param \Magento\Catalog\Model\CategoryRepository $categoryRepository
     * @param \Magento\Framework\ObjectManagerInterface $objectmanager
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        NodeFactory $nodeFactory,
        TreeFactory $treeFactory,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        \Magento\Cms\Model\Template\FilterProvider $filterProvider,
        \Magento\Catalog\Model\CategoryRepository $categoryRepository,
        \Magento\Framework\ObjectManagerInterface $objectmanager,
        array $data = []
    ) {
        $this->categoryFactory = $categoryFactory;
        $this->storeManager = $context->getStoreManager();
        $this->filterProvider = $filterProvider;
        parent::__construct($context, $nodeFactory, $treeFactory, $data);
        $this->nodeFactory = $nodeFactory;
        $this->treeFactory = $treeFactory;
        $this->categoryRepository = $categoryRepository;
        $this->objectmanager = $objectmanager;
    }

    /**
     * Add sub menu HTML code for current menu item
     *
     * @param \Magento\Framework\Data\Tree\Node $child
     * @param string $childLevel
     * @param string $childrenWrapClass
     * @param int $limit
     * @return string HTML code
     */
    protected function _addSubMenu($child, $childLevel, $childrenWrapClass, $limit)
    {
        $html = '';
        if($child->getData('id') === "all categories"){
            $html .='<div class="vertical-menu-content"><div class="category">';
            $categories = $this->categoryFactory->create()->getCollection()
                ->addAttributeToSelect('*')
                ->setStore($this->storeManager->getStore())
                ->addFieldToFilter('is_active',["in" => ['1']])
                ->addFieldToFilter('is_marketing',["neq" => 1])
                ->addFieldToFilter('is_anchor',1)
                ->addFieldToFilter('level',2);

                $firstCategory = [];
                $getChildCat = [];
                foreach ($categories as $allcats) {
                    if($allcats->getLevel() == 2 && $allcats->getIsActive() == 1 && $allcats->getIsAnchor() == 1 && $allcats->getChildrenCount() > 0 ) {
                        $firstCategory[] = ['name' => $allcats->getName(), 'url' => $allcats->getUrl(), 'entity_id' => $allcats->getEntityId()];
                        if ($allcats->hasChildren()) {
                            $getChildCategory = $allcats->getChildrenCategories();
                            foreach ($getChildCategory as $childCategory) {
                                $getChildCat[$allcats->getId()][] = ['name' => $childCategory->getName(), 'url' => $childCategory->getUrl()];
                            }
                            $html .='<span class=""><a class="ftlAjW" onmouseover="getRelatedCategoryData(this)" data-subcat="'.$this->prepareSubCatsHtml($getChildCategory, $allcats->getId()).'" data-cat-name="'.$this->escapeHtml($allcats->getName()).'" href="'.$allcats->getUrl().'">'.$this->escapeHtml($allcats->getName()).'</a></span>';
                        }
                    }
                }

                if (count($firstCategory) > 0 && count($getChildCat[$firstCategory[0]['entity_id']]) > 0) {
                    $html .='</div>
                    <div class="detail">
                        <div class="topBar">'. __($firstCategory[0]['name']) .'</div>
                        <div class="subCats">
                            <div class="column">
                                <div class="subtitle"></div><div class="list">';
                                    foreach ($getChildCat[$firstCategory[0]['entity_id']] as $key => $childCat) {
                                        $html.='<a class="ftlAjW" href="'.$childCat['url'].'">'.$childCat['name'].'</a>';
                                    }
                                $html.='</div></div>
                            <div class="column">'.$this->getTopPartnersBlockHtml($firstCategory[0]['entity_id']).'</div>
                        </div>
                    </div></div>';
                }

                //$html .= '<div class="megamenu-content"><div class="megamenu-container">';
                //$html .= '<ul class="all-cats-ul-items1 level0 submenu-col9  submenu ui-menu ui-widget ui-widget-content ui-corner-all">';
                /*foreach ($categories as $allcats) {

                    if($allcats->getLevel() == 2 && $allcats->getIsActive() == 1 && $allcats->getIsAnchor() == 1 && $allcats->getChildrenCount() > 0 ){

                         $html .= ' <li class="all-cats-li-items1 level1 nav-2-1 category-item first parent ui-menu-item">   <div class="link-arrow"><a href="' . $allcats->getUrl() . '" ><span>' . $this->escapeHtml(
                                $allcats->getName()
                            ) . '</span></a></div>  ';
                         $html .= '<ul class="all-cats-ul-items2">';
                         foreach ($categories as $key => $value2) {
                             if($value2->getLevel() == 3 &&  $value2->getParentId() == $allcats->getEntityId() && $value2->getIsActive() == 1 ){

                                $html .= '  <li class="all-cats-li-items2"><div class="link-arrow"> <a href="' . $value2->getUrl() . '" ><span>' . $this->escapeHtml(
                                $value2->getName()
                            ) . '</span></a>  </div> </li>';
                                }
                         }
                       $html .= '</ul>';
                       $html .= '</li>';
                    }
            }*/
            //$html .= '</ul>';
            //$html .= '</div></div>';
        }


        // Added menu content start
        $menuContent = '';
        if ($childLevel === 0) {

             

            $levelFirstId = $child->getData('id');
            $explodeId = explode("-", $levelFirstId);
            $firstLevelCategoryId = $explodeId[count($explodeId)-1];
            try {
                $catData = $this->categoryRepository->get(
                    $firstLevelCategoryId,
                    $this->storeManager->getStore()->getId()
                );
                $getContent = (string)$catData->getData('menu_content');
                /*if ($child->hasChildren()) {
                    $menuContent .= $this->getTopPartnersHtml();
                }*/
                $staticContent = $this->filterProvider->getBlockFilter()
                    ->filter($getContent);
                if (!empty($staticContent)) {
                    // $menuContent .= '<div class="menu-block">';
                   // $menuContent .='<div class="menu-block-img">' . $staticContent . '</div></div>';
                }
            } catch (\Exception $e) {
                
            }
        }
        // Added menu content end

        if ($child->hasChildren() || $menuContent) {
            $colStops = [];
            if ($childLevel == 0 && $limit) {
                $colStops = $this->_columnBrake($child->getChildren(), $limit);
            }

            $staticMenuClass = ($menuContent && !$child->hasChildren()) ? 'static-menu-block' : '';
            if ($childLevel == 0) {
                $html .= '<div class="megamenu-content"><div class="megamenu-container">';
            }
            $customMenuClass = ($childLevel == 0) ? 'submenu-col'.$child->getChildren()->count() : '';

            $html .= '<ul class=" level' . $childLevel . ' ' .
                    $customMenuClass . ' ' . $staticMenuClass. ' ' . $childrenWrapClass . '">';
            $html .= $this->_getHtml($child, $childrenWrapClass, $limit, $colStops);
            $html .= '</ul>';
            $html .= $menuContent;

            if ($childLevel == 0) {
                $html .= '</div>';
                /*....Start to show dynamic block for top partners....*/
                $levelFirstId = $child->getData('id');
                $explodeId = explode("-", $levelFirstId);
                $firstLevelCategoryId = $explodeId[count($explodeId)-1];
                $catData = $this->categoryRepository->get(
                        $firstLevelCategoryId,
                        $this->storeManager->getStore()->getId()
                    );
                $getContent = (string)$catData->getData('menu_content');
                $staticContent = $this->filterProvider->getBlockFilter()->filter($getContent);
                if (!empty($staticContent)) {
                    $menuContent .= '<div class="menu-block new-one">';
                    $menuContent .='<div class="menu-block-img">' . $staticContent . '</div></div>';
                }
                /*....End to show dynamic block for top partners....*/
                /*....Hide Dynamic top partners....*/
                //$menuContent .= $this->getTopPartnersHtml();
                $html .= $menuContent;
                $html .= '</div>';
            }
        }

        return $html;
    }
    
    /**
     *
     * @return string
     */
    protected function getTopPartnersHtml()
    {
        if ($this->topPartnersHtml === null) {
            $this->topPartnersHtml = $this->getLayout()
                    ->createBlock(\Ktpl\PromoCallouts\Block\HomePage\BestSellers::class, 'megamenu_top_partners')
                    ->setTemplate('Ktpl_MegaMenu::top_partners.phtml')->toHtml();
        }
        return $this->topPartnersHtml;
    }

    /**
     * Recursively generates top menu html from data that is specified in $menuTree
     *
     * @param \Magento\Framework\Data\Tree\Node $menuTree
     * @param string $childrenWrapClass
     * @param int $limit
     * @param array $colBrakes
     * @return string
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function _getHtml(
        \Magento\Framework\Data\Tree\Node $menuTree,
        $childrenWrapClass,
        $limit,
        array $colBrakes = []
    ) {
        $html = '';

        $children = $menuTree->getChildren();
        $parentLevel = $menuTree->getLevel();
        $childLevel = $parentLevel === null ? 0 : $parentLevel + 1;

        $counter = 1;
        $itemPosition = 1;
        $childrenCount = $children->count();

        $parentPositionClass = $menuTree->getPositionClass();
        $itemPositionClassPrefix = $parentPositionClass ? $parentPositionClass . '-' : 'nav-';

        /** @var \Magento\Framework\Data\Tree\Node $child */
        foreach ($children as $child) {
            if ($childLevel === 0 && $child->getData('is_parent_active') === false) {
                continue;
            }
            $child->setLevel($childLevel);
            $child->setIsFirst($counter == 1);
            $child->setIsLast($counter == $childrenCount);
            $child->setPositionClass($itemPositionClassPrefix . $counter);

            $outermostClassCode = '';
            $outermostClass = $menuTree->getOutermostClass();

            if ($childLevel == 0 && $outermostClass) {
                $outermostClassCode = ' class="' . $outermostClass . '" ';
                $child->setClass($outermostClass);
            }

            if (!empty($colBrakes) && $colBrakes[$counter]['colbrake']) {
                $html .= '</ul></li><li class="column"><ul>';
            }

            $html .= '<li ' . $this->_getRenderedMenuItemAttributes($child) .'has-megamenu="1">';
            $html .= '<div class="link-arrow">'
                    . '<a href="' . $child->getUrl() . '" ' . $outermostClassCode . '><span>' . $this->escapeHtml(
                        $child->getName()
                    ) . '</span></a>'
                    . '<label class="mobile" href="javascript:void(0);"> </label></div>' . $this->_addSubMenu(
                        $child,
                        $childLevel,
                        $childrenWrapClass,
                        $limit
                    ) . '</li>';
            $itemPosition++;
            $counter++;
        }

        if (!empty($colBrakes) && $limit) {
            $html = '<li class="column"><ul>' . $html . '</ul></li>';
        }

        return $html;
    }

    /**
     * Returns array of menu item's classes
     *
     * @param \Magento\Framework\Data\Tree\Node $item
     * @return array
     */
    protected function _getMenuItemClasses(\Magento\Framework\Data\Tree\Node $item)
    {
        $classes = [];

        $classes[] = 'level' . $item->getLevel();
        $classes[] = $item->getPositionClass();

        if ($item->getIsCategory()) {
            $classes[] = 'category-item';
        }

        if ($item->getIsFirst()) {
            $classes[] = 'first';
        }

        if ($item->getIsActive()) {
            $classes[] = 'active';
        } elseif ($item->getHasActive()) {
            $classes[] = 'has-active';
        }

        if ($item->getIsLast()) {
            $classes[] = 'last';
        }

        if ($item->getClass()) {
            $classes[] = $item->getClass();
        }

        // Added menu content start
        $menuContent = '';
        if ($item->getLevel() === 0) {
            $levelFirstId = $item->getData('id');
            $explodeId = explode("-", $levelFirstId);
            $firstLevelCategoryId = $explodeId[count($explodeId)-1];
            $categoryData = $this->categoryFactory->create()->load($firstLevelCategoryId);
            $menuContent = $categoryData->getData('menu_content');
            $classes[] = 'mm-parent-cat-'.$firstLevelCategoryId;
        }
        // Added menu content end

        if ($item->hasChildren() || $menuContent || $item->getId() == "all categories") {
            $classes[] = 'parent';
        }

        return $classes;
    }

    public function getTopPartnersBlockHtml($categoryId) {
        $menuContent = '';
        $catData = $this->categoryRepository->get(
            $categoryId,
            $this->storeManager->getStore()->getId()
        );
        $getContent = (string)$catData->getData('menu_content');
        $staticContent = $this->filterProvider->getBlockFilter()->filter($getContent);
        if (!empty($staticContent)) {
            $menuContent = $staticContent;
        }
        return $menuContent;
    }

    /**
     * @param $childCategoryObj
     * @param $parentCatId
     * @return string
     */
    public function prepareSubCatsHtml($childCategoryObj, $parentCatId) {
        $html = '';
        $html.='<div class="column"><div class="subtitle"></div><div class="list">';
                foreach ($childCategoryObj as $key => $childCat) {
                    $html .= '<a class="ftlAjW" href="' . $childCat->getUrl() . '">' . $childCat->getName() . '</a>';
                }
        $html.='</div></div><div class="column">'.$this->getTopPartnersBlockHtml($parentCatId).'</div>';
        $encodedHtml = base64_encode($html);
        return $encodedHtml;
    }
}
