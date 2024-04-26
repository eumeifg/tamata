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

use Magedelight\Catalog\Model\ProductRequest;
use Magento\Backend\Block\Widget\Form\Generic as GenericForm;
use Magento\Backend\Block\Widget\Tab\TabInterface;

class Offer extends GenericForm implements TabInterface
{

    /**
     * @var \Magedelight\Catalog\Model\Source\Condition
     */
    public $productCondition;

    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $_systemStore;

    /**
     * @var \Magento\Catalog\Model\ProductRepository
     */
    protected $productRepository;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magedelight\Catalog\Helper\Pricing\Data
     */
    protected $pricingHelper;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Catalog\Model\ProductRepository $productRepository
     * @param \Magento\Store\Model\System\Store $systemStore
     * @param \Magedelight\Catalog\Model\Source\Condition $productCondition
     * @param \Magedelight\Catalog\Helper\Pricing\Data $pricingHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        \Magento\Store\Model\System\Store $systemStore,
        \Magedelight\Catalog\Model\Source\Condition $productCondition,
        \Magedelight\Catalog\Helper\Pricing\Data $pricingHelper,
        array $data = []
    ) {
        $this->productRepository = $productRepository;
        $this->_systemStore = $systemStore;
        $this->productCondition = $productCondition;
        $this->scopeConfig = $context->getScopeConfig();
        $this->pricingHelper = $pricingHelper;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     *
     * @return array
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
     *
     * @return string
     */
    protected function getSelectedWebsites()
    {
        $model = $this->_coreRegistry->registry('vendor_product_request');
        $websites = explode(",", $model->getWebsiteIds());
        return  $websites;
    }

    /**
     *
     * @return boolean
     */
    public function isMultiWebsiteOptionEnabled()
    {
        return $this->scopeConfig->getValue(
            \Magedelight\Catalog\Model\Product::M_W_ENABLE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Prepare form
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        /** @var $model \Ves\Brand\Model\Brand */
        $model = $this->_coreRegistry->registry('vendor_product_request');

        $model->setData('old_status', $this->getRequest()->getParam('status'));

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();

        $htmlIdPrefix = 'productrequest_';
        $form->setHtmlIdPrefix($htmlIdPrefix);

        $hasVariants = $model->getHasVariants() ? true : false;
        if ($model->getHasVariants()) {
            $offerTabLabel = __('General Information');
        } else {
            $offerTabLabel = __('Offer Information');
        }

        $fieldset = $form->addFieldset('offer_fieldset', ['legend' => $offerTabLabel ]);

        $existing = $this->getRequest()->getParam('existing');
        $model->setData('existing', $existing);
        $fieldset->addField('existing', 'hidden', ['name' => 'existing']);

        if ($model->getId()) {
            $fieldset->addField('product_request_id', 'hidden', ['name' => 'product_request_id']);
            $fieldset->addField('vendor_id', 'hidden', ['name' => 'vendor_id']);
            $fieldset->addField('category_id', 'hidden', ['name' => 'category_id']);
            $fieldset->addField('attribute_set_id', 'hidden', ['name' => 'attribute_set_id']);
            $fieldset->addField('main_category_id', 'hidden', ['name' => 'main_category_id']);
            $fieldset->addField('has_variants', 'hidden', ['name' => 'has_variants']);
            $fieldset->addField('old_status', 'hidden', ['name' => 'old_status']);
            $fieldset->addField('can_list', 'hidden', ['name' => 'can_list']);
            $fieldset->addField('store_id', 'hidden', ['name' => 'store_id']);
            $fieldset->addField('email', 'hidden', ['name' => 'email']);
            $fieldset->addField('business_name', 'hidden', ['name' => 'business_name']);
            $model->setData('business_name', $model->getBusinessName());
            $model->setData('email', $model->getEmail());
            $model->setData('store_id', $model->getStoreId());
            $model->setData('can_list', $model->getCanList());

            $pid = $model->getData('marketplace_product_id', false);
            if ($pid) {
                $marketplaceProduct = $this->productRepository->getById($pid);

                $pname = $marketplaceProduct->getId() ? $marketplaceProduct->getName() : 'Marketplace Product';

                $model->setData('product_link', $pname);
                $fieldset->addField('marketplace_product_id', 'hidden', ['name' => 'marketplace_product_id']);

                $fieldset->addField('product_link', 'link', [
                    'label' => __('Marketplace Product Link'),
                    'style' => "",
                    'href' => $this->getUrl('catalog/product/edit', ['id' => $pid]),
                    'value'  => $pname,
                    'target' => '_blank',
                    'after_element_html' => ''
                ]);
            }
            $currentWebsite = $this->getWebsites();
            $model->setData('website_id', $currentWebsite['label']);
        }

        if (!$hasVariants) {
            $fieldset->addField(
                'vendor_sku',
                'text',
                [
                'name' => 'vendor_sku',
                'label' => __('Seller SKU'),
                'title' => __('Seller SKU'),
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
                'values' => $this->productCondition->getOptionArray()
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
                'required' => true
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

            $dateFormat = $this->_localeDate->getDateFormat(\IntlDateFormatter::SHORT);

            $fieldset->addField(
                'special_from_date',
                'date',
                [
                'name' => 'special_from_date',
                'label' => __('Special From Date'),
                'title' => __('Special From Date'),
                'date_format' => $dateFormat,

                ]
            )->setAfterElementHtml("<script type=\"text/javascript\">
                //<![CDATA[
                  require([
                  'jquery',
                  'mage/calendar'
                    ], function($){
                        $('#productrequest_special_from_date').calendar({           
                           hideIfNoPrevNext: true,
                           minDate: new Date(),
                           showOn: 'button',
                           dateFormat: '$dateFormat',
                            onSelect: function( selectedDate ) {
                                $('#productrequest_special_to_date').datepicker('option', 'minDate', selectedDate);
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
                'title' => __('Special To Date'),
                'date_format' => $dateFormat,
                ]
            )->setAfterElementHtml("<script type=\"text/javascript\">
                //<![CDATA[
                  require([
                  'jquery',
                  'mage/calendar'
                    ], function($){
                        $('#productrequest_special_to_date').calendar({           
                           hideIfNoPrevNext: true,
                           minDate: new Date(),
                           showOn: 'button',
                           dateFormat: '$dateFormat',
                           onSelect: function( selectedDate ) {
                                $('#productrequest_special_from_date').datepicker('option', 'maxDate', selectedDate );
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
                'title' => __('Quantity')
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
        }

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

        $fieldset->addField(
            'created_at',
            'label',
            [
                'name' => 'created_at',
                'label' => __('Created At'),
                'title' => __('Created At'),
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
            'disapprove_message',
            'textarea',
            [
                'name' => 'disapprove_message',
                'label' => __('Disapprove Message'),
                'title' => __('Disapprove Message')
            ]
        );

        $this->setChild(
            'form_after',
            $this->getLayout()->createBlock(
                \Magento\Backend\Block\Widget\Form\Element\Dependence::class
            )->addFieldMap(
                "{$htmlIdPrefix}status",
                'status'
            )
            ->addFieldMap(
                "{$htmlIdPrefix}disapprove_message",
                'disapprove_message'
            )
            ->addFieldDependence(
                'disapprove_message',
                'status',
                ProductRequest::STATUS_DISAPPROVED
            )
        );

        $form->setFieldNameSuffix('product');
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
}
