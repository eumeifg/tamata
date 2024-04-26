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
namespace Magedelight\Vendor\Block\Microsite\Html;

use Magedelight\Vendor\Api\VendorRepositoryInterface;
use Magedelight\Vendor\Helper\Microsite\Data;

class VendorSidebar extends \Magento\Framework\View\Element\Template
{

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory
     */
    protected $_categoryCollectionFactory;

    /**
     * @var \Magento\Catalog\Helper\Category
     */
    protected $_categoryHelper;

    /**
     * @var \Magento\Catalog\Model\Indexer\Category\Flat\State
     */
    protected $_categoryFlatState;

    /**
     * @var VendorRepositoryInterface
     */
    protected $vendorRepository;
    /**
     * @var Data
     */
    private $micrositeHelper;

    /**
     * VendorSidebar constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param VendorRepositoryInterface $vendorRepository
     * @param \Magento\Catalog\Helper\Category $categoryHelper
     * @param \Magento\Catalog\Model\Indexer\Category\Flat\State $categoryFlatState
     * @param \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory
     * @param Data $micrositeHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        VendorRepositoryInterface $vendorRepository,
        \Magento\Catalog\Helper\Category $categoryHelper,
        \Magento\Catalog\Model\Indexer\Category\Flat\State $categoryFlatState,
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory,
        \Magedelight\Vendor\Helper\Microsite\Data $micrositeHelper,
        array $data = []
    ) {
        $this->_categoryFlatState = $categoryFlatState;
        $this->_categoryHelper = $categoryHelper;
        $this->_categoryCollectionFactory = $categoryCollectionFactory;
        $this->vendorRepository = $vendorRepository;
        parent::__construct($context, $data);
        $this->micrositeHelper = $micrositeHelper;
    }

    /**
     * Retrieve Categories List
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getVendorCategories()
    {
        $vendorCategoryCollection = $this->_categoryCollectionFactory->create();
        $vendor = $this->vendorRepository->getById($this->getRequest()->getParam('vid'));
        $vendorCats = $vendor->getCategory();
        $vendorCategoryCollection->addAttributeToSelect('path')
            ->addAttributeToFilter('entity_id', ['in' => $vendorCats])
            ->load();
        $vendorCates = [];
        foreach ($vendorCategoryCollection as $vendorCategory) {
            $vendorCates = array_merge($vendorCates, explode('/', $vendorCategory->getPath()));
        }
        return $vendorCates;
    }

    /**
     * @return string
     */
    public function getCategoryUrl()
    {
        $vendorId = $this->getRequest()->getParam('vid');
        if ($vendorId) {
            return $this->getUrl('rbvendor/microsite_vendor/product/', ['_query'=>['vid'=>$vendorId]]);
        }
    }

    /**
     * @param bool $sorted
     * @param bool $asCollection
     * @param bool $toLoad
     * @return \Magento\Framework\Data\Tree\Node\Collection
     */
    public function getStoreCategories($sorted = false, $asCollection = false, $toLoad = true)
    {
        return $this->_categoryHelper->getStoreCategories($sorted, $asCollection, $toLoad);
    }

    /**
     * @param $category
     * @return array
     */
    public function getChildCategories($category)
    {
        if ($this->_categoryFlatState->isFlatEnabled() && $category->getUseFlatResource()) {
            $subcategories = (array)$category->getChildrenNodes();
        } else {
            $subcategories = $category->getChildren();
        }
        return $subcategories;
    }

    /**
     * @param $category
     * @param string $html
     * @param int $level
     * @return string
     */
    public function getChildCategoryView($category, $html = '', $level = 1)
    {
        /* Check if category has children*/
        if ($category->hasChildren()) {
            $childCategories = $this->getSubcategories($category);
            if (count($childCategories) > 0) {
                $html .= '<ul class="o-list o-list--unstyled" data-role="content">';
                /*/* Loop through children categories*/
                foreach ($childCategories as $childCategory) {
                    $html .= '<li class="level' . $level . '">';
                    $categoryUrl = 'javascript:void(0)';
                    $class = 'seller-category';
                    $span = '<span id="category-id-' . $childCategory->getId() . '" class="icon-plus"></span>';
                    if (!$childCategory->hasChildren()) {
                        $categoryUrl = $this->micrositeHelper->getVendorMicrositeUrl(
                            $this->getRequest()->getParam('vid')
                        );
                        $categoryUrl = preg_replace("/\/$/", '', $categoryUrl);
                        $categoryUrl = $categoryUrl . '?catId=' . $childCategory->getId();
                        $class = 'seller-directory-category-link';
                        $span = '';
                    }
                    $html .= '<a  href="javascript:void(0)" data-url = "' . $categoryUrl . '" class="' . $class . '">';
                    $html .= $childCategory->getName() . $span . '</a>';

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

    /**
     * @param $category
     * @return array
     */
    public function getSubcategories($category)
    {
        if ($this->_categoryFlatState->isFlatEnabled() && $category->getUseFlatResource()) {
            return (array)$category->getChildrenNodes();
        }
        return $category->getChildren();
    }

    /**
     * @param $categoryId
     * @return string
     */
    public function getMainCategoryUrl($categoryId)
    {
        return $this->getUrl(
            'rbvendor/microsite_vendor/product/',
            ['_current' => true, '_use_rewrite' => true, 'catId' => $categoryId]
        );
    }

    /**
     * @return bool
     */
    public function showAllCategoryLink()
    {
        $catId = $this->getRequest()->getParam('catId');
        if (!empty($catId)) {
            return true;
        }

        return false;
    }

    /**
     * @return string
     */
    public function getAllCategoryLink()
    {
        $vendorId = $this->getRequest()->getParam('vid');
        return $this->getUrl('rbvendor/microsite_vendor/product/', ['_use_rewrite' => true, 'vid' => $vendorId]);
    }
}
