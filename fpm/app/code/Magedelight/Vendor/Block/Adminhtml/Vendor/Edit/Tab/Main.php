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
use Magedelight\Vendor\Model\Source\Status;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;

class Main extends Generic implements TabInterface
{

    /**
     * @var \Magedelight\Vendor\Model\Source\Status
     */
    protected $statusOption;

    /**
     * @var \Magedelight\Theme\Model\Source\DefaultCountry
     */
    protected $defaultCountry;

    /**
     * @var \Magedelight\Theme\Model\Source\Region
     */
    protected $region;

    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $_systemStore;

    /**
     * @var \Magento\Directory\Block\Data
     */
    protected $directoryHelper;

    protected $backendSession;

    /**
     * @var \Magento\Directory\Model\AllowedCountriesFactory
     */
    protected $allowedCountriesFactory;

    /**
     * @var \Magedelight\Vendor\Helper\Data
     */
    protected $vendorHelper;

    /**
     *
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magedelight\Theme\Model\Source\Region $region
     * @param \Magedelight\Theme\Model\Source\DefaultCountry $defaultCountry
     * @param Status $statusOption
     * @param \Magento\Directory\Helper\Data $directoryHelper
     * @param \Magento\Directory\Model\AllowedCountriesFactory $allowedCountriesFactory
     * @param \Magedelight\Vendor\Helper\Data $vendorHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magedelight\Theme\Model\Source\Region $region,
        \Magedelight\Theme\Model\Source\DefaultCountry $defaultCountry,
        \Magedelight\Vendor\Model\Source\Status $statusOption,
        \Magento\Directory\Helper\Data $directoryHelper,
        \Magento\Directory\Model\AllowedCountriesFactory $allowedCountriesFactory,
        \Magedelight\Vendor\Helper\Data $vendorHelper,
        array $data = []
    ) {
        $this->region = $region;
        $this->defaultCountry = $defaultCountry;
        $this->statusOption = $statusOption;
        $this->directoryHelper = $directoryHelper;
        $this->backendSession = $context->getBackendSession();
        $this->allowedCountriesFactory = $allowedCountriesFactory;
        $this->vendorHelper = $vendorHelper;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Prepare form
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $vendor = $this->_coreRegistry->registry(RegistryConstants::CURRENT_VENDOR);

        if ($vendor->getId()) {
            $form->addField('vendor_id', 'hidden', ['name' => 'vendor_id']);
        }

        $form->setHtmlIdPrefix('vendor_');
        $form->setFieldNameSuffix('vendor');

        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Profile Details')]);

        $fieldset->addType('image', \Magedelight\Vendor\Block\Adminhtml\Helper\Image::class);

        $fieldset->addField(
            'name',
            'text',
            [
            'name' => 'name',
            'label' => __('Name'),
            'title' => __('Name'),
            'class' => 'validate-alpha-with-spaces-name',
            'required' => true,
            'autofocus' => true,
            ]
        );

        if ($vendor->getId()) {
            $vendor->setEmailId($vendor->getEmail());
            $fieldset->addField(
                'email',
                'text',
                [
                'name' => 'email',
                'label' => __('Vendor Email'),
                'title' => __('Vendor Email'),
                'required' => true,
                'class' => 'validate-email',
                ]
            );
        // $fieldset->addField(
            //     'email',
            //     'hidden',
            //     [
            //     'name' => 'email',
            //     'class' => 'validate-email',
            //     ]
            // );
        } else {
            $fieldset->addField(
                'email',
                'text',
                [
                'name' => 'email',
                'label' => __('Vendor Email'),
                'title' => __('Vendor Email'),
                'required' => true,
                'class' => 'validate-email',
                ]
            );
        }

        if (!$vendor->getIsUser()) {
            $fieldset->addField(
                'mobile',
                'text',
                [
                'name' => 'mobile',
                'label' => __('Mobile'),
                'title' => __('Mobile'),
                'required' => true,
                'class' => 'required-entry validate-phone-international',
                ]
            );

            if (!$this->vendorHelper->isRemoved('address1', 'personal')) {
                $fieldset->addField(
                    'address1',
                    'text',
                    [
                    'name' => 'address1',
                    'label' => __('Address Line 1'),
                    'title' => __('Address Line 1'),
                    'required' => true,
                    'class' => 'validate-alpha-with-spaces-spl-150'
                    ]
                );
            }

            if (!$this->vendorHelper->isRemoved('address2', 'personal')) {
                $fieldset->addField(
                    'address2',
                    'text',
                    [
                    'name' => 'address2',
                    'label' => __('Address Line 2'),
                    'title' => __('Address Line 2'),
                    'class' => 'validate-alpha-with-spaces-spl-150-address2'
                    ]
                );
            }

            if (!$this->vendorHelper->isRemoved('country_id', 'personal')) {
                $fieldset->addField(
                    'country_code',
                    'hidden',
                    [
                    'name' => 'country_code'
                    ]
                );
                $countryOptions = $this->directoryHelper->getCountryCollection()->toOptionArray();

                $fieldset->addField(
                    'country_id',
                    'select',
                    [
                    'name' => 'country_id',
                    'label' => __('Country'),
                    'title' => __('Country'),
                    'values' => $countryOptions,
                    'required' => true
                    ]
                );
            }

            $sessionData = $this->backendSession->getVendorData();
            if (!$this->vendorHelper->isRemoved('region', 'personal')) {
                $regions = [];
                $currentSelectedRegion = '';
                $currentRegion = '';
                if ($vendor->getId()) {
                    $regions = $this->region->toOptionArray($vendor->getCountryId());
                    $currentSelectedRegion = $vendor->getRegionId();
                    $currentRegion = $vendor->getRegion();
                } elseif ($sessionData && array_key_exists('country_id', $sessionData['vendor'])) {
                    $regions = $this->region->toOptionArray($sessionData['vendor']['country_id']);
                    if (array_key_exists('region_id', $sessionData['vendor'])) {
                        $currentSelectedRegion = $sessionData['vendor']['region_id'];
                    } elseif (array_key_exists('region', $sessionData['vendor'])) {
                        $currentRegion = $sessionData['vendor']['region'];
                    }
                }
                $region = $fieldset->addField(
                    'region',
                    \Magedelight\Vendor\Block\Adminhtml\Vendor\Edit\Renderer\Region::class,
                    [
                    'name' => 'region',
                    'label' => __('State'),
                    'title' => __('State'),
                    ]
                );

                $region->setElementText('region')
                        ->setElementSelect('region_id')
                        ->setElementCountry('country_id')
                        ->setCurrentRegion($currentRegion)
                        ->setCurrentSelectedRegion($currentSelectedRegion)
                        ->setRegions($regions);
            }

            if (!$this->vendorHelper->isRemoved('city', 'personal')) {
                $fieldset->addField(
                    'city',
                    'text',
                    [
                    'name' => 'city',
                    'label' => __('City'),
                    'title' => __('City'),
                    'class' => 'validate-alpha-with-spaces-name-50',
                    'required' => true,
                    ]
                );
            }

            if (!$this->vendorHelper->isRemoved('pincode', 'personal')) {
                $isZipRequired = $this->region->getIsZipRequired($this->region->getDefaultCountry());
                $fieldset->addField(
                    'pincode',
                    'text',
                    [
                    'name' => 'pincode',
                    'label' => __('Pincode'),
                    'title' => __('Pincode'),
                    'required' => $isZipRequired,
                    'class' => 'validate-pin-code'
                    ]
                )->setAfterElementHtml('
                    <script>
                        require([
                             "jquery",
                        ], function($){
                            $(document).ready(function () {                        
                                $("#vendor_pincode").addClass("validate-zip-code");
                            });
                          });
                    </script>');
            }
        }
        $fieldset->addField(
            'status',
            'select',
            [
            'name' => 'status',
            'label' => __('Profile Status'),
            'title' => __('Profile Status'),
            'values' => $this->statusOption->toOptionArray(),
            'required' => true
            ]
        );

        if (!$vendor->getIsUser()) {
            $fieldset->addField(
                'status_description',
                'textarea',
                [
                'name' => 'status_description',
                'label' => __('Inactive/Disapproved Reason'),
                'title' => __('Inactive/Disapproved Reason'),
                'required' => false,
                ]
            );

            $fieldset->addField(
                'vacation_message',
                'textarea',
                [
                'name' => 'vacation_message',
                'label' => __('Vacation Message'),
                'title' => __('Vacation Message'),
                'required' => false,
                ]
            );

            $dateFormat = $this->_localeDate->getDateFormatWithLongYear();

            $fieldset->addField(
                'vacation_from_date',
                'date',
                [
                'name' => 'vacation_from_date',
                'label' => __('Vacation From Date'),
                'title' => __('Vacation From Date'),
                //'input_format' => \Magento\Framework\Stdlib\DateTime::DATE_INTERNAL_FORMAT,
                'date_format' => $dateFormat,
                'required' => false,
                ]
            );

            $fieldset->addField(
                'vacation_to_date',
                'date',
                [
                'date_format' => $dateFormat,
                'name' => 'vacation_to_date',
                'label' => __('Vacation To Date'),
                'title' => __('Vacation To Date'),
                //'input_format' => \Magento\Framework\Stdlib\DateTime::DATE_INTERNAL_FORMAT,
                'required' => false,
                ]
            );
        }

        if (!$vendor->getId() && $sessionData) {
            $vendor->setData('mobile', '+' . $sessionData['vendor']['country_code'] . $sessionData['vendor']['mobile']);
        }
        $form->setValues($vendor->getData());
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
        $allowedCountries = implode(',', $this->allowedCountriesFactory->create()->getAllowedCountries());

        /* get the current form as html content. */
        $html = parent::getFormHtml();
        /* Append the content after the form content. */
        $html .= " <script>
            require([
                'jquery',
                'intltelinpututil',
                'Magedelight_Vendor/build/js/intlTelInput',
                'mage/translate'
                ], function ($) {
            var allowCountry = '" . $allowedCountries . "';
            var AC = allowCountry.split(',');
            
            var telInput = $('#vendor_mobile');
            
            telInput.intlTelInput({
                responsiveDropdown: 1,
                onlyCountries: AC,
                nationalMode: 0,
                autoFormat: 0,
                autoPlaceholder: 'off',
                separateDialCode: true
            });
            
            var dialCode = $('.country-list li.country').first().attr('data-dial-code');
            
            if(dialCode){
                var paddingLeft = (dialCode.toString().length + 1) * 28;    
                //$(telInput).css('padding-left', paddingLeft.toString() + 'px');
                $(telInput).css('padding-left', '56px');
            }
            
            $.validator.addMethod(
                    'validate-phone-international',
                    function (v) {
                        var isValidNumber;
                        if(isValidNumber = telInput.intlTelInput('isValidNumber')){
                            $('#vendor_country_code').val($('.country-list li.country.active').attr('data-dial-code'));
                        }else{
                            $('#vendor_country_code').val('');
                        }
                        return isValidNumber;
                    },
                    $.mage.__('Please enter a valid mobile number.')
                );
            
            $(function() {
                /* source Magedelight\Vendor\Model\Source\Status */
                var VENDOR_STATUS_PENDING = 0;
                var VENDOR_STATUS_ACTIVE = 1;
                var VENDOR_STATUS_INACTIVE = 2;
                var VENDOR_STATUS_DISAPPROVED = 3;
                var VENDOR_STATUS_VACATION_MODE = 4;
                var VENDOR_STATUS_CLOSED = 5;

                var vacationMessage    = $('.field-vacation_message');
                var vacationFrmDate    = $('.field-vacation_from_date');
                var vacationToDate     = $('.field-vacation_to_date');
                var disapprovedMessage = $('.field-status_description');

                vacationMessage.hide();
                vacationFrmDate.hide();
                vacationToDate.hide();
                disapprovedMessage.hide();

                if ($('#vendor_status').val() == VENDOR_STATUS_VACATION_MODE ) {
                    vacationMessage.show();
                    vacationFrmDate.show();
                    vacationToDate.show();
                    disapprovedMessage.hide();
                    $('#vendor_status_description').removeClass('required-entry _required');
                    
                    $('.field-vacation_message').addClass('required _required');
                    $('.field-vacation_from_date').addClass('required _required');
                    $('.field-vacation_to_date').addClass('required _required');
                        
                    $('#vendor_vacation_message').addClass('required-entry _required');
                    $('#vendor_vacation_from_date').addClass('required-entry');
                    $('#vendor_vacation_to_date').addClass('required-entry');

                } else if ($('#vendor_status').val() == VENDOR_STATUS_INACTIVE ||
                    $('#vendor_status').val() == VENDOR_STATUS_DISAPPROVED) {
                    
                    $('.field-status_description').addClass('required _required');
                    $('#vendor_status_description').addClass('required-entry _required');
                    
                    $('#vendor_vacation_message').removeClass('required-entry _required');
                    $('#vendor_vacation_from_date').removeClass('required-entry _required');
                    $('#vendor_vacation_to_date').removeClass('required-entry _required');
                    disapprovedMessage.show();
                }
                else{
                    vacationMessage.hide();
                    vacationFrmDate.hide();
                    vacationToDate.hide();
                    disapprovedMessage.hide();
                    $('#vendor_vacation_message').removeClass('required-entry _required');
                    $('#vendor_status_description').removeClass('required-entry _required');
                    $('#vendor_vacation_from_date').removeClass('required-entry _required');
                    $('#vendor_vacation_to_date').removeClass('required-entry _required');
                }

                $('#edit_form').on('change', '#vendor_status', function (event) {

                    if ($('#vendor_status').val() == VENDOR_STATUS_VACATION_MODE) {
                        vacationMessage.show();
                        vacationFrmDate.show();
                        vacationToDate.show();
                        disapprovedMessage.hide();
                        $('#vendor_status_description').removeClass('required-entry _required');
                        
                        $('.field-vacation_message').addClass('required _required');
                        $('.field-vacation_from_date').addClass('required _required');
                        $('.field-vacation_to_date').addClass('required _required');
                        
                        $('#vendor_vacation_message').addClass('required-entry _required');
                        $('#vendor_vacation_from_date').addClass('required-entry');
                        $('#vendor_vacation_to_date').addClass('required-entry');
                        
                    } else if($('#vendor_status').val() == VENDOR_STATUS_INACTIVE || $('#vendor_status').val() == VENDOR_STATUS_DISAPPROVED) {
                        disapprovedMessage.show();
                        vacationMessage.hide();
                        vacationFrmDate.hide();
                        vacationToDate.hide();
                        
                        $('.field-status_description').addClass('required _required');
                        $('#vendor_status_description').addClass('required-entry _required');
                        
                        $('.field-vacation_message').removeClass('required _required');
                        $('.field-vacation_from_date').removeClass('required _required');
                        $('.field-vacation_to_date').removeClass('required _required');
                        
                        $('#vendor_vacation_message').removeClass('required-entry _required');
                        $('#vendor_vacation_from_date').removeClass('required-entry');
                        $('#vendor_vacation_to_date').removeClass('required-entry');
                    } else {
                        vacationMessage.hide();
                        vacationFrmDate.hide();
                        vacationToDate.hide();
                        disapprovedMessage.hide();
                        $('#vendor_vacation_message').removeClass('required-entry _required');
                        $('#vendor_vacation_from_date').removeClass('required-entry');
                        $('#vendor_vacation_to_date').removeClass('required-entry');
                        $('#vendor_status_description').removeClass('required-entry _required');
                    }
                });

                });
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
        return __('Profile Details');
    }

    /**
     * Prepare title for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return __('Vendor Information');
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
