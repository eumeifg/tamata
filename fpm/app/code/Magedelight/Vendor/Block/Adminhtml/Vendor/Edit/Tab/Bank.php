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

use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magedelight\Vendor\Controller\RegistryConstants;
use Magedelight\Vendor\Model\Vendor;
use Magento\Store\Model\ScopeInterface;

class Bank extends Generic implements TabInterface
{
    /**
     * @var \Magedelight\Vendor\Helper\Data
     */
    protected $vendorHelper;
    
    /**
     *
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magedelight\Vendor\Helper\Data $vendorHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magedelight\Vendor\Helper\Data $vendorHelper,
        array $data = []
    ) {
        $this->vendorHelper = $vendorHelper;
        parent::__construct($context, $registry, $formFactory, $data);
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

    /**
     * Prepare label for tab
     * @return \Magento\Framework\Phrase
     */
    public function getTabLabel()
    {
        return __('Bank Details');
    }

    /**
     * Prepare title for tab
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return __('Bank Details');
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        $vendor = $this->_coreRegistry->registry(RegistryConstants::CURRENT_VENDOR);
        if (!$vendor->getIsUser() && $this->getConfigValue(
            Vendor::IS_ENABLED_BANKING_DETAILS_XML_PATH
        )
        ) {
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
     * Prepare form
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
            ['legend' => __('Bank Details'), 'class' => 'fieldset-wide']
        );

        $isBankInfoOptional = $this->getConfigValue(
            Vendor::IS_BANK_DETAILS_OPTIONAL_XML_PATH
        );

        $fieldset->addField(
            'bank_account_name',
            'text',
            [
                'name'     => 'bank_account_name',
                'label'    => __('Account Holder Name'),
                'title'    => __('Account Holder Name'),
                'required' => (!$isBankInfoOptional) ? true : false,
                'class' => 'validate-alpha-with-spaces-spl-150'
            ]
        );

        $fieldset->addField(
            'bank_account_number',
            'text',
            [
                'name'     => 'bank_account_number',
                'label'    => __('Bank Account Number'),
                'title'    => __('Bank Account Number'),
                'required' => (!$isBankInfoOptional) ? true : false,
                'class'    => 'validate-bank-account-number'
                
            ]
        );
        $fieldset->addField(
            'bank_name',
            'text',
            [
                'name'     => 'bank_name',
                'label'    => __('Bank Name'),
                'title'    => __('Bank Name'),
                'required' => (!$isBankInfoOptional) ? true : false,
                'class' => 'validate-alpha-with-spaces-spl-150'
            ]
        );

        $fieldset->addField(
            'ifsc',
            'text',
            [
                'name'     => 'ifsc',
                'label'    => __('IFSC'),
                'title'    => __('IFSC'),
                'required' => (!$isBankInfoOptional) ? true : false,
                'class'    => 'validate-ifsc-code',
                
            ]
        );
        /*->setAfterElementHtml('
            <script>
                require([
                     "jquery",
                ], function($){
                        $(document).ready(function () {
                           $("#vendor_ifsc").addClass("validate-ifsc-code");
                    });
                  });
           </script>
        ');*/

        $form->setValues($vendor->getData());
        $this->setForm($form);

        return parent::_prepareForm();
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
}
