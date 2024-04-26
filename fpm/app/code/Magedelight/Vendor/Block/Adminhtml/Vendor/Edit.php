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
namespace Magedelight\Vendor\Block\Adminhtml\Vendor;

use Magedelight\Vendor\Controller\RegistryConstants;
use Magedelight\Vendor\Model\Source\Status as VendorStatus;

/**
 * Vendor edit block
 */
class Edit extends \Magento\Backend\Block\Widget\Form\Container
{
    const XML_PATH_GENERAL_COUNTRY_DEFAULT = 'general/country/default';

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     *
     * @var \Magedelight\Vendor\Model\Vendor
     */
    protected $_vendor;

    /**
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * Initialize vendor edit block
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_objectId = 'vendor_id';
        $this->_blockGroup = 'Magedelight_Vendor';
        $this->_controller = 'adminhtml_vendor';

        $vendorId = $this->getVendorId();
        $this->_vendor = $this->_coreRegistry->registry(RegistryConstants::CURRENT_VENDOR);

        parent::_construct();

        if ($this->_isAllowedAction('Magedelight_Vendor::edit_vendor')) {
            $this->buttonList->update('save', 'label', __('Save Vendor'));
            if ($this->getRequest()->getParam('vendor_id')) {
                $this->buttonList->add(
                    'saveandcontinue',
                    [
                    'label' => __('Save and Continue Edit'),
                    'class' => 'save',
                    'data_attribute' => [
                        'mage-init' => [
                            'button' => ['event' => 'saveAndContinueEdit', 'target' => '#edit_form'],
                        ],
                    ]
                    ],
                    -100
                );
            }
        } else {
            $this->buttonList->remove('save');
        }

        if (($this->_vendor->getId() && $this->_vendor->getStatus() == VendorStatus::VENDOR_STATUS_PENDING) ||
            $this->_vendor->getIsUser()) {
            $this->buttonList->update('delete', 'label', __('Delete Vendor'));
        } else {
            $this->buttonList->remove('delete');
        }

        if ($this->_vendor->getId() &&
            $this->_vendor->getStatus() == VendorStatus::VENDOR_STATUS_PENDING &&
            !$this->_vendor->getEmailVerified()) {
            $url = $this->getUrl(
                'vendor/index/resendConfirmation',
                ['vendor_id' => $vendorId,'website_id'=>$this->_vendor->getWebsiteId()]
            );
            $this->buttonList->add(
                'resend_confirmation',
                [
                    'label' => __('Send Confirmation Email'),
                    'onclick' => 'setLocation(\'' . $url . '\')',
                    'class' => 'resend resend-email'
                ],
                0
            );
        }

        if ($vendorId && $this->_vendor->getEmailVerified()) {
            $url = $this->getUrl(
                'vendor/index/resetPassword',
                ['vendor_id' => $vendorId,'website_id'=>$this->_vendor->getWebsiteId()]
            );
            $this->buttonList->add(
                'reset_password',
                [
                    'label' => __('Reset Password'),
                    'onclick' => 'setLocation(\'' . $url . '\')',
                    'class' => 'reset reset-password'
                ],
                0
            );
        }
    }

    /**
     * Retrieve text for header element depending on loaded page
     *
     * @return \Magento\Framework\Phrase
     */
    public function getHeaderText()
    {
        /*if ($this->_coreRegistry->registry('md_vendor')->getId()) {
            return __("Edit Vendor '%1'", $this->escapeHtml($this->_viewHelper->getVendorName($vendor)));
            return __("Edit Vendor '%1'", $this->escapeHtml($this->_coreRegistry->registry('md_vendor')->getName()));
        } else {
            return __('New Vendor');
        }*/
        if (!$this->_coreRegistry->registry('md_vendor')->getId()) {
            return __('New Vendor');
        }
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

    /**
     * Getter of url for "Save and Continue" button
     * tab_id will be replaced by desired by JS later
     *
     * @return string
     */
    protected function _getSaveAndContinueUrl()
    {
        return $this->getUrl(
            'vendor/*/save',
            ['_current' => true, 'back' => 'edit', 'active_tab' => '{{tab_id}}']
        );
    }

    /**
     * Prepare layout
     *
     * @return \Magento\Framework\View\Element\AbstractBlock
     */
    protected function _prepareLayout()
    {
        $this->_formScripts[] = "
            function toggleEditor() {
                if (tinyMCE.getInstanceById('page_content') == null) {
                    tinyMCE.execCommand('mceAddControl', false, 'page_content');
                } else {
                    tinyMCE.execCommand('mceRemoveControl', false, 'page_content');
                }
            };
        ";

        /*$this->_formScripts[] = "
            require([
                'jquery', // jquery Library
                'jquery/ui', // Jquery UI Library
                'jquery/validate', // Jquery Validation Library
                'mage/mage',
                'mage/translate', // Magento text translate (Validation message translte as per language)
                'intltelinpututil', // International telephone validation utils script
                'intltelinput' // International telephone validation
            ], function($){
                var telInput = jQuery(\"#vendor_mobile\");
                var defaultCountry = \"".strtolower($this->getDefaultCountry())."\";
                telInput.intlTelInput({
                    onlyCountries:[defaultCountry],
                    defaultCountry: defaultCountry,
                    responsiveDropdown: true,
                    nationalMode: 1,
                    autoFormat: 0
                });
                $.validator.addMethod(
                    'validate-phone-international',
                    function (v) {
                        return telInput.intlTelInput(\"isValidNumber\")
                    },
                    $.mage.__('Please enter a valid mobile number.')
                );
                $('.number-prefix-new').text('+' + $('.country-list li.country.active').attr('data-dial-code'));
            });";*/

        return parent::_prepareLayout();
    }
    /**
     * Return the Vendor Id.
     *
     * @return int|null
     */
    public function getVendorId()
    {
        $vendorId = $this->_coreRegistry->registry(RegistryConstants::CURRENT_VENDOR_ID);
        return $vendorId;
    }

    /**
     * @return mixed
     */
    public function getDefaultCountry()
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;

        return $this->_scopeConfig->getValue(self::XML_PATH_GENERAL_COUNTRY_DEFAULT, $storeScope);
    }

    /**
     * Get URL for back (reset) button
     *
     * @return string
     */
    public function getBackUrl()
    {
        if ($this->_vendor->getId()) {
            switch ($this->_vendor->getStatus()) {
                case VendorStatus::VENDOR_STATUS_PENDING:
                    return $this->getUrl('*/*/');
                case VendorStatus::VENDOR_STATUS_DISAPPROVED:
                    return $this->getUrl('*/*/rejected');
                case VendorStatus::VENDOR_STATUS_INACTIVE:
                    return $this->getUrl('*/*/inactive');
                default:
                    return $this->getUrl('*/*/approved');
            }
        }
        return $this->getUrl('*/*/');
    }
}
