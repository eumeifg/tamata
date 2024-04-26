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
namespace Magedelight\Catalog\Block\Adminhtml\Product\Edit\Tab;

use Magento\Backend\Block\Widget\Form\Generic as GenericForm;
use Magento\Backend\Block\Widget\Tab\TabInterface;

class Form extends GenericForm implements TabInterface
{

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $productFactory;
    
    /**
     * @var \Magedelight\Catalog\Helper\Pricing\Data
     */
    protected $pricingHelper;
    
    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magedelight\Catalog\Helper\Pricing\Data $pricingHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magedelight\Catalog\Helper\Pricing\Data $pricingHelper,
        array $data = []
    ) {
        $this->productFactory = $productFactory;
        $this->pricingHelper = $pricingHelper;
        parent::__construct($context, $registry, $formFactory, $data);
    }
    
    /**
     * Prepare form before rendering HTML
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        /** @var $model \Ves\Brand\Model\Brand */
        $model = $this->_coreRegistry->registry('vendor_product');
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        
        $htmlIdPrefix = 'vendor_product_';
        $form->setHtmlIdPrefix($htmlIdPrefix);
        
        $storeId = $this->getRequest()->getParam('store');
        if (!$this->getRequest()->getParam('store')) {
            $storeId = 0;
        }
        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Product Information')]);
        if ($model->getId()) {
            $model->setData('store_id', $storeId);
            $fieldset->addField('vendor_product_id', 'hidden', ['name' => 'id']);
            $fieldset->addField('vendor_id', 'hidden', ['name' => 'vendor_id']);
            $fieldset->addField('store_id', 'hidden', ['name' => 'store_id']);
            
            $pid = $model->getData('marketplace_product_id', false);
            if ($pid) {
                $fieldset->addField('marketplace_product_id', 'hidden', ['name' => 'marketplace_product_id']);
            }
        }

        $product = $this->getProduct($pid);
        $pr_name = $product->getName();
       
        $model->setData('product_link', $pr_name);
        
        $fieldset->addField('product_link', 'link', [
            'label' => __('Product Name'),
            'style' => "",
            'href' => $this->getUrl('catalog/product/edit', ['id' => $model->getMarketplaceProductId()]),
            'values' => $pr_name,
            'target' => '_blank',
            'after_element_html' => '',
        ]);
        
        $fieldset->addField(
            'vendor_sku',
            'text',
            [
                'name' => 'vendor_sku',
                'label' => __('Vendor SKU'),
                'title' => __('Vendor SKU'),
                'required' => true
            ]
        );

        $fieldset->addField(
            'condition',
            'select',
            [
                'name' => 'condition',
                'label' => __('Condition'),
                'title' => __('Condition'),
                'values' => $model->getAvailableCondition()
            ]
        );

        $fieldset->addField(
            'condition_note',
            'textarea',
            [
                'name' => 'condition_note',
                'label' => __('Condition Note'),
                'title' => __('Condition Note')
            ]
        );
        
        $fieldset->addField(
            'price',
            'text',
            [
                'name' => 'price',
                'label' => __('Price'),
                'title' => __('Price'),
                'required' => true,
            ]
        );
        
        $fieldset->addField(
            'special_price',
            'text',
            [
                'name' => 'special_price',
                'label' => __('Special Price'),
                'title' => __('Special Price'),
                'class' => 'validate-number validate-not-negative-number validate-special-price'
            ]
        );
        
        $dateFormat = $this->_localeDate->getDateFormat(
            \IntlDateFormatter::SHORT
        );

        $fieldset->addField(
            'special_from_date',
            'date',
            [
                'name' => 'special_from_date',
                'label' => __('Special From Date'),
                'date_format' => $dateFormat,
                'class' => 'validate-date validate-date-range date-range-task_data-from'
            ]
        )->setAfterElementHtml("<script type=\"text/javascript\">
                //<![CDATA[
                  require([
                  'jquery',
                  'mage/calendar'
                    ], function($){
                        $('#vendor_product_special_from_date').calendar({           
                           hideIfNoPrevNext: true,
                           minDate: new Date(),
                           showOn: 'button',
                           dateFormat: '$dateFormat',
                            onSelect: function( selectedDate ) {
                                $('#vendor_product_special_to_date').datepicker('option', 'minDate', selectedDate);
                            }
                        });
                    });         
                //]]>
            </script>");
        
        $fieldset->addField(
            'special_to_date',
            'date',
            [
                'name' => 'special_to_date',
                'label' => __('Special To Date'),
                'date_format' => $dateFormat,
                'class' => 'validate-date validate-date-range date-range-task_data-from'
            ]
        )->setAfterElementHtml("<script type=\"text/javascript\">
                //<![CDATA[
                  require([
                  'jquery',
                  'mage/calendar'
                    ], function($){
                        $('#vendor_product_special_to_date').calendar({           
                           hideIfNoPrevNext: true,
                           minDate: new Date(),
                           showOn: 'button',
                           dateFormat: '$dateFormat',
                           onSelect: function( selectedDate ) {
                                $('#vendor_product_special_from_date').datepicker('option', 'maxDate', selectedDate );
                            }
                        });
                    });         
                //]]>
            </script>");
        
        $fieldset->addField(
            'qty',
            'text',
            [
                'name' => 'qty',
                'label' => __('Quantity'),
                'title' => __('Quantity'),
                'class' => 'validate-number validate-not-negative-number'
            ]
        );
        
        $fieldset->addField(
            'reorder_level',
            'text',
            [
                'name' => 'reorder_level',
                'label' => __('Reorder level'),
                'title' => __('Reorder level')
            ]
        );
        
        $fieldset->addField(
            'warranty_type',
            'select',
            [
                'name' => 'warranty_type',
                'label' => __('Warranty Type'),
                'title' => __('Warranty Type'),
                'values' => $model->getWarrantyTypes()
            ]
        );
        
        $fieldset->addField(
            'warranty_description',
            'textarea',
            [
                'name' => 'warranty_description',
                'label' => __('Warranty Description'),
                'title' => __('Warranty Description')
            ]
        );
        
        $fieldset->addField(
            'status',
            'select',
            [
                'name' => 'status',
                'label' => __('Status'),
                'title' => __('Status'),
                'values' => $model->getAvailableStatuses()
            ]
        );

        $model->setData('price', $this->pricingHelper->formatPrice($model->getData('price')));
        $model->setData('special_price', $this->pricingHelper->formatPrice($model->getData('special_price')));
        $form->setValues($model->getData());
        $this->setForm($form);

        return parent::_prepareForm();
    }
    
    /**
     * Get form HTML
     *
     * @return string
     */
    public function getFormHtml()
    {
        /* get the current form as html content. */
        $html = parent::getFormHtml();
        /* Append the content after the form content. */
        $html .= " <script>
            require(['jquery','Magedelight_Catalog/js/offer/validate'], function ($, offerValidation) {
                offerValidation();
            });
        </script>";
        
        return $html;
    }

    /**
     * Prepare label for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabLabel()
    {
        return __('Request Information');
    }

    /**
     * Prepare title for tab
     *
     * @return \Magento\Framework\Phrase
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
     * Check permission for passed action
     *
     * @param string $resourceId
     * @return bool
     */
    protected function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);
    }
    
    /**
     *
     * @param integer $id
     * @return \Magedelight\Catalog\Model\Product
     */
    public function getProduct($id)
    {
        return $this->productFactory->create()->load($id);
    }
}
