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
use Magedelight\Vendor\Model\Vendor;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;

class Business extends Generic implements TabInterface
{

    /**
     * @var \Magedelight\Vendor\Helper\Data
     */
    protected $vendorHelper;

    protected $_scopeConfig;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Store\Model\System\Store $systemStore
     * @param \Magedelight\Vendor\Helper\Data $vendorHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Store\Model\System\Store $systemStore,
        \Magedelight\Vendor\Helper\Data $vendorHelper,
        array $data = []
    ) {
        $this->vendorHelper = $vendorHelper;
        $this->_systemStore = $systemStore;
        $this->_scopeConfig = $context->getScopeConfig();
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * @return string
     */
    public function getTabLabel()
    {
        return __('Business Details');
    }

    /**
     * Prepare title for tab
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return __('Business Details');
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
     * Prepare form
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareForm()
    {
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();

        $vendor = $this->_coreRegistry->registry(RegistryConstants::CURRENT_VENDOR);

        $companyLogoSize = '';
        $companyLogoHeight = $this->vendorHelper->getConfigValue('vendor/general/company_logo_height');
        $companyLogoWidth =  $this->vendorHelper->getConfigValue('vendor/general/company_logo_width');
        $companyLogoSize .=  "(Allowed File Types : (JPG,JPEG,PNG), Max Size:";
        $companyLogoSize .= $this->vendorHelper->getFormattedFileSize(Vendor::DEFAULT_IMAGE_SIZE) . ".)";

        $form->setHtmlIdPrefix('vendor_');
        $form->setFieldNameSuffix('vendor');

        $fieldset = $form->addFieldset(
            'meta_fieldset',
            ['legend' => __('Business Details'), 'class' => 'fieldset-wide']
        );
        $fieldset->addType('file', \Magedelight\Vendor\Block\Adminhtml\Helper\File::class);
        $fieldset->addType('image', \Magedelight\Vendor\Block\Adminhtml\Helper\Image::class);

        $fieldset->addField(
            'business_name',
            'text',
            [
                'name'     => 'business_name',
                'label'    => __('Business Name'),
                'title'    => __('Business Name'),
                'required' => true,
                'class' => 'validate-alpha-with-spaces-spl-150'

            ]
        );

        if (!$this->vendorHelper->isRemoved('logo', 'business')) {
            $fieldset->addField(
                'logo',
                'image',
                [
                    'name'     => 'logo',
                    'label'    => __('Company Logo'),
                    'title'    => __('Company Logo'),
                    'note'     => $companyLogoWidth . ' X ' . $companyLogoHeight . ' ' . $companyLogoSize,
                    'required' => false
                ]
            )->setAfterElementHtml('
                <script>
                    require([
                         "jquery",
                    ], function($){
                        $(document).ready(function () {   
                        $("#vendor_logo").addClass("logo-size-validation validate-image-type");
                            $( "#vendor_logo" ).attr( "accept", "image/x-png,image/jpeg,image/jpg,image/png" );
                      });
                  });
               </script>
            ')->setSubDir('vendor/logo');
        }

        if (!$this->vendorHelper->isRemoved('vat', 'business')) {
            $fieldset->addField(
                'vat',
                'text',
                [
                    'name'     => 'vat',
                    'label'    => __('VAT'),
                    'title'    => __('VAT'),
                    'required' => true,
                    'class'    => 'validate-alpha-with-name-20'
                ]
            );
        }

        if (!$this->vendorHelper->isRemoved('vat_doc', 'business')) {
            $fieldset->addField(
                'vat_doc',
                'image',
                [
                    'name'     => 'vat_doc',
                    'label'    => __('VAT Document'),
                    'title'    => __('VAT Document'),
                    'accept'   => "image/png, image/jpg, image/jpeg",
                    'note'     => __(
                        'Allowed File Types : (JPG,JPEG,PNG), Max Size:%1.',
                        $this->vendorHelper->getFormattedFileSize(Vendor::DEFAULT_IMAGE_SIZE)
                    ),
                    'required' => true
                ]
            )->setAfterElementHtml('
                <script>
                    require([
                         "jquery",
                    ], function($){
                        $(document).ready(function () {                        
                            $("#vendor_vat_doc").addClass("vat-size-validation validate-image-type");
                            if($("#vendor_vat_doc_image").attr("src") == null){
                                $("#vendor_vat_doc").addClass("required-file");
                            }else {
                                $("#vendor_vat_doc").removeClass("required-file");
                            }
                            $( "#vendor_vat_doc" ).attr( "accept", "image/jpeg,image/jpg,image/png" );
                        });
                      });
               </script>
            ')->setSubDir('vendor/vat_doc');
        }

        if (!$this->vendorHelper->isRemoved('other_marketplace_profile', 'business')) {
            $fieldset->addField(
                'other_marketplace_profile',
                'text',
                [
                    'name'  => 'other_marketplace_profile',
                    'label' => __('Other Marketplace URL'),
                    'title' => __('Other Marketplace URL'),
                    'class' => 'validate-alpha-with-spaces-spl-150-address2'
                ]
            );
        }
        $data = $vendor->getData();

        $logoImage = '';
        if (!$this->vendorHelper->isRemoved('logo', 'business')) {
            if (isset($data['logo']['value'])) {
                $logoImage = $data['logo']['value'];
                unset($data['logo']['value']);
            }
        }

        $vatDoc = '';
        if (!$this->vendorHelper->isRemoved('vat_doc', 'business')) {
            if (isset($data['vat_doc']['value'])) {
                $vatDoc = $data['vat_doc']['value'];
                unset($data['vat_doc']['value']);
            }
        }

        if ($logoImage) {
            $data['logo'] = $logoImage;
        }

        if ($vatDoc) {
            $data['vat_doc'] = $vatDoc;
        }

        $form->setValues($data);
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
