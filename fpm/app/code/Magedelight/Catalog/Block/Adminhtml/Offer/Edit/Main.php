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
namespace Magedelight\Catalog\Block\Adminhtml\Offer\Edit;

use Magento\Backend\Block\Widget\Form\Generic as GenericForm;

class Main extends GenericForm
{

    /**
     * @var \Magedelight\Vendor\Model\Config\Source\Websites
     */
    protected $websites;

    /**
     * @var \Magento\Catalog\Model\ProductRepository
     */
    protected $productRepository;

    /**
     * @var \Magedelight\Vendor\Api\VendorRepositoryInterface
     */
    protected $vendorRepository;

    /**
     * @var \Magedelight\Catalog\Helper\Pricing\Data
     */
    protected $pricingHelper;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magedelight\Catalog\Model\Config\Source\Websites $websites
     * @param \Magento\Catalog\Model\ProductRepository $productRepository
     * @param \Magedelight\Vendor\Api\VendorRepositoryInterface $vendorRepository
     * @param \Magedelight\Catalog\Helper\Pricing\Data $pricingHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magedelight\Catalog\Model\Config\Source\Websites $websites,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        \Magedelight\Vendor\Api\VendorRepositoryInterface $vendorRepository,
        \Magedelight\Catalog\Helper\Pricing\Data $pricingHelper,
        array $data = []
    ) {
        $this->websites = $websites;
        $this->_productRepository = $productRepository;
        $this->storeManager = $context->getStoreManager();
        $this->vendorRepository = $vendorRepository;
        $this->pricingHelper = $pricingHelper;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Prepare form
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        parent::_prepareForm();

        /** @var $model \Ves\Brand\Model\Brand */
        $model = $this->_coreRegistry->registry('vendor_offer');

        $productId = $this->_request->getParam('product_id', null);
        $productWebsites = [];
        if ($productId) {
            $product = $this->_productRepository->getById($productId);
            if ($product) {
                $productWebsites = $product->getWebsiteIds();
            }
        }
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();

        $htmlIdPrefix = 'vendor_offer_';
        $form->setHtmlIdPrefix($htmlIdPrefix);

        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Vendor Offer Information')]);
        $isElementDisabled = false;

        if ($model->getId()) {
            $pid = $model->getData('marketplace_product_id', false);
            if ($pid) {
                $fieldset->addField('marketplace_product_id', 'hidden', ['name' => 'marketplace_product_id']);
            }
            $fieldset->addField('vendor_id', 'hidden', ['name' => 'vendor_id']);
            $model->setBusinessName($this->vendorRepository->getById($model->getVendorId())->getBusinessName());
            $isElementDisabled = true;
        }

        $model->setData('product_link', $model->getName());

        $websites = $fieldset->addField(
            'website_id',
            'select',
            [
                    'name' => 'website_id',
                    'class' => 'custom-select',
                    'label' => __('Website'),
                    'title' => __('Website'),
                    'required' => true,
                    'values' => $this->websites->toOptionArray($productWebsites),
                    'disabled' => $isElementDisabled
                ]
        );
        if ($model->getId()) {
            $websiteId = $this->storeManager->getStore($model->getStoreId())->getWebsiteId();
            $fieldset->addField(
                'business_name',
                'link',
                [
                    'name' => 'business_name',
                    'label' => __('Vendor Name'),
                    'title' => __('Vendor Name'),
                    'href' => $this->getUrl(
                        'vendor/index/edit',
                        ['vendor_id' => $model->getVendorId(),'website_id' => $websiteId]
                    ),
                    'target' => '_blank'
                ]
            );
        } else {
            $fieldset->addField(
                'vendor_id',
                'select',
                [
                    'name' => 'vendor_id',
                    'class' => 'custom-select',
                    'label' => __('Vendor'),
                    'title' => __('Vendor'),
                    //'values' => $this->_vendors->toOptionArray(2),
                    'values' =>  ['--Please Select Website--'],
                    'required' => true,
                    'disabled' => $isElementDisabled
                ]
            );
        }

        if (!$isElementDisabled) {
            $form->getElement('vendor_id')->setAfterElementHtml('
                <script type="text/javascript">
                    require([
                         "jquery","customselect"
                    ], function($){
                        $(window).load(function() {
                            $(".custom-select").customselect();

                        });
                      });
                </script>
            ');

            /*
            * Add Ajax to website box to get vendors for selected website
            */
            $websites->setAfterElementHtml(
                "<script type=\"text/javascript\">
                        require([
                        'jquery',
                        'mage/template',
                        'jquery/ui',
                        'mage/translate'
                    ],
                    function($, mageTemplate) {
                       $('#edit_form').on('change', '#vendor_offer_website_id', function(event){
                          $.ajax({
                                url : '" . $this->getVendorListUrl() . "website/' +  $('#vendor_offer_website_id').val()+'/product_id/$productId',
                                type: 'get',
                                dataType: 'json',
                                showLoader:true,
                                success: function(data){
                                    $('#vendor_offer_vendor_id').customselect('destroy');
                                    $('#vendor_offer_vendor_id').html(data.htmlcontent);
                                    $('#vendor_offer_vendor_id').customselect();
                                }
                          });
                       })
                    }
                );
                </script>"
            );
        }

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
            'price',
            'text',
            [
                'name' => 'price',
                'label' => __('Price'),
                'title' => __('Price'),
                'class' => 'validate-number validate-not-negative-number',
                'required' => true
            ]
        );

        $fieldset->addField(
            'special_price',
            'text',
            [
                'name' => 'special_price',
                'label' => __('Special Price'),
                'class' => 'validate-number validate-not-negative-number validate-special-price',
                'title' => __('Special Price')
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
                'title' => __('Special From Date')
            ]
        );

        $fieldset->addField(
            'special_to_date',
            'date',
            [
                'name' => 'special_to_date',
                'label' => __('Special To Date'),
                'title' => __('Special To Date'),
                'date_format' => $dateFormat
            ]
        );

        $fieldset->addField(
            'qty',
            'text',
            [
                'name' => 'qty',
                'label' => __('Units Available'),
                'class' => 'validate-number validate-not-negative-number',
                'title' => __('Units Available')
            ]
        );

        $fieldset->addField(
            'reorder_level',
            'text',
            [
                'name' => 'reorder_level',
                'label' => __('Reorder level'),
                'class' => 'validate-number validate-not-negative-number',
                'title' => __('Reorder level')
            ]
        );

        $model->setData('price', $this->pricingHelper->formatPrice($model->getData('price')));
        $model->setData('special_price', $this->pricingHelper->formatPrice($model->getData('special_price')));
        $form->setValues($model->getData());
        $this->setForm($form);

        return $this;
    }

    /**
     * @return string
     */
    public function getVendorListUrl()
    {
        return $this->getUrl('vendor/index/vendorList');
    }
}
