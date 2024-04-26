<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Commissions
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Commissions\Block\Adminhtml\Vendorcommission\Edit\Tab;

use Magedelight\Commissions\Model\Source\CalculationType;
use Magedelight\Commissions\Model\Source\Status;
use Magedelight\Commissions\Model\Source\VendorBusiness;

class Vendorcommission extends \Magento\Backend\Block\Widget\Form\Generic implements
    \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * @var VendorBusiness
     */
    private $allvendor;

    protected $systemStore;

    protected $status;

    protected $calculationType;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Store\Model\System\Store $systemStore,
        Status $status,
        CalculationType $calculationType,
        VendorBusiness $allvendor,
        \Magedelight\Commissions\Model\VendorcommissionFactory $vendorcommissionFactory,
        array $data = []
    ) {
        $this->systemStore = $systemStore;
        $this->status = $status;
        $this->calculationType = $calculationType;
        $this->allvendor = $allvendor;
        $this->vendorcommissionFactory = $vendorcommissionFactory;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    protected function _prepareForm()
    {
        $model = $this->vendorcommissionFactory->create();
        $id = $this->getRequest()->getParam('vendor_commission_id');
        $model->load($id);

        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('page_');
        $allVendors = ['-- ' . __('Please Select Website') . ' --'];

        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Vendor commission Information')]);

        if (isset($id)) {
            $fieldset->addField('vendor_commission_id', 'hidden', [
                'required' => false,
                'name' => 'vendor_commission_id',
                'value' => $this->getRequest()->getParam('vendor_commission_id')
            ]);
        }

        $websites = $fieldset->addField(
            'website_id',
            'select',
            [
                'name' => 'website_id',
                'class' => 'custom-select',
                'label' => __('Website'),
                'title' => __('Website'),
                'required' => true,
                'values' => $this->systemStore->getWebsiteValuesForForm(true)
            ]
        );

        if ($model && $model->getId()) {
            $websiteId = $model->getWebsiteId();
            $allVendors = $this->allvendor->toOptionArray($websiteId);
            array_unshift($allVendors, ['value' => '', 'label' => __('-- Please select vendor --')]);
        }

        $fieldset->addField(
            'vendor_id',
            'select',
            [
                'name' => 'vendor_id',
                'label' => __('Vendor'),
                'class' => 'custom-select',
                'title' => __('Vendor'),
                'required' => true,
                'values' => $allVendors,
                'note' => __('Select the vendor already created')
            ]
        );

        $fieldset->addField(
            'vendor_commission_value',
            'text',
            [
                'name' => 'vendor_commission_value',
                'label' => __('Vendor Commission'),
                'title' => __('Vendor Commission'),
                'required' => true,
                'note' => __('Enter the value for the commission to be levied to the vendor')
            ]
        );
        $fieldset->addField(
            'vendor_calculation_type',
            'select',
            [
                'name' => 'vendor_calculation_type',
                'label' => __('Calculation Type'),
                'title' => __('Calculation Type'),
                'values' => $this->calculationType->toOptionArray(),
                'note' => __('Selected configuration will be the base to calculate the commission either flat or
                 percentage')
            ]
        );
        $fieldset->addField(
            'vendor_marketplace_fee',
            'text',
            [
                'name' => 'vendor_marketplace_fee',
                'label' => __('Vendor Marketplace Fee Commission'),
                'title' => __('Vendor Marketplace Fee Commission'),
                'required' => true,
                'note' => __('Marketplace fee to be paid either in percentage or flat as per the selection')
             ]
        );
        $fieldset->addField(
            'vendor_marketplace_fee_type',
            'select',
            [
                'name' => 'vendor_marketplace_fee_type',
                'label' => __('Vendor Marketplace Fee Calculation Type'),
                'title' => __('Vendor Marketplace Fee Calculation Type'),
                'values' => $this->calculationType->toOptionArray(),
                'note' => __('Selected calculation type is considered for Marketplace fee calculation')
            ]
        );
        $fieldset->addField(
            'vendor_cancellation_fee',
            'text',
            [
                'name' => 'vendor_cancellation_fee',
                'label' => __('Vendor Cancellation Fee Commission'),
                'title' => __('Vendor Cancellation Fee Commission'),
                'required' => true,
                'note' => __('Enter the value for the cancellation fee charged to be levied to the vendor for canceling
                 the order')
             ]
        );
        $fieldset->addField(
            'vendor_cancellation_fee_type',
            'select',
            [
                'name' => 'vendor_cancellation_fee_type',
                'label' => __('Vendor Cancellation Fee Calculation Type'),
                'title' => __('Vendor Cancellation Fee Calculation Type'),
                'values' => $this->calculationType->toOptionArray(),
                'note' => __('Selected calculation type is considered for cancellation calculation')
            ]
        );
        $fieldset->addField(
            'vendor_status',
            'select',
            [
                'label' => __('Status'),
                'title' => __('Page Status'),
                'name' => 'vendor_status',
                'required' => true,
                'values' => $this->status->toOptionArray()
            ]
        );

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
                    $(window).load(function() {
                        $('.custom-select').customselect();
                    });
                    $('.custom-select').customselect();
                    $('#edit_form').on('change', '#page_website_id', function(event){
                      $.ajax({
                            url : '" . $this->getVendorListUrl() . "website/' +  $('#page_website_id').val(),
                            type: 'get',
                            dataType: 'json',
                            showLoader:true,
                            success: function(data){
                                $('#page_vendor_id').customselect('destroy');
                                $('#page_vendor_id').html(data.htmlcontent);
                                $('#page_vendor_id').customselect();
                            }
                      });
                   })
                }
            );
            </script>"
        );

        $form->setValues($model->getData());
        $this->setForm($form);

        return parent::_prepareForm();
    }

    public function getTabLabel()
    {
        return __('Vendor commission');
    }

    public function getTabTitle()
    {
        return __('Vendor commission');
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

    protected function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed('Magedelight_Commissions::vendorcommission');
    }

    /**
     * @return string
     */
    public function getVendorListUrl()
    {
        return $this->getUrl('vendor/index/vendorList');
    }
}
