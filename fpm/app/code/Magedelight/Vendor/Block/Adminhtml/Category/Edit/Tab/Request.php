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
namespace Magedelight\Vendor\Block\Adminhtml\Category\Edit\Tab;

use Magedelight\Vendor\Model\Category\Request\Source\Status as RequestStatus;
use Magento\Framework\Data\Collection;
use Magento\Framework\Data\Tree\Node;
use Magento\Framework\Data\Tree\NodeFactory;
use Magento\Framework\Data\TreeFactory;
use Magento\Backend\Block\Widget\Tab\TabInterface;

class Request extends \Magento\Backend\Block\Widget\Form\Generic implements TabInterface
{

    /**
     * Top menu data tree
     *
     * @var \Magento\Framework\Data\Tree\Node
     */
    protected $_menu;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory
     */
    protected $categoryCollectionFactory;

    /**
     * @var \Magedelight\Vendor\Helper\Data
     */
    protected $vendorHelper;

    /**
     * @var \Magento\Catalog\Helper\Category
     */
    protected $catalogCategory;

    /**
     * @var \Magento\Catalog\Model\Layer\Resolver
     */
    protected $layerResolver;

    /**
     * @var TreeFactory
     */
    protected $treeFactory;

    /**
     * @var NodeFactory
     */
    protected $nodeFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magedelight\Vendor\Api\CategoryRequestRepositoryInterface
     */
    protected $categoryRequestRepository;

    /**
     * @var \Magedelight\Vendor\Model\Category\Request\Source\Status
     */
    protected $requestStatuses;

    protected $vendorWebsiteRepository;

    /**
     * Request constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Framework\App\Request\Http $request
     * @param \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory
     * @param \Magedelight\Vendor\Helper\Data $vendorHelper
     * @param \Magento\Catalog\Helper\Category $catalogCategory
     * @param \Magento\Catalog\Model\Layer\Resolver $layerResolver
     * @param NodeFactory $nodeFactory
     * @param TreeFactory $treeFactory
     * @param \Magedelight\Vendor\Api\CategoryRequestRepositoryInterface $categoryRequestRepository
     * @param RequestStatus $requestStatuses
     * @param \Magedelight\Vendor\Api\VendorWebsiteRepositoryInterface $vendorWebsiteRepository
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory,
        \Magedelight\Vendor\Helper\Data $vendorHelper,
        \Magento\Catalog\Helper\Category $catalogCategory,
        \Magento\Catalog\Model\Layer\Resolver $layerResolver,
        NodeFactory $nodeFactory,
        TreeFactory $treeFactory,
        \Magedelight\Vendor\Api\CategoryRequestRepositoryInterface $categoryRequestRepository,
        \Magedelight\Vendor\Model\Category\Request\Source\Status $requestStatuses,
        \Magedelight\Vendor\Api\VendorWebsiteRepositoryInterface $vendorWebsiteRepository,
        array $data = []
    ) {
        $this->registry = $registry;
        $this->request = $request;
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        $this->vendorHelper = $vendorHelper;
        $this->vendorWebsiteRepository = $vendorWebsiteRepository;
        $this->nodeFactory = $nodeFactory;
        $this->treeFactory = $treeFactory;
        $this->storeManager = $context->getStoreManager();
        $this->catalogCategory = $catalogCategory;
        $this->layerResolver = $layerResolver;
        $this->categoryRequestRepository = $categoryRequestRepository;
        $this->requestStatuses = $requestStatuses;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Prepare form
     *
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function _prepareForm()
    {
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();

        $request = $this->_coreRegistry->registry('category_request');
        $form->setHtmlIdPrefix('request_');
        $form->setFieldNameSuffix('request');

        $fieldset = $form->addFieldset(
            'meta_fieldset',
            ['legend' => __('Categories'), 'class' => 'fieldset-wide']
        );
        $currentWebsite = $this->getWebsites();
        $request->setData('website_id', $currentWebsite['label']);
        $websiteId = $this->storeManager->getStore($request->getStoreId())->getWebsiteId();

        $vendorWebsiteModel = $this->vendorWebsiteRepository->getVendorWebsiteData($request->getVendorId(), $websiteId);
        $vendorBusinessName = $vendorWebsiteModel->getBusinessName();
        $request->setData('business_name', $vendorBusinessName);

        if ($request->getId()) {
            $fieldset->addField('request_id', 'hidden', ['name' => 'request_id']);
            $options = $this->requestStatuses->toOptionArray();
            /*$options[0] = [
                'value'=>'',
                'label'=>__('Select')
            ];*/
            $disabled = false;

            if ($request->getStatus() != RequestStatus::STATUS_PENDING) {
                $disabled = true;
            }

            $fieldset->addField(
                'business_name',
                'label',
                [
                    'name' => __('business_name'),
                    'label' => __('Vendor Name'),
                    'title' => __('Vendor Name')
                    ]
            );

            $fieldset->addField(
                'status',
                'select',
                [
                        'name' => 'status',
                        'label' => __('Approve/Reject'),
                        'title' => __('Approve/Reject'),
                        'values' => $options,
                        //'required' => true,
                        'disabled' => $disabled

                    ]
            );

            $fieldset->addField(
                'website_id',
                'label',
                [
                    'name' => __('website_id'),
                    'label' => __('Websites'),
                    'title' => __('Websites')
                    ]
            );

            $fieldset->addField(
                'status_description',
                'textarea',
                [
                    'name' => 'status_description',
                    'label' => __('Reason to reject request'),
                    'title' => __('Reason to reject request'),
                    //'required' => true,
                    'disabled' => $disabled
                    ]
            );
        }

        $form->setValues($request->getData());

        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * @return string
     */
    public function getFormHtml()
    {
        /* get the current form as html content. */
        $html = parent::getFormHtml();
        /* Append the phtml file after the form content. */
        $html .= $this->setTemplate('Magedelight_Vendor::request/categories.phtml')->toHtml();
        return $html;
    }

    /**
     *
     * @return array|\Magedelight\Vendor\Api\Data\CategoryRequestInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getCategoryRequest()
    {
        return $collection = $this->categoryRequestRepository->getById(
            $this->getRequest()->getParam('request_id')
        );
    }

    /**
     * Get top menu html
     *
     * @param string $outermostClass
     * @param string $childrenWrapClass
     * @param int $limit
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getCategoryHtml($outermostClass = '', $childrenWrapClass = '', $limit = 0)
    {
        $request = $this->getCategoryRequest();
        $rootId = $this->storeManager->getStore($request->getStoreId())->getRootCategoryId();
        $storeId = $this->storeManager->getStore()->getId();
        /** @var \Magento\Catalog\Model\ResourceModel\Category\Collection $collection */
        $collection = $this->getCategoryTree($storeId, $rootId);
        $currentCategory = $this->getCurrentCategory();
        $mapping = [$rootId => $this->getMenu()];  /* use nodes stack to avoid recursion */

        foreach ($collection as $category) {
            if (!isset($mapping[$category->getParentId()])) {
                continue;
            }
            /** @var Node $parentCategoryNode */
            $parentCategoryNode = $mapping[$category->getParentId()];

            $categoryNode = new Node(
                $this->getCategoryAsArray($category, $currentCategory),
                'id',
                $parentCategoryNode->getTree(),
                $parentCategoryNode
            );
            $parentCategoryNode->addChild($categoryNode);

            $mapping[$category->getId()] = $categoryNode; /* add node in stack */
        }

        $this->_eventManager->dispatch(
            'page_block_html_topmenu_gethtml_before',
            ['menu' => $this->getMenu(), 'block' => $this, 'request' => $this->getRequest()]
        );

        $this->getMenu()->setOutermostClass($outermostClass);
        $this->getMenu()->setChildrenWrapClass($childrenWrapClass);

        $html = $this->_getHtml($this->getMenu(), $childrenWrapClass, $limit);

        $transportObject = new \Magento\Framework\DataObject(['html' => $html]);
        $this->_eventManager->dispatch(
            'page_block_html_topmenu_gethtml_after',
            ['menu' => $this->getMenu(), 'transportObject' => $transportObject]
        );
        $html = $transportObject->getHtml();

        return $html;
    }

    /**
     * Count All Subnavigation Items
     *
     * @param \Magento\Backend\Model\Menu $items
     * @return int
     */
    protected function _countItems($items)
    {
        $total = $items->count();
        foreach ($items as $item) {
            /** @var $item \Magento\Backend\Model\Menu\Item */
            if ($item->hasChildren()) {
                $total += $this->_countItems($item->getChildren());
            }
        }

        return $total;
    }

    /**
     * Building Array with Column Brake Stops
     *
     * @param \Magento\Backend\Model\Menu $items
     * @param int $limit
     * @return array|void
     *
     * @todo: Add Depth Level limit, and better logic for columns
     */
    protected function _columnBrake($items, $limit)
    {
        $total = $this->_countItems($items);
        if ($total <= $limit) {
            return;
        }

        $result[] = ['total' => $total, 'max' => (int)ceil($total / ceil($total / $limit))];

        $count = 0;
        $firstCol = true;

        foreach ($items as $item) {
            $place = $this->_countItems($item->getChildren()) + 1;
            $count += $place;

            if ($place >= $limit) {
                $colbrake = !$firstCol;
                $count = 0;
            } elseif ($count >= $limit) {
                $colbrake = !$firstCol;
                $count = $place;
            } else {
                $colbrake = false;
            }

            $result[] = ['place' => $place, 'colbrake' => $colbrake];

            $firstCol = false;
        }

        return $result;
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
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function _getHtml(
        \Magento\Framework\Data\Tree\Node $menuTree,
        $childrenWrapClass,
        $limit,
        $colBrakes = []
    ) {
        $html = '';

        $requestedCategories = [];
        $request = $this->getCategoryRequest();
        if ($request->getId()) {
            $requestedCategories = explode(',', $request->getCategories());
        }

        $children = $menuTree->getChildren();
        $parentLevel = $menuTree->getLevel();
        $childLevel = $parentLevel === null ? 0 : $parentLevel + 1;

        $counter = 1;
        $itemPosition = 1;
        $childrenCount = $children->count();

        foreach ($children as $child) {
            $childIdData = explode('-', $child->getId());
            $childId = $childIdData[2];
            $child->setLevel($childLevel);
            $child->setIsFirst($counter == 1);
            $child->setIsLast($counter == $childrenCount);

            $outermostClassCode = '';
            $outermostClass = $menuTree->getOutermostClass();

            if ($childLevel == 0 && $outermostClass) {
                $outermostClassCode = ' class="' . $outermostClass . '" ';
                $child->setClass($outermostClass);
            }

            if (is_array($colBrakes) && count($colBrakes) && $colBrakes[$counter]['colbrake']) {
                $html .= '</ul></li><li class="column"><ul>';
            }

            $childClass = '';

            $html .= '<li ' . $this->_getRenderedMenuItemAttributes($child) . ' >';

            $disabled = 'disabled';
            if (in_array($childId, $requestedCategories, true)) {
                $childCategoryName = "child_categories_exists[]";
            } else {
                $childCategoryName = "child_categories[]";
            }
            if (!$child->hasChildren()) {
                $checked = in_array($childId, $requestedCategories, true) ? ' checked = "checked"' : '';
                $html .= '<input type="checkbox" name="' . $childCategoryName . '" ' . $disabled . ' id="category-';
                $html .= $childId . '" value="' . $childId . '" title="' . $child->getName();
                $html .= '" class="checkbox" ' . $checked . ' />';
            }

            if ($child->hasChildren()) {
                $checked = in_array($childId, $requestedCategories, true) ? ' checked = "checked"' : '';
                $html .= '<input type="checkbox" name="parent_categories[]" ' . $disabled;
                $html .= ' id="category-' . $childId . '" value="' . $childId;
                $html .= '" title="' . $this->escapeHtml($child->getName());
                $html .= '" class="checkbox slt-chk selectall" ' . $checked . ' />';
            }

            $html .='<label class="label cat-collapse" for="category-' . $childId . '">';
            $html .= '<span>' . $this->escapeHtml($child->getName()) . '</span></label>';

            if ($child->hasChildren()) {
                $html .='<span class="selectall-cat-toggle-btn"></span>';
            }

            $html .= $this->_addSubMenu(
                $child,
                $childLevel,
                $childrenWrapClass,
                $limit
            ) . '</li>';
            $itemPosition++;
            $counter++;
        }

        if (is_array($colBrakes) && count($colBrakes) && $limit) {
            $html = '<li class="column"><ul>' . $html . '</ul></li>';
        }

        return $html;
    }

    /**
     * Add sub menu HTML code for current menu item
     *
     * @param \Magento\Framework\Data\Tree\Node $child
     * @param string $childLevel
     * @param string $childrenWrapClass
     * @param int $limit
     * @return string HTML code
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function _addSubMenu($child, $childLevel, $childrenWrapClass, $limit)
    {
        $html = '';
        if (!$child->hasChildren()) {
            return $html;
        }

        $colStops = null;
        if ($childLevel == 0 && $limit) {
            $colStops = $this->_columnBrake($child->getChildren(), $limit);
        }

        $html .= '<ul class="level' . $childLevel . ' submenu category-ul">';
        $html .= $this->_getHtml($child, $childrenWrapClass, $limit, $colStops);
        $html .= '</ul>';

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

        $classes[] = 'level' . $item->getLevel() . ' item';
        $classes[] = $item->getPositionClass();

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

        if ($item->hasChildren()) {
            $classes[] = 'has-children expand';
        }

        return $classes;
    }

    /**
     * Generates string with all attributes that should be present in menu item element
     *
     * @param \Magento\Framework\Data\Tree\Node $item
     * @return string
     */
    protected function _getRenderedMenuItemAttributes(\Magento\Framework\Data\Tree\Node $item)
    {
        $html = '';
        $attributes = $this->_getMenuItemAttributes($item);
        foreach ($attributes as $attributeName => $attributeValue) {
            $html .= ' ' . $attributeName . '="' . str_replace('"', '\"', $attributeValue) . '"';
        }

        return $html;
    }

    /**
     * Returns array of menu item's attributes
     *
     * @param \Magento\Framework\Data\Tree\Node $item
     * @return array
     */
    protected function _getMenuItemAttributes(\Magento\Framework\Data\Tree\Node $item)
    {
        $menuItemClasses = $this->_getMenuItemClasses($item);

        return ['class' => implode(' ', $menuItemClasses)];
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
     * Get current Category from catalog layer
     *
     * @return \Magento\Catalog\Model\Category
     */
    protected function getCurrentCategory()
    {
        $catalogLayer = $this->layerResolver->get();

        if (!$catalogLayer) {
            return null;
        }

        return $catalogLayer->getCurrentCategory();
    }

    /**
     * Convert category to array
     *
     * @param \Magento\Catalog\Model\Category $category
     * @param \Magento\Catalog\Model\Category $currentCategory
     * @return array
     */
    protected function getCategoryAsArray($category, $currentCategory)
    {
        return [
            'name' => $category->getName(),
            'id' => 'category-node-' . $category->getId(),
            'url' => $this->catalogCategory->getCategoryUrl($category),
            'has_active' => in_array(
                (string)$category->getId(),
                explode('/', $currentCategory->getPath()),
                true
            ),
            'is_active' => $category->getId() == $currentCategory->getId()
        ];
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
        $collection->addAttributeToSelect('name');
        /* load only from store root */
        $collection->addFieldToFilter('path', ['like' => '1/' . $rootId . '/%']);
        $collection->addIsActiveFilter();
        $collection->addUrlRewriteToResult();
        $collection->addOrder('level', Collection::SORT_ORDER_ASC);
        $collection->addOrder('position', Collection::SORT_ORDER_ASC);
        $collection->addOrder('parent_id', Collection::SORT_ORDER_ASC);
        $collection->addOrder('entity_id', Collection::SORT_ORDER_ASC);

        return $collection;
    }

    /**
     * @return \Magento\Framework\Phrase|string
     */
    public function getTabLabel()
    {
        return __('Request Information');
    }

    /**
     * @return \Magento\Framework\Phrase|string
     */
    public function getTabTitle()
    {
        return __('Request Information');
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getWebsites()
    {
        foreach ($this->_storeManager->getWebsites() as $website) {
            $websiteList[] = $website->getData();
        }
        $websites = [];
        foreach ($websiteList as $website) {
            $selectedWebsite = $this->getSelectedWebsites();
            if ($selectedWebsite[0] == $website['website_id']) {
                $websites['label'] = $website['name'];
            }
        }
        return $websites;
    }

    /**
     * @return int
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function getSelectedWebsites()
    {
        $model = $this->_coreRegistry->registry('category_request');

        return  $this->storeManager->getStore($model->getStoreId())->getWebsiteId();
    }
}
