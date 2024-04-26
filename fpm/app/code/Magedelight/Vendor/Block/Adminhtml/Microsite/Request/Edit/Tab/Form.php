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
namespace Magedelight\Vendor\Block\Adminhtml\Microsite\Request\Edit\Tab;

use Magento\Backend\Block\Widget\Form\Generic as GenericForm;
use Magento\Backend\Block\Widget\Tab\TabInterface;

/**
 * Description of Form
 *
 * @author Rocket Bazaar Core Team
 */
class Form extends GenericForm implements TabInterface
{

    /**
     * @var \Magento\Cms\Model\Wysiwyg\Config
     */
    protected $_wysiwygConfig;

    /**
     * @var \Magedelight\Vendor\Model\VendorFactory
     */
    protected $_vendor;

    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $_systemStore;

    protected $storeManager;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry             $registry
     * @param \Magento\Framework\Data\FormFactory     $formFactory
     * @param \Magento\Store\Model\System\Store       $systemStore
     * @param \Magento\Cms\Model\Wysiwyg\Config       $wysiwygConfig
     * @param array                                   $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Cms\Model\Wysiwyg\Config $wysiwygConfig,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Store\Model\System\Store $systemStore,
        \Magedelight\Vendor\Model\VendorFactory $vendorFactory,
        array $data = []
    ) {
        $this->_wysiwygConfig = $wysiwygConfig;
        $this->_vendor = $vendorFactory;
        $this->_systemStore = $systemStore;
        $this->storeManager = $context->getStoreManager();
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Prepare form
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        $model = $this->_coreRegistry->registry('md_vendor_microsite');

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();

        $form->setHtmlIdPrefix('microsite_');

        $allowedFileExtensions = 'jpg,jpeg,png,gif';
        $allowedFileSize = "512KB";

        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Microsite Information')]);
        $fieldset->addType('image', \Magedelight\Vendor\Block\Adminhtml\Helper\Image::class);

        $isElementDisabled = false;
        if ($model->getId()) {
            $fieldset->addField('microsite_id', 'hidden', ['name' => 'microsite_id']);
            $fieldset->addField('vendor_id', 'hidden', ['name' => 'vendor_id']);
            $model->setWebsiteId($this->_storeManager->getStore($model->getStoreId())->getWebsiteId());
            $isElementDisabled = true;
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
                'values' => $this->_systemStore->getWebsiteValuesForForm(true),
                'note' => __('This field is just for filtering vendors and stores according to website.'),
                'disabled' => $isElementDisabled
            ]
        );

        if (!$isElementDisabled) {
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
                       $('#edit_form').on('change', '#microsite_website_id', function(event){
                          $.ajax({
                                url : '" . $this->getVendorListUrl() . "website/' +  $('#microsite_website_id').val(),
                                type: 'get',
                                dataType: 'json',
                                showLoader:true,
                                success: function(data){
                                    $('#microsite_vendor_name').customselect('destroy');
                                    $('#microsite_vendor_name').html(data.htmlcontent.vendors);
                                    $('#microsite_store_id').html(data.htmlcontent.stores);
                                    $('#microsite_vendor_name').customselect();
                                }
                          });
                       })
                    }
                );
                </script>"
            );
        }

        if (isset($model)) {
            $storeValues = $this->getStoreValuesForForm(
                $this->_storeManager->getStore($model->getStoreId())->getWebsiteId()
            );
        } else {
            $storeValues = $this->getStoreValuesForForm();
        }

        if (!$this->_storeManager->isSingleStoreMode()) {
            $field = $fieldset->addField(
                'store_id',
                'multiselect',
                [
                    'name' => 'stores[]',
                    'label' => __('Store View'),
                    'title' => __('Store View'),
                    'disabled' => true,
                    'required' => false,
                    'values' => $storeValues
                ]
            );
            $renderer = $this->getLayout()->createBlock(
                \Magento\Backend\Block\Store\Switcher\Form\Renderer\Fieldset\Element::class
            );
            $field->setRenderer($renderer);
        } else {
            $fieldset->addField(
                'store_id',
                'hidden',
                ['name' => 'stores[]', 'value' => $this->_storeManager->getStore(true)->getId()]
            );
            $model->setStoreId($this->_storeManager->getStore(true)->getId());
        }

        if (isset($model)) {
            $websiteId = $this->storeManager->getStore($model->getStoreId())->getWebsiteId();
            $fieldset->addField(
                'vendor_name',
                'link',
                [
                    'name' => 'vendor_name',
                    'label' => __('Vendor Name'),
                    'title' => __('Vendor Name'),
                    'href' => $this->getUrl(
                        'vendor/index/edit',
                        ['vendor_id' => $model->getVendorId(),'website_id' => $websiteId]
                    ),
                    'target' => '_blank',
                ]
            );
        } else {
            $itemsArray = [['label'=>__('--Please select--'),'value' => '']];
            $fieldset->addField(
                'vendor_name',
                'select',
                [
                    'name' => 'vendor_name',
                    'class' => 'custom-select',
                    'label' => __('Vendor Name'),
                    'title' => __('Vendor Name'),
                    'values' => $itemsArray,
                    'note' => __('This field consist of vendors who have not created their microsite yet for selected website.'),
                    'required' => true,
                ]
            )->setAfterElementHtml('
                <script type="text/javascript">
                    require([
                         "jquery",
                    ], function($){
                        $(window).load(function() {
                            $("#microsite_vendor_name").customselect();
                        });
                      });
               </script>
            ');
        }

        $fieldset->addField(
            'page_title',
            'text',
            [
            'name' => 'page_title',
            'label' => __('Page Title'),
            'title' => __('Page Title'),
            'required' => true
            ]
        );

        $fieldset->addField(
            'banner',
            'image',
            [
                'name' => 'banner',
                'label' => __('Banner'),
                'title' => __('Banner'),
                'path'=> $model->getBanner(),
                'required' => false,
                'note' => 'Allowed File extension(' . $allowedFileExtensions . ') and filsize(' . $allowedFileSize . ' MB).',
                'value' => $model->getBanner()
            ]
        )->setAfterElementHtml('
            <script>
                require([
                     "jquery",
                ], function($){
                    $(document).ready(function () {

                        if($("#microsite_banner").attr("src") == null){
                            $("#microsite_banner").addClass("microsite_banner-validation");
                        }else {
                            $("#microsite_banner").addClass("microsite_banner-validation");
                        }

                        $( "#microsite_banner" ).attr( "accept", "image/x-png,image/gif,image/jpeg,image/jpg,image/png" );
                    });
                  });
           </script>
        ')->setSubDir('microsite');

        $fieldset->addField(
            'url_key',
            'text',
            [
                'name' => 'url_key',
                'label' => __('Url Key'),
                'title' => __('Url Key'),
                'required' => true,
            ]
        );

        $fieldset->addField(
            'meta_keyword',
            'textarea',
            [
                'name' => 'meta_keyword',
                'label' => __('Meta Keywords'),
                'title' => __('Meta Keywords'),
            ]
        );

        $fieldset->addField(
            'meta_description',
            'textarea',
            [
                'name' => 'meta_description',
                'label' => __('Meta Description'),
                'title' => __('Meta Description'),
            ]
        );

        $fieldset->addField(
            'google_analytics_account_number',
            'textarea',
            [
                'name' => 'google_analytics_account_number',
                'label' => __('Google Analytics Account Number'),
                'title' => __('Google Analytics Account Number'),
            ]
        );

        $fieldset->addField(
            'short_description',
            'textarea',
            [
                'name' => 'short_description',
                'label' => __('Short Description'),
                'title' => __('Short Description'),
            ]
        );

        $fieldset->addField(
            'twitter_page',
            'text',
            [
                'name' => 'twitter_page',
                'label' => __('Twitter Page'),
                'title' => __('Twitter Page'),
            ]
        );

        $fieldset->addField(
            'google_page',
            'text',
            [
                'name' => 'google_page',
                'label' => __('Google Page'),
                'title' => __('Google Page'),
            ]
        );

        $fieldset->addField(
            'facebook_page',
            'text',
            [
                'name' => 'facebook_page',
                'label' => __('Facebook Page'),
                'title' => __('Facebook Page'),
            ]
        );

        $fieldset->addField(
            'tumbler_page',
            'text',
            [
                'name' => 'tumbler_page',
                'label' => __('Tumbler Page'),
                'title' => __('Tumbler Page'),
            ]
        );

        $fieldset->addField(
            'instagram_page',
            'text',
            [
                'name' => 'instagram_page',
                'label' => __('Instagram Page'),
                'title' => __('Instagram Page'),
            ]
        );

        $fieldset->addField(
            'delivery_policy',
            'textarea',
            [
                'name' => 'delivery_policy',
                'label' => __('Shipping and payment terms'),
                'title' => __('Shipping and payment terms')
            ]
        );

        $fieldset->addField(
            'return_policy',
            'textarea',
            [
                'name' => 'return_policy',
                'label' => __('Return Policy'),
                'title' => __('Return Policy')
            ]
        );

        $data = $model->getData();
        $banner = '';
        if (isset($data['banner']['value'])) {
            $banner = $data['banner']['value'];
            unset($data['banner']['value']);
        }

        if ($banner) {
            $data['banner'] = $banner;
        }

        $form->setValues($data);
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
        return __('Microsite Information');
    }

    /**
     * Prepare title for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return __('Microsite Information');
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

    /**
     * @return string
     */
    protected function getVendorListUrl()
    {
        return $this->getUrl('vendor/microsite_/index/vendorList');
    }

    /**
     * Retrieve store values for form
     * @param string $websiteId
     * @return array
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function getStoreValuesForForm($websiteId = null)
    {
        $options = [];

        $nonEscapableNbspChar = html_entity_decode('&#160;', ENT_NOQUOTES, 'UTF-8');
        $websiteGroups = [];
        foreach ($this->_storeManager->getWebsites() as $website) {
            if ($websiteId && $website->getId() != $websiteId) {
                continue;
            }

            $websiteShow = false;
            foreach ($website->getGroups() as $group) {
                $websiteGroups[$group->getId()] = $group;
            }
            foreach ($websiteGroups as $group) {
                if ($website->getId() != $group->getWebsiteId()) {
                    continue;
                }
                $groupShow = false;
                foreach ($this->_storeManager->getStores() as $store) {
                    if ($group->getId() != $store->getGroupId()) {
                        continue;
                    }
                    if (!$websiteShow) {
                        $options[] = ['label' => $website->getName(), 'value' => []];
                        $websiteShow = true;
                    }
                    if (!$groupShow) {
                        $groupShow = true;
                        $values = [];
                    }
                    $values[] = [
                        'label' => str_repeat($nonEscapableNbspChar, 4) . $store->getName(),
                        'value' => $store->getId(),
                    ];
                }
                if ($groupShow) {
                    $options[] = [
                        'label' => str_repeat($nonEscapableNbspChar, 4) . $group->getName(),
                        'value' => $values,
                    ];
                }
            }
        }
        return $options;
    }
}
