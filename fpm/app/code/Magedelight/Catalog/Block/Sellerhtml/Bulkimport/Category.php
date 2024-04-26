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
namespace Magedelight\Catalog\Block\Sellerhtml\Bulkimport;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;

class Category extends \Magedelight\Backend\Block\Template
{

    /**
     * @var \Magedelight\Backend\Model\Auth\Session
     */
    protected $authSession;

    /**
     * @var CollectionFactory
     */
    protected $categoryCollectionFactory;

    /**
     * @var CategoryRepositoryInterface
     */
    protected $categoryRepositoryInterface;

    protected $storeManager;

    /**
     * @param \Magedelight\Backend\Block\Template\Context $context
     * @param \Magedelight\Backend\Model\Auth\Session $authSession
     * @param CollectionFactory $categoryCollectionFactory
     * @param CategoryRepositoryInterface $categoryRepositoryInterface
     * @param array $data
     */
    public function __construct(
        \Magedelight\Backend\Block\Template\Context $context,
        \Magedelight\Backend\Model\Auth\Session $authSession,
        CollectionFactory $categoryCollectionFactory,
        CategoryRepositoryInterface $categoryRepositoryInterface,
        array $data = []
    ) {
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        $this->categoryRepositoryInterface = $categoryRepositoryInterface;
        $this->storeManager = $context->getStoreManager();
        $this->authSession = $authSession;
        parent::__construct($context, $data);
    }

    /**
     * Retrieve Allowed Categories List
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getAllowedTabCategories()
    {
        $vendorCats = [];
        foreach ($this->getCurrentVendorCategories() as $category_id) {
            $category = $this->categoryRepositoryInterface->get($category_id);
            if ($category->getIsActive()) {
                $cats = explode('/', $category->getPath());
                $i = 1;
                foreach ($cats as $cat) {
                    if ($cat != 1 && $cat != 2) {
                        $cat = $this->categoryRepositoryInterface->get($cat);
                        if (count($cats) == $i) {
                            isset($vendorCats[$category->getId()]) ?
                                $vendorCats[$category->getId()] .= $cat->getName() :
                                $vendorCats[$category->getId()] = $cat->getName();
                        } else {
                            isset($vendorCats[$category->getId()]) ?
                                $vendorCats[$category->getId()] .= $cat->getName() . '&nbsp;&nbsp;&#xbb;&nbsp;&nbsp;' :
                                $vendorCats[$category->getId()] = $cat->getName() . '&nbsp;&nbsp;&#xbb;&nbsp;&nbsp;';
                        }
                    }
                    $i++;
                }
            }
        }
        asort($vendorCats);
        return $vendorCats;
    }

    public function getCurrentVendorCategories()
    {
        return $this->authSession->getUser()->getCategoryIds();
    }

    /**
     * Retrieve Categories List
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getCategories()
    {
        /** @var \Magento\Catalog\Model\ResourceModel\Category\Collection $collection */
        $collection = $this->categoryCollectionFactory->create();
        $rootCategoryId = $this->storeManager->getStore()->getRootCategoryId();
        $collection->addAttributeToSelect('name')
                ->addAttributeToFilter('entity_id', $rootCategoryId)->addRootLevelFilter()->load();

        foreach ($collection as $category) {
            return $this->_getTreeCategories($category, false);
        }
    }

    /**
     * Retrieve tree Categories
     * @param $parent
     * @param $isChild
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _getTreeCategories($parent, $isChild)
    {
        /** @var \Magento\Catalog\Model\ResourceModel\Category\Collection $collection */
        $collection = $this->categoryCollectionFactory->create();

        $vendorCats = $this->authSession->getUser()->getCategoryIds();
        $vendorCats = (empty($vendorCats)) ? [] : $vendorCats;

        $collection->addAttributeToSelect('name')
            ->addAttributeToFilter('is_active', '1')
            ->addAttributeToFilter('include_in_menu', '1')
            ->addAttributeToFilter('parent_id', ['eq' => $parent->getId()])
            ->addAttributeToFilter('entity_id', ['neq' => $parent->getId()])
            ->addAttributeToSort('position', 'asc')
            ->load();
        $currentlevel = $parent->getLevel() + 1;
        $html = ($currentlevel == 3) ? '<ul class="category-ul level-' . $currentlevel . '" style="display:none">' :
            '<ul class="category-ul level-' . $currentlevel . '">';

        foreach ($collection as $category) {
            if ($category->hasChildren()) {
                $childCategories = explode(',', $category->getAllChildren());
                /* Check if at least one child category exists in vendor categories. */
                if (!count(array_intersect($vendorCats, $childCategories)) > 0) {
                    continue;
                }
            }
            $class = 'level-' . $category->getLevel();
            if ($category->getLevel() > $currentlevel) {
                $html .= '<ul class="' . $class . '">';
            } elseif ($category->getLevel() < $currentlevel) {
                $html .= '</ul><ul class="' . $class . '">';
            }

            $childClass = '';
            if ($category->hasChildren()) {
                $childClass = ($category->getLevel() == 2) ? ' base has-children expand sub-cat-parent' :
                    ' has-children expand sub-cat-parent';
            }

            if (!$category->hasChildren()) {
                $checked = in_array($category->getId(), $vendorCats, true) ? ' checked = "checked"' : '';
                if ($checked) {
                    $html .= '<li class="item level-' . $category->getLevel() . $childClass . '">';
                    $html .= '<a id="' . $category->getId() . '" title="' . __('Click here to download') . '" ';
                    $html .= 'href="' . $this->getUrl(
                        'rbcatalog/bulkimport/downloadSample',
                        ['name' => $category->getName(), 'cid' => $category->getId()]
                    ) . '">' . $category->getName() . '</a>';
                }
            } else {
                $html .= '<li class="item level-' . $category->getLevel() . $childClass . '">';
                $html .= '<label for="category-' . $category->getId() . '" class="label cat-collapse">';
                $html .= '<span>' . $category->getName() . '</span></label>';
            }
            $currentlevel = $category->getLevel();
            if ($category->hasChildren()) {
                $html .= $this->_getTreeCategories($category, true);
            }

            $html .= '</li>';
        }
        $html .= '</ul>';
        return $html;
    }
}
