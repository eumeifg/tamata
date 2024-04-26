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
namespace Magedelight\Catalog\Block\Adminhtml\ProductRequest\Edit\Tab;

use Magento\Backend\Block\Widget\Form\Generic as GenericForm;
use Magento\Backend\Block\Widget\Tab\TabInterface;

class Attributes extends GenericForm implements TabInterface
{
    /**
     *
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        array $data = []
    ) {
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Prepare form
     *
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareForm()
    {
        /** @var $model ProductRequest */
        $model = $this->_coreRegistry->registry('vendor_product_request');

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();

        $form->setHtmlIdPrefix('productrequest_');

        $isCollapsable = true;
        $group = $this->getGroup();
        $legend = __(ucwords(str_replace('_', ' ', $group)));
        // Initialize product object as form property to use it during elements generation
        $form->setDataObject($model);

        $fieldset = $form->addFieldset(
            'group-fields-' . $group,
            ['class' => 'user-defined', 'legend' => $legend, 'collapsable' => $isCollapsable]
        );

        $attributes = $this->getGroupAttributes();
        $exclude = ['price_type'];
        $this->_setFieldset($attributes, $fieldset, ['gallery','price_type']);
        $form->setFieldNameSuffix('product');
        $form->setValues($model->getData());
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * Retrieve additional element types
     *
     * @return array
     */
    protected function _getAdditionalElementTypes()
    {
        $result = [
            'price' => \Magento\Catalog\Block\Adminhtml\Product\Helper\Form\Price::class,
            'weight' => \Magento\Catalog\Block\Adminhtml\Product\Helper\Form\Weight::class,
            'gallery' => \Magento\Catalog\Block\Adminhtml\Product\Helper\Form\Gallery::class,
            'image' => \Magento\Catalog\Block\Adminhtml\Product\Helper\Form\Image::class,
            'boolean' => \Magento\Catalog\Block\Adminhtml\Product\Helper\Form\Boolean::class,
            'textarea' => \Magento\Catalog\Block\Adminhtml\Helper\Form\Wysiwyg::class,
        ];

        $response = new \Magento\Framework\DataObject();
        $response->setTypes([]);
        $this->_eventManager->dispatch('adminhtml_catalog_product_edit_element_types', ['response' => $response]);

        foreach ($response->getTypes() as $typeName => $typeClass) {
            $result[$typeName] = $typeClass;
        }

        return $result;
    }

    /**
     * Prepare label for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabLabel()
    {
        return __('Product Request Information');
    }

    /**
     * Prepare title for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return __('Product Request Information');
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
     * Check permission for passed action
     *
     * @param string $resourceId
     * @return bool
     */
    protected function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);
    }
}
