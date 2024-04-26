<?php
 /**
  * KrishTechnolabs
  *
  * PHP version 7
  *
  * @category  KrishTechnolabs
  * @package   MDC_Commissions
  * @copyright 2019 (c) KrishTechnolabs (https://www.KrishTechnolabs.com/)
  * @license   https://www.krishtechnolabs.com/LICENSE.txt Krish License
  * @link      https://www.krishtechnolabs.com/
  */
namespace MDC\Commissions\Block\Adminhtml\Vendor\Edit\Tab;

use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magedelight\Vendor\Controller\RegistryConstants;
use Magedelight\Vendor\Model\Source\Status;

class Main extends \Magedelight\Vendor\Block\Adminhtml\Vendor\Edit\Tab\Main
{

    /**
     * @var \MDC\Commissions\Model\Source\VendorGroup
     */
    protected $vendorGroups;

    /**
     *
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magedelight\Theme\Model\Source\Region $region
     * @param \Magedelight\Theme\Model\Source\DefaultCountry $defaultCountry
     * @param \Magedelight\Vendor\Model\Source\Status $statusOption
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
        \MDC\Commissions\Model\Source\VendorGroup $vendorGroups,
        \Magedelight\Vendor\Helper\Data $vendorHelper,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $registry,
            $formFactory,
            $region,
            $defaultCountry,
            $statusOption,
            $directoryHelper,
            $allowedCountriesFactory,
            $vendorHelper,
            $data
        );
        $this->vendorGroups = $vendorGroups;
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

        $fieldset->addType('image', 'Magedelight\Vendor\Block\Adminhtml\Helper\Image');

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
                'email_id',
                'text',
                [
                'name' => 'email_id',
                'label' => __('Vendor Email'),
                'title' => __('Vendor Email'),
                'required' => true,
                'class' => 'validate-email',
                ]
            );
            $fieldset->addField(
                'email',
                'hidden',
                [
                'name' => 'email',
                'class' => 'validate-email',
                ]
            );
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
            
            
            if(!$this->vendorHelper->isRemoved('address1', 'personal')){
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
            
            if(!$this->vendorHelper->isRemoved('address2', 'personal')){
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

            if(!$this->vendorHelper->isRemoved('country_id', 'personal')){
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
            if(!$this->vendorHelper->isRemoved('region', 'personal')){
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
                    '\Magedelight\Vendor\Block\Adminhtml\Vendor\Edit\Renderer\Region',
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
            
            
            if(!$this->vendorHelper->isRemoved('city', 'personal')){
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
            
            if(!$this->vendorHelper->isRemoved('pincode', 'personal')){
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
                    </script>'
                );
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
        
        $fieldset->addField(
            'vendor_group',
            'select',
            [
            'name' => 'vendor_group',
            'label' => __('Group'),
            'title' => __('Group'),
            'values' => $this->vendorGroups->toOptionArray(),
            'required' => true
            ]
        );
        
        if (!$vendor->getId() && $sessionData) {
            $vendor->setData('mobile', '+'.$sessionData['vendor']['country_code'].$sessionData['vendor']['mobile']);
        }
        $form->setValues($vendor->getData());
        $this->setForm($form);
        return $this;
    }
}
