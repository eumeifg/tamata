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
namespace Magedelight\Vendor\Block\Adminhtml\Vendor\Edit\Tab;

use Magedelight\Vendor\Controller\RegistryConstants;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;

class Category extends Generic implements TabInterface
{

    /**
     * @var \Magedelight\Vendor\Helper\Data
     */
    protected $vendorHelper;
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory
     */
    protected $categoryCollectionFactory;

    /**
     *
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magedelight\Vendor\Helper\Data $vendorHelper
     * @param \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magedelight\Vendor\Helper\Data $vendorHelper,
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory,
        array $data = []
    ) {
        parent::__construct($context, $registry, $formFactory, $data);
        $this->vendorHelper = $vendorHelper;
        $this->categoryCollectionFactory = $categoryCollectionFactory;
    }

    /**
     * @return string
     */
    public function getFormHtml()
    {
        // get the current form as html content.
        $html = parent::getFormHtml();
        //Append the phtml file after the form content.
        $html .= $this->setTemplate('Magedelight_Vendor::vendor/form/categories.phtml')->toHtml();
        return $html;
    }

    /**
     * Prepare label for tab
     * @return \Magento\Framework\Phrase
     */
    public function getTabLabel()
    {
        return __('Product(s) Category Details');
    }

    /**
     * Prepare title for tab
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return __('Product(s) Category');
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        $vendor = $this->_coreRegistry->registry(RegistryConstants::CURRENT_VENDOR);
        if (!$vendor->getIsUser()) {
            return true;
        }
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * Retrieve Categories List
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getCategories()
    {
        /** @var \Magento\Catalog\Model\ResourceModel\Category\Collection $collection */
        $collection = $this->categoryCollectionFactory->create();

        $collection->addAttributeToSelect('name')->addRootLevelFilter()->load();
        foreach ($collection as $category) {
            return $this->_getTreeCategories($category, false);
        }
    }

    /**
     * @param $parent
     * @param $isChild
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _getTreeCategories($parent, $isChild)
    {
        /** @var \Magento\Catalog\Model\ResourceModel\Category\Collection $collection */
        $collection = $this->categoryCollectionFactory->create();
        $vendor = $this->_coreRegistry->registry(RegistryConstants::CURRENT_VENDOR);
        $vendorCats = $vendor->getCategoryIds();
        ($vendorCats === null) ? $vendorCats = [] : $vendorCats;
        $membershipCategories = [];
        $collection->addAttributeToSelect('name')
            ->addAttributeToFilter('is_active', '1')
            ->addAttributeToFilter('include_in_menu', '1')
            ->addAttributeToFilter('parent_id', ['eq' => $parent->getId()])
            ->addAttributeToFilter('entity_id', ['neq' => $parent->getId()])
            ->addAttributeToSort('position', 'asc')
            ->load();
        $currentlevel = $parent->getLevel() + 1;

        $ulClasses = ($currentlevel > 2) ? " submenu" : "";
        $html = '<ul class="category-ul level-' . $currentlevel . ' ' . $ulClasses . '">';
        foreach ($collection as $category) {
            $addSelectbtn = false;
            $class = 'level-' . $category->getLevel();
            if ($category->getLevel() > $currentlevel) {
                $html .= '<ul class="' . $class . '">';
            } elseif ($category->getLevel() < $currentlevel) {
                $html .= '</ul><ul class="' . $class . '">';
            }

            $childClass = '';
            if ($category->hasChildren()) {
                $childClass = ' expand';
                if ($category->getLevel() == 3) {
                    $childClass = ' has-children sub-cat-parent';
                } else {
                    $childClass = ($category->getLevel() == 2) ? ' base has-children' : ' has-children';
                }

                $disabled = '';
                $checked = '';
                $childrens = explode(',', $category->getAllChildren());

                if (($key = array_search($category->getId(), $childrens)) !== false) {
                    unset($childrens[$key]);
                }
                if (count(array_intersect($childrens, $membershipCategories)) == count($childrens)) {
                    $checked = ' checked = "checked"';
                }

                $addSelectbtn = true;
            }

            $html .= '<li class="item level-' . $category->getLevel() . $childClass . '">';
            if (!$category->hasChildren() && !($vendorCats === null)) {
                $checked = in_array($category->getId(), $vendorCats, true) ? ' checked = "checked"' : '';
                $html .= '<input type="checkbox" name="category[]" id="category-' . $category->getId() . '" ';
                $html .= 'value="' . $category->getId() . '" title="' . $category->getName() . '" ';
                $html .= 'class="checkbox"' . $checked . '/>';
            }

            $html .= '<label class="cat-collapse" for="category-' . $category->getId() . '">';
            $html .= '<span>' . $category->getName() . '</span></label>';
            if ($addSelectbtn) {
                $html .= '<input type="checkbox" ' . $disabled . ' name="selectall" id="slt-' . $category->getId();
                $html .= '" value="' . $category->getId() . '" title="' . $category->getName();
                $html .= '" class="checkbox slt-chk"/><label class="label selectall-subcat" for="slt-';
                $html .= $category->getId() . '" ' . $checked . '><span>' . __('Select All ') . '<strong>';
                $html .= $category->getName() . '</strong>' . '</span></label>';
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

    /**
     * Prepare form
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareForm()
    {
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();

        $vendor = $this->_coreRegistry->registry(RegistryConstants::CURRENT_VENDOR);
        $form->setHtmlIdPrefix('vendor_');
        $form->setFieldNameSuffix('vendor');

        $fieldset = $form->addFieldset(
            'meta_fieldset',
            ['legend' => __('Categories'), 'class' => 'fieldset-wide']
        );

        if ($vendor->getCategoriesIds() === null) {
            $vendor->setCategoriesIds($vendor->getCategoryIds());
        }
        $form->setValues($vendor->getData());

        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * Check permission for passed action
     * @param string $resourceId
     * @return bool
     */
    protected function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);
    }
}
