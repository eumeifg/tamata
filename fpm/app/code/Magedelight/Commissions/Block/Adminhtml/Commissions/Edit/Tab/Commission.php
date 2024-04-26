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
namespace Magedelight\Commissions\Block\Adminhtml\Commissions\Edit\Tab;

use Magento\Backend\Block\Widget\Form\Generic as GenericForm;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magedelight\Commissions\Model\Source\Category as SourceCateogry;
use Magedelight\Commissions\Model\Source\Status;
use Magedelight\Commissions\Model\Source\CalculationType;

/**
 * @author Rocket Bazaar Core Team
 */
class Commission extends GenericForm implements TabInterface
{
    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $systemStore;
    /**
     * @var SourceCateogry
     */
    protected $sourceCateogry;
    /**
     * @var status
     */
    protected $status;
    /**
     * @var CalculationType
     */
    protected $calculationType;

    /**
     *
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Store\Model\System\Store $systemStore
     * @param SourceCateogry $sourceCateogry
     * @param Status $status
     * @param CalculationType $calculationType
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Store\Model\System\Store $systemStore,
        SourceCateogry $sourceCateogry,
        Status $status,
        CalculationType $calculationType,
        array $data = []
    ) {
        $this->sourceCateogry = $sourceCateogry;
        $this->systemStore = $systemStore;
        $this->status = $status;
        $this->calculationType = $calculationType;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Prepare form
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        $commission = $this->_coreRegistry->registry('md_commissions_commission');
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();

        $form->setHtmlIdPrefix('commission_');
        $form->setFieldNameSuffix('commission');
        $categories = ['-- '.__('Please Select Website').' --'];
        $fieldset = $form->addFieldset(
            'base_fieldset',
            [
                'legend' => __('Commission & Fees Information'),
                'class'  => 'fieldset-wide'
            ]
        );
        
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
        
        if ($commission && $commission->getId()) {
            $websiteId = $commission->getWebsiteId();
            $categories = $this->sourceCateogry->toOptionArray(true, $websiteId);
        }
        
        $fieldset->addField(
            'product_category',
            'select',
            [
                'name' => 'product_category',
                'class' => 'custom-select',
                'label' => __('Category'),
                'title' => __('Category'),
                'required' => true,
                'values' => $categories,
                'note' => __('Select the category to add commission for')
            ]
        );
        
        $fieldset->addField(
            'commission_value',
            'text',
            [
                'name' => 'commission_value',
                'label' => __('Commission'),
                'title' => __('Value'),
                'required' => true,
                'note' => __('Enter the value for the commission to be levied on the category')
            ]
        );

        $fieldset->addField(
            'calculation_type',
            'select',
            [
                'name' => 'calculation_type',
                'label' => __('Commission Calculation Type'),
                'title' => __('Commission Calculation Type'),
                'values' => $this->calculationType->toOptionArray(),
                'note' => __('Selected configuration will be the base to calculate the commission either flat or
                 percentage')
            ]
        );

        $fieldset->addField(
            'marketplace_fee',
            'text',
            [
                'name' => 'marketplace_fee',
                'label' => __('Marketplace Fee Commission'),
                'title' => __('Marketplace Fee Value'),
                'required' => true,
                'note' => __('Marketplace fee to be paid either in percentage or flat as per the selection')
            ]
        );

        $fieldset->addField(
            'marketplace_fee_type',
            'select',
            [
                'name' => 'marketplace_fee_type',
                'label' => __('Marketplace Fee Commission Type'),
                'title' => __('Marketplace Fee Commission Type'),
                'values' => $this->calculationType->toOptionArray(),
                'note' => __('Selected calculation type is considered for Marketplace fee calculation')
            ]
        );

        $fieldset->addField(
            'cancellation_fee_commission_value',
            'text',
            [
                'name' => 'cancellation_fee_commission_value',
                'label' => __('Cancellation Fee Commission'),
                'title' => __('Cancellation Fee Value'),
                'required' => true,
                'note' => __('Enter the value for the cancellation fee charged to be levied on the category')
            ]
        );

        $fieldset->addField(
            'cancellation_fee_calculation_type',
            'select',
            [
                'name' => 'cancellation_fee_calculation_type',
                'label' => __('Cancellation Fee Commission Type'),
                'title' => __('Cancellation Fee Commission Type'),
                'values' => $this->calculationType->toOptionArray(),
                'note' => __('Selected calculation type is considered for cancellation calculation')
            ]
        );

        $fieldset->addField(
            'status',
            'select',
            [
                'label' => __('Status'),
                'title' => __('Page Status'),
                'name' => 'status',
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
                        $('.custom-select').customselect({'showdisabled': true});
                    });
                    $('#edit_form').on('change', '#commission_website_id', function(event){
                      $.ajax({
                            url : '" . $this->getCategoryListUrl() . "website/' +  $('#commission_website_id').val(),
                            type: 'get',
                            dataType: 'json',
                            showLoader:true,
                            success: function(data){
                                $('#commission_product_category').customselect('destroy');
                                $('#commission_product_category').html(data.htmlcontent);
                                $('#commission_product_category').customselect({'showdisabled': true});
                            }
                      });
                   })
                }
            );
            </script>"
        );
        
        if ($commission) {
            $fieldset->addField(
                'commission_id',
                'hidden',
                [
                    'name' => 'commission_id'
                ]
            );
        }
        
        $this->_eventManager->dispatch('adminhtml_commission_page_edit_tab_main_prepare_form', ['form' => $form]);

        if ($commission) {
            $form->addValues($commission->getData());
        }
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * Prepare label for tab
     *
     * @return string
     */
    public function getTabLabel()
    {
        return __('Commission Information');
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
    public function getCategoryListUrl()
    {
        return $this->getUrl('*/*/categoryList');
    }
}
