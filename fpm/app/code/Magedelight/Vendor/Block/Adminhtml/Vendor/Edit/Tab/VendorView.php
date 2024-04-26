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

class VendorView extends Generic implements TabInterface
{
    /**
     * @var \Magedelight\Vendor\Helper\Data
     */
    protected $vendorHelper;

    /**
     * VendorView constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magedelight\Vendor\Helper\Data $vendorHelper
     * @param \Magento\Customer\Model\Address\Config $addressConfig
     * @param \Magento\Directory\Model\RegionFactory $regionFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magedelight\Vendor\Helper\Data $vendorHelper,
        \Magento\Customer\Model\Address\Config $addressConfig,
        \Magento\Directory\Model\RegionFactory $regionFactory,
        array $data = []
    ) {
        $this->regionFactory = $regionFactory;
        $this->_addressConfig = $addressConfig;
        $this->vendorHelper = $vendorHelper;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Prepare label for tab
     * @return \Magento\Framework\Phrase
     */
    public function getTabLabel()
    {
        return __('Vendor View');
    }

    /**
     * Prepare title for tab
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return __('Vendor View');
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
     * @param string $resourceId
     * @return bool
     */
    protected function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);
    }

    /**
     * @return mixed
     */
    public function getVendorData()
    {
        $vendor = $this->_coreRegistry->registry(RegistryConstants::CURRENT_VENDOR);
        return $vendor;
    }

    /**
     * @return mixed
     */
    public function getBillingAddressHtml()
    {
        $vendorData = $this->getVendorData();
        $regionId = $vendorData->getRegionId();
        $regionName = $this->getRegionNameById($regionId);

        $address['firstname'] = $vendorData->getName();

        $address['street'] = $vendorData->getAddress1() . " " . $vendorData->getAddress2();
        $address['city'] = $vendorData->getCity();

        $address['region'] = (!$this->vendorHelper->isRemoved('region', 'personal')) ? $regionName : '';
        $address['postcode'] = (!$this->vendorHelper->isRemoved('pincode', 'personal')) ?
            $vendorData->getPincode() : '';
        $address['country_id'] = $vendorData->getCountryId();
        $renderer = $this->_addressConfig->getFormatByCode('html')->getRenderer();
        return $renderer->renderArray($address);
    }

    /**
     * @return mixed
     */
    public function getPickupAddressHtml()
    {
        $vendorData = $this->getVendorData();
        $regionId = $vendorData->getPickupRegionId();
        $regionName = $this->getRegionNameById($regionId);

        $address['firstname'] = $vendorData->getName();
        $address['street'] = $vendorData->getPickupAddress1() . " " . $vendorData->getPickupAddress2();
        $address['city'] = $vendorData->getPickupCity();
        $address['region'] = (!$this->vendorHelper->isRemoved('pickup_region', 'shipping')) ?
            $regionName : '';
        $address['postcode'] = (!$this->vendorHelper->isRemoved('pickup_pincode', 'shipping')) ?
            $vendorData->getPickupPincode() : '';
        $address['country_id'] = $vendorData->getPickupCountryId();
        $renderer = $this->_addressConfig->getFormatByCode('html')->getRenderer();
        return $renderer->renderArray($address);
    }

    /**
     * @param $regionId
     * @return string
     */
    public function getRegionNameById($regionId)
    {
        try {
            $region = $this->regionFactory->create()->load($regionId);
            $regionName = $region->getName();
        } catch (\Exception $e) {
            $regionName = "";
        }
        return $regionName;
    }

    /**
     *
     * @param string $field
     * @param string $type
     * @return boolean
     */
    public function isRemoved($field = '', $type = '')
    {
        return $this->vendorHelper->isRemoved($field, $type);
    }

    /**
     *
     * @param string $field
     * @param int $storeId
     * @return mixed
     */
    public function getConfigValue($field, $storeId = null)
    {
        return $this->vendorHelper->getConfigValue($field, $storeId);
    }
}
