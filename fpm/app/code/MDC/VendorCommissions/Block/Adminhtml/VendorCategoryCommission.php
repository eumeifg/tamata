<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_OffersImportExport
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace MDC\VendorCommissions\Block\Adminhtml;

use Magento\Backend\Block\Widget\Form\Generic;

class VendorCategoryCommission extends Generic
{

    /**
     * @var \Magento\Store\Model\WebsiteFactory
     */
    protected $_websiteFactory;

    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $_systemStore;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Store\Model\System\Store $systemStore
     * @param \Magento\Store\Model\WebsiteFactory $websiteFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Store\Model\System\Store $systemStore,
        \Magento\Store\Model\WebsiteFactory $websiteFactory,
        array $data = []
    ) {
        $this->_websiteFactory = $websiteFactory;
        $this->_systemStore = $systemStore;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Prepare form
     *
     * @return $this
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareForm()
    {
        $form = $this->_formFactory->create();

        $form->setHtmlIdPrefix('offers_');
        $isElementDisabled = false;
        $fieldset = $form->addFieldset('base_fieldset', []);

        $websites = $this->_websiteFactory->create()->getCollection()->toOptionArray();
        array_unshift($websites, ['value' => '', 'label' => __('--- Please Select Website ---')]);

        $fieldset->addField(
            'website_id',
            'select',
            [
                'name' => 'website_id',
                'class' => 'custom-select',
                'label' => __('Website'),
                'title' => __('Website'),
                'values' => $websites,
                'required' => true,
                'disabled' => false
            ]
        );

        $fieldset->addField(
            'vendor_id',
            'select',
            [
                'name' => 'vendor_id',
                'class' => 'custom-select',
                'label' => __('Vendor'),
                'title' => __('Vendor'),
                'values' =>  ['--- Please Select Vendor ---'],
                'required' => true,
                'disabled' => $isElementDisabled
            ]
        );

        $form->getElement('vendor_id')->setAfterElementHtml('
            <br /><button id="load-category" class="primary">Load</button>
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
        $form->getElement('website_id')->setAfterElementHtml(
            "<script type=\"text/javascript\">
                        require([
                        'jquery',
                        'mage/template',
                        'customselect',
                        'jquery/ui',
                        'mage/translate'
                    ],
                    function($, mageTemplate) {
                       $('#offers_website_id').change(function(event){
                          $.ajax({
                                url : '" . $this->getVendorListUrl() . "website/' +  $('#offers_website_id').val(),
                                type: 'get',
                                dataType: 'json',
                                showLoader:true,
                                success: function(data){
                                    $('#offers_vendor_id').customselect('destroy');
                                    $('#offers_vendor_id').html(data.htmlcontent);
                                    $('#offers_vendor_id').customselect();
                                }
                          });
                       })
                    }
                );
            </script>"
        );

        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * @return string
     */
    public function getVendorListUrl()
    {
        return $this->getUrl('vendoroffers/index/vendorList');
    }

    /**
     * @return string
     */
    public function getCategoryHtmlUrl()
    {
        return $this->getUrl('commissionsadmin/vendorCategoryCommission/categoryHtml');
    }
}
