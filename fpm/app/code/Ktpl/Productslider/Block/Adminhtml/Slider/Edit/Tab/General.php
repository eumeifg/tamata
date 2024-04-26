<?php
/**
 * Ktpl
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the KrishTechnolabs.com license that is
 * available through the world-wide-web at this URL:
 * https://https://www.KrishTechnolabs.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Ktpl
 * @package     Ktpl_Productslider
 * @copyright   Copyright (c) Ktpl (https://https://www.KrishTechnolabs.com//)
 * @license     https://https://www.KrishTechnolabs.com/LICENSE.txt
 */

namespace Ktpl\Productslider\Block\Adminhtml\Slider\Edit\Tab;

use Magento\Backend\Block\Store\Switcher\Form\Renderer\Fieldset\Element;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Form\Element\Dependence;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Convert\DataObject;
use Magento\Framework\Data\Form\Element\Renderer\RendererInterface;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Registry;
use Magento\Store\Model\System\Store;
use Ktpl\Productslider\Block\Adminhtml\Slider\Edit\Tab\Renderer\Category;
use Ktpl\Productslider\Model\Config\Source\Location;
use Ktpl\Productslider\Model\Config\Source\ProductType;
use Ktpl\Productslider\Model\ResourceModel\SliderFactory;
use Ktpl\Productslider\Model\Slider;
use Ktpl\Productslider\Block\Adminhtml\Slider\Edit\Tab\Renderer\BrandVendorDynamicRow;

/**
 * Class General
 * @package Ktpl\Productslider\Block\Adminhtml\Slider\Edit\Tab
 */
class General extends Generic implements TabInterface
{
    /**
     * @var Store
     */
    protected $_systemStore;

    /**
     * @var SliderFactory
     */
    protected $_resourceModelSliderFactory;

    /**
     * @var GroupRepositoryInterface
     */
    protected $_groupRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $_searchCriteriaBuilder;

    /**
     * @var DataObject
     */
    protected $_objectConverter;

    /**
     * @var Location
     */
    protected $_location;

    /**
     * @var ProductType
     */
    protected $_productType;

    /**
     * General constructor.
     *
     * @param SliderFactory $resourceModelSliderFactory
     * @param GroupRepositoryInterface $groupRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param DataObject $objectConverter
     * @param Location $location
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param Store $systemStore
     * @param ProductType $productType
     * @param array $data
     */
    public function __construct(
        SliderFactory $resourceModelSliderFactory,
        GroupRepositoryInterface $groupRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        DataObject $objectConverter,
        Location $location,
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        Store $systemStore,
        ProductType $productType,
        array $data = []
    ) {
        $this->_resourceModelSliderFactory = $resourceModelSliderFactory;
        $this->_groupRepository            = $groupRepository;
        $this->_searchCriteriaBuilder      = $searchCriteriaBuilder;
        $this->_objectConverter            = $objectConverter;
        $this->_systemStore                = $systemStore;
        $this->_location                   = $location;
        $this->_productType                = $productType;

        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * @return Generic
     * @throws LocalizedException
     */
    protected function _prepareForm()
    {
        /** @var Slider $slider */
        $slider = $this->_coreRegistry->registry('ktpl_productslider_slider');

        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('slider_');
        $form->setFieldNameSuffix('slider');
        $fieldset = $form->addFieldset('base_fieldset', [
            'legend' => __('General Information'),
            'class'  => 'fieldset-wide'
        ]);

        $fieldset->addField('name', 'text', [
            'name'     => 'name',
            'label'    => __('Name'),
            'title'    => __('Name'),
            'required' => true,
        ]);

        $fieldset->addField('status', 'select', [
            'name'     => 'status',
            'label'    => __('Status'),
            'title'    => __('Status'),
            'required' => true,
            'options'  => [
                '1' => __('Enable'),
                '0' => __('Disable')
            ]
        ]);
        if (!$slider->getId()) {
            $slider->setData('status', 1);
        }

        $fieldset->addField('location', 'select', [
            'name'   => 'location',
            'label'  => __('Position'),
            'title'  => __('Position'),
            'values' => $this->_location->toOptionArray()
        ]);

        $productType = $fieldset->addField('product_type', 'select', [
            'name'   => 'product_type',
            'label'  => __('Type'),
            'title'  => __('Type'),
            'values' => $this->_productType->toOptionArray(),
            'note'     => __('For the Custom Specific Product goto the tab Select Product')
        ]);

        $fieldset->addField('slider_view_all_link', 'text', [
            'name'     => 'slider_view_all_link',
            'label'    => __('View all link'),
            'title'    => __('View all link'),
        ]);

        $customSpecificProduct = $fieldset->addField('new_items_banner', 'image', [
                'name'        => 'slider[new_items_banner]',
                'label'       => __('New Items Banner'),
                'title'       => __('New Items Banner'),
        ]);
        $newItemsType = $fieldset->addField('page_type', 'select', [
            'name'     => 'page_type',
            'label'    => __('New Items Type'),
            'title'    => __('New Items Type'),
            'required' => true,
            'options'  => [
                'category' => __('Category')
            ]
        ]);
        $newItemsDataId = $fieldset->addField('data_id', 'text', [
            'name'        => 'data_id',
            'label'       => __('New Items Data ID'),
            'title'       => __('New Items Data ID'),
            'required' => true,
        ]);

        $categoryIds = $fieldset->addField('categories_ids', Category::class, [
            'name'  => 'categories_ids',
            'label' => __('Categories'),
            'title' => __('Categories')
        ]);

        $brandsVendors = $fieldset->addField(
            'brand_vendor',
            BrandVendorDynamicRow::class, [
            'name'  => 'brand_vendor',
            'label' => __('Brands & Vendors'),
            'title' => __('Brands & Vendors')
        ]);

        if (!$this->_storeManager->isSingleStoreMode()) {
            /** @var RendererInterface $rendererBlock */
            $rendererBlock = $this->getLayout()->createBlock(Element::class);
            $fieldset->addField('store_ids', 'multiselect', [
                'name'     => 'store_ids',
                'label'    => __('Store Views'),
                'title'    => __('Store Views'),
                'required' => true,
                'values'   => $this->_systemStore->getStoreValuesForForm(false, true)
            ])->setRenderer($rendererBlock);
        } else {
            $fieldset->addField('store_ids', 'hidden', [
                'name'  => 'store_ids',
                'value' => $this->_storeManager->getStore()->getId()
            ]);
        }

        $customerGroups = $this->_groupRepository->getList($this->_searchCriteriaBuilder->create())->getItems();
        $fieldset->addField('customer_group_ids', 'multiselect', [
            'name'     => 'customer_group_ids[]',
            'label'    => __('Customer Groups'),
            'title'    => __('Customer Groups'),
            'required' => true,
            'values'   => $this->_objectConverter->toOptionArray($customerGroups, 'id', 'code'),
            'note'     => __('Select customer group(s) to display the block to')
        ]);

        $fieldset->addField('time_cache', 'text', [
            'name'  => 'time_cache',
            'label' => __('Cache Lifetime'),
            'title' => __('Cache Lifetime'),
            'class' => 'validate-digits',
            'note'  => __('seconds. 86400 by default, if not set. To refresh instantly, clear the Blocks HTML Output cache.')
        ]);

        $fieldset->addField('from_date', 'date', [
            'name'        => 'from_date',
            'label'       => __('From Date'),
            'title'       => __('From'),
            'date_format' => 'M/d/yyyy',
            'timezone'    => false
        ]);

        $fieldset->addField('to_date', 'date', [
            'name'        => 'to_date',
            'label'       => __('To Date'),
            'title'       => __('To'),
            'date_format' => 'M/d/yyyy',
            'timezone'    => false
        ]);

        $fieldset->addField('sort_order', 'text', [
            'name'  => 'sort_order',
            'label' => __('Sort Order'),
            'title' => __('Sort Order'),
            'class' => 'validate-digits'
        ]);

        $this->setChild(
            'form_after',
            $this->getLayout()->createBlock(Dependence::class)
                ->addFieldMap($productType->getHtmlId(), $productType->getName())
                ->addFieldMap($categoryIds->getHtmlId(), $categoryIds->getName())
                ->addFieldMap($brandsVendors->getHtmlId(), $brandsVendors->getName())
                ->addFieldMap($customSpecificProduct->getHtmlId(), $customSpecificProduct->getName())
                ->addFieldMap($newItemsType->getHtmlId(), $newItemsType->getName())
                ->addFieldMap($newItemsDataId->getHtmlId(), $newItemsDataId->getName())
                ->addFieldDependence($categoryIds->getName(), $productType->getName(), ProductType::CATEGORY)
                ->addFieldDependence($brandsVendors->getName(), $productType->getName(), ProductType::BRAND_N_VENDOR)
                ->addFieldDependence($customSpecificProduct->getName(), $productType->getName(), ProductType::CUSTOM_PRODUCTS)
                ->addFieldDependence($newItemsType->getName(), $productType->getName(), ProductType::CUSTOM_PRODUCTS)
                ->addFieldDependence($newItemsDataId->getName(), $productType->getName(), ProductType::CUSTOM_PRODUCTS)
        );

        $form->setValues($slider->getData());
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * Prepare title for tab
     *
     * @return string
     */
    public function getTabTitle()
    {
        return $this->getTabLabel();
    }

    /**
     * Prepare label for tab
     *
     * @return string
     */
    public function getTabLabel()
    {
        return __('General Information');
    }

    /**
     * Returns status flag about this tab can be showed or not
     *
     * @return bool
     * @codeCoverageIgnore
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * Returns status flag about this tab hidden or not
     *
     * @return bool
     * @codeCoverageIgnore
     */
    public function isHidden()
    {
        return false;
    }
}
