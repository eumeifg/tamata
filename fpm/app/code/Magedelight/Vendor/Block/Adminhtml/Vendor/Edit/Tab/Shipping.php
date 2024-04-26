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
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;

class Shipping extends Generic implements TabInterface
{

    /**
     * @var \Magedelight\Theme\Model\Source\DefaultCountry
     */
    protected $defaultCountry;

    /**
     * @var \Magedelight\Theme\Model\Source\Region
     */
    protected $region;

    /**
     * @var \Magento\Directory\Helper\Data
     */
    protected $directoryHelper;

    /**
     * @var \Magedelight\Vendor\Helper\Data
     */
    protected $vendorHelper;

    protected $backendSession;

    /**
     *
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magedelight\Theme\Model\Source\Region $region
     * @param \Magedelight\Theme\Model\Source\DefaultCountry $defaultCountry
     * @param \Magento\Directory\Helper\Data $directoryHelper
     * @param \Magedelight\Vendor\Helper\Data $vendorHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magedelight\Theme\Model\Source\Region $region,
        \Magedelight\Theme\Model\Source\DefaultCountry $defaultCountry,
        \Magento\Directory\Helper\Data $directoryHelper,
        \Magedelight\Vendor\Helper\Data $vendorHelper,
        array $data = []
    ) {
        $this->region = $region;
        $this->defaultCountry = $defaultCountry;
        $this->directoryHelper = $directoryHelper;
        $this->backendSession = $context->getBackendSession();
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
        $form->setHtmlIdPrefix('vendor_');
        $form->setFieldNameSuffix('vendor');

        $fieldset = $form->addFieldset(
            'meta_fieldset',
            ['legend' => __('Pickup and Shipping Information'), 'class' => 'fieldset-wide']
        );

        if (!$this->vendorHelper->isRemoved('pickup_address1', 'shipping')) {
            $fieldset->addField(
                'pickup_address1',
                'text',
                [
                'name' => 'pickup_address1',
                'label' => __('Address Line 1'),
                'title' => __('Address Line 1'),
                'required' => true,
                'class' => 'validate-alpha-with-spaces-spl-150'

                ]
            );
        }

        if (!$this->vendorHelper->isRemoved('pickup_address2', 'shipping')) {
            $fieldset->addField(
                'pickup_address2',
                'text',
                [
                'name' => 'pickup_address2',
                'label' => __('Address Line 2'),
                'title' => __('Address Line 2'),
                'class' => 'validate-alpha-with-spaces-spl-150-address2'

                ]
            );
        }

        if (!$this->vendorHelper->isRemoved('pickup_country_id', 'shipping')) {
            $countryOptions = $this->directoryHelper->getCountryCollection()->toOptionArray();

            $fieldset->addField(
                'pickup_country_id',
                'select',
                [
                'name' => 'pickup_country_id',
                'label' => __('Country'),
                'title' => __('Country'),
                'values' => $countryOptions,
                'required' => true
                ]
            );
        }

        if (!$this->vendorHelper->isRemoved('pickup_region', 'shipping')) {
            $regions = [];
            $currentSelectedRegion = '';
            $currentRegion = '';
            $sessionData = $this->backendSession->getVendorData();
            if ($vendor->getId()) {
                $regions = $this->region->toOptionArray($vendor->getPickupCountryId());
                $currentSelectedRegion = $vendor->getPickupRegionId();
                $currentRegion = $vendor->getPickupRegion();
            } elseif ($sessionData && array_key_exists('pickup_country_id', $sessionData['vendor'])) {
                $regions = $this->region->toOptionArray($sessionData['vendor']['pickup_country_id']);
                if (array_key_exists('pickup_region_id', $sessionData['vendor'])) {
                    $currentSelectedRegion = $sessionData['vendor']['pickup_region_id'];
                } elseif (array_key_exists('pickup_region', $sessionData['vendor'])) {
                    $currentRegion = $sessionData['vendor']['pickup_region'];
                }
            }

            $region = $fieldset->addField(
                'pickup_region',
                \Magedelight\Vendor\Block\Adminhtml\Vendor\Edit\Renderer\Region::class,
                [
                    'name' => 'region',
                    'label' => __('State'),
                    'title' => __('State'),
                    ]
            );

            $region->setElementText('pickup_region')
                ->setElementSelect('pickup_region_id')
                ->setElementCountry('pickup_country_id')
                ->setCurrentRegion($currentRegion)
                ->setCurrentSelectedRegion($currentSelectedRegion)
                ->setRegions($regions);
        }

        if (!$this->vendorHelper->isRemoved('pickup_city', 'shipping')) {
            $fieldset->addField(
                'pickup_city',
                'text',
                [
                'name' => 'pickup_city',
                'label' => __('City'),
                'title' => __('City'),
                'required' => true,
                'class' => 'validate-alpha-with-spaces-name-50',

                ]
            );
        }

        if (!$this->vendorHelper->isRemoved('pickup_pincode', 'shipping')) {
            $isZipRequired = $this->region->getIsZipRequired($this->region->getDefaultCountry());
            $fieldset->addField(
                'pickup_pincode',
                'text',
                [
                'name' => 'pickup_pincode',
                'label' => __('Pincode'),
                'title' => __('Pincode'),
                'required' => $isZipRequired,
                'class'    => 'validate-pin-code',

                ]
            );
        }
        /*
         * DeliveryZones module has been Disabled
         *
        if (is_null($vendor->getDeliveryZonesIds())) {
            $vendor->setDeliveryZonesIds($vendor->getDeliveryZoneIds());
        } */
        $form->setValues($vendor->getData());
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
        return __('Pickup and Shipping Information');
    }

    /**
     * Prepare title for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return __('Pickup and Shipping Information');
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        $vendor = $this->_coreRegistry->registry(RegistryConstants::CURRENT_VENDOR);
        if (!$vendor->getIsUser()) {
            return true;
        }
        return false;
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
