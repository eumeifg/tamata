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
namespace Magedelight\Vendor\Block\Microsite\Sellerdirectory;

/**
 * Description of Sidebar
 *
 * @author Rocket Bazaar Core Team
 */
class Sidebar extends \Magento\Framework\View\Element\Template
{

    /**
     * @var \Magento\Catalog\Model\CategoryFactory
     */
    protected $_categoryFactory;

    /**
     * @var \Magento\Catalog\Helper\Category
     */
    protected $_categoryHelper;

    /**
     * @var \Magento\Catalog\Model\Indexer\Category\Flat\State
     */
    protected $_categoryFlatState;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Catalog\Helper\Category $categoryHelper,
        \Magento\Catalog\Model\Indexer\Category\Flat\State $categoryFlatState,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_categoryFlatState = $categoryFlatState;
        $this->_categoryHelper = $categoryHelper;
        $this->_categoryFactory = $categoryFactory;
    }

    public function getStoreCategories($sorted = false, $asCollection = false, $toLoad = true)
    {
        return $this->_categoryHelper->getStoreCategories($sorted, $asCollection, $toLoad);
    }

    public function getChildCategories($category)
    {
        if ($this->_categoryFlatState->isFlatEnabled() && $category->getUseFlatResource()) {
            $subcategories = (array)$category->getChildrenNodes();
        } else {
            $subcategories = $category->getChildren();
        }
        return $subcategories;
    }

    public function getChildCategory($categoryId)
    {
        return $this->_categoryFactory->create()->load($categoryId);
    }

    public function getChildCategoryView($category, $html = '', $level = 1)
    {
        // Check if category has children
        if ($category->hasChildren()) {
            $childCategories = $this->getSubcategories($category);
            if (count($childCategories) > 0) {
                $html .= '<ul class="o-list o-list--unstyled" data-role="content">';
                // Loop through children categories
                foreach ($childCategories as $childCategory) {
                    $html .= '<li class="level' . $level . '">';
                    $categoryUrl = 'javascript:void(0)';
                    $class = 'seller-category';
                    $span = '<span id="category-id-' . $childCategory->getId() . '" class="icon-plus"></span>';
                    if (!$childCategory->hasChildren()) {
                        $categoryUrl = $this->getUrl(
                            '*/*/*',
                            ['_current' => true, '_use_rewrite' => true, 'id' => $childCategory->getId()]
                        );
                        $class = 'seller-directory-category-link';
                        $span = '';
                    }
                    $html .= '<a href="' . $categoryUrl . '" class="' . $class . '"> ';
                    $html .= $childCategory->getName() . $span . '</a>';
                    /* if ($childCategory->hasChildren()) {
                        $isActive = false;
                        if ( $isActive )
                        {
                            $html .= '<span class="expanded"><i class="fa fa-minus"></i></span>';
                        }
                        else
                        {
                            $html .= '<span class="first collapse-container"><i class="icon-plus"></i></span>';
                        }
                    } */
                    if ($childCategory->hasChildren()) {
                        $html .= $this->getChildCategoryView($childCategory, '', ($level + 1));
                    }
                    $html .= '</li>';
                }
                $html .= '</ul>';
            }
        }
        return $html;
    }

    public function getCategoryUrl($category)
    {
        return $this->_categoryHelper->getCategoryUrl($category);
    }

    public function getSubcategories($category)
    {
        if ($this->_categoryFlatState->isFlatEnabled() && $category->getUseFlatResource()) {
            return (array)$category->getChildrenNodes();
        }
        return $category->getChildren();
    }

    public function getMainCategoryUrl($categoryId)
    {
        return $this->getUrl('*/*/*', ['_current' => true, '_use_rewrite' => true, 'id' => $categoryId]);
    }
}
