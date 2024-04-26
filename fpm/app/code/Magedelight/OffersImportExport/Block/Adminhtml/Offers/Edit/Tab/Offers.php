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
namespace Magedelight\OffersImportExport\Block\Adminhtml\Offers\Edit\Tab;

use Magento\Backend\Block\Widget\Form\Generic;

class Offers extends Generic implements \Magento\Backend\Block\Widget\Tab\TabInterface
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
        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Offers')]);

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
                       $('#edit_form').on('change', '#offers_website_id', function(event){
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

        $fieldset->addField(
            'vendor_offers',
            'file',
            [
                'name' => 'vendor_offers',
                'label' => __('Choose a csv file'),
                'required' => true
            ]
        );

        $fieldset->addField(
            'sample',
            'link',
            [
            'name'      => __('Download a sample csv file'),
            'href'      => 'javascript:void(0);',
            'value'     => __('Download a sample csv file'),
            ]
        );

        $text = '<b>' . 'marketplace_sku' . '</b>' . ' : Enter product sku as seen in core product. ';
        $text .= 'Refer Catalog > Products to get sku.';
        $text .= '<br/><br/>';
        $text .= '<b>' . 'vendor_sku' . '</b>' . ' : Enter unique vendor sku.';
        $text .= '<br/><br/>';
        $text .= '<b>' . 'vendor_id' . '</b>' . ' : ';
        $text .= 'Enter vendor_id and make sure it is same as selected in vendor field.';
        $text .= ' Refer ROCKET BAZAAR > VENDORS > APPROVED/ACTIVE to get vendor id.';
        $text .= '<br/><br/>';
        $text .= '<b>' . 'special_from_date, special_to_date' . '</b>' . ' : Enter date in format dd/mm/yyyy.';
        $text .= '<br/><br/>';
        $text .= '<b>' . 'status' . '</b>' . ' : Enter numeric value e.g 0. (0 - Unlist, 1 - List). ';
        $text .= 'Note: Only listed offers are seen in storefront.';
        $text .= '<br/>';

        $fieldset->addField(
            'registered_website_details',
            'note',
            [
                'name' => 'registered_website_details',
                'text' =>  $text,
                'label' =>  __('Guidelines'),
            ]
        );

        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * Prepare label for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabLabel()
    {
        return __('Add Offers');
    }

    /**
     * Prepare title for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return __('Add Offers');
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
     * @return string
     */
    public function getVendorListUrl()
    {
        return $this->getUrl('vendoroffers/index/vendorList');
    }
}
