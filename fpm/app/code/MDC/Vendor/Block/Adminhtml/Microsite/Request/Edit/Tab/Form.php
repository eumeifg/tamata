<?php
/**
 * KrishTechnolabs
 *
 * PHP version 7
 *
 * @category  KrishTechnolabs
 * @package   MDC_Vendor
 * @copyright 2019 (c) KrishTechnolabs (https://www.KrishTechnolabs.com/)
 * @license   https://www.krishtechnolabs.com/LICENSE.txt Krish License
 * @link      https://www.krishtechnolabs.com/
 */
namespace MDC\Vendor\Block\Adminhtml\Microsite\Request\Edit\Tab;

class Form extends \Magedelight\Vendor\Block\Adminhtml\Microsite\Request\Edit\Tab\Form
{

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

        $allowedFileExtensions = 'jpg,jpeg,png,gif' ;
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
                    'href' => $this->getUrl('vendor/index/edit', [
                        'vendor_id' => $model->getVendorId(),
                        'website_id' => $websiteId
                        ]),
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
                'note' => 'Allowed File extension('.$allowedFileExtensions.') and filsize('.$allowedFileSize.' MB).',
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

                        $( "#microsite_banner" ).attr(
                            "accept",
                            "image/x-png,image/gif,image/jpeg,image/jpg,image/png"
                        );
                    });
                  });
           </script>
        ')->setSubDir('microsite');
        $fieldset->addField(
            'banner_associate_url',
            'text',
            [
            'name' => 'banner_associate_url',
            'label' => __('Banner Associate Url'),
            'title' => __('Banner Associate Url'),
            'required' => false
            ]
        );
        $fieldset->addField(
            'promo_banner_1',
            'image',
            [
                'name' => 'promo_banner_1',
                'label' => __('Promotional Banner 1'),
                'title' => __('Promotional Banner 1'),
                'path'=> $model->getPromoBanner1(),
                'required' => false,
                'value' => $model->getPromoBanner1()
            ]
        )->setAfterElementHtml('
            <script>
                require([
                     "jquery",
                ], function($){
                    $(document).ready(function () {
                        $( "#microsite_promo_banner_1" ).attr(
                            "accept",
                            "image/x-png,image/gif,image/jpeg,image/jpg,image/png"
                        );
                    });
                  });
           </script>
        ')->setSubDir('microsite/promo_banners');
        $fieldset->addField(
            'promo_banner_associate_url1',
            'text',
            [
            'name' => 'promo_banner_associate_url1',
            'label' => __('Promotional Banner Associate Url'),
            'title' => __('Promotional Banner Associate Url'),
            'required' => false
            ]
        );
        $fieldset->addField(
            'promo_banner_2',
            'image',
            [
                'name' => 'promo_banner_2',
                'label' => __('Promotional Banner 2'),
                'title' => __('Promotional Banner 2'),
                'path'=> $model->getPromoBanner2(),
                'required' => false,
                'value' => $model->getPromoBanner2()
            ]
        )->setAfterElementHtml('
            <script>
                require([
                     "jquery",
                ], function($){
                    $(document).ready(function () {
                        $( "#microsite_promo_banner_2" ).attr(
                            "accept",
                            "image/x-png,image/gif,image/jpeg,image/jpg,image/png"
                        );
                    });
                  });
           </script>
        ')->setSubDir('microsite/promo_banners');
        $fieldset->addField(
            'promo_banner_associate_url2',
            'text',
            [
            'name' => 'promo_banner_associate_url2',
            'label' => __('Promotional Banner Associate Url 2'),
            'title' => __('Promotional Banner Associate Url 2'),
            'required' => false
            ]
        );
        $fieldset->addField(
            'promo_banner_3',
            'image',
            [
                'name' => 'promo_banner_3',
                'label' => __('Promotional Banner 3'),
                'title' => __('Promotional Banner 3'),
                'path'=> $model->getPromoBanner3(),
                'required' => false,
                'value' => $model->getPromoBanner3()
            ]
        )->setAfterElementHtml('
            <script>
                require([
                     "jquery",
                ], function($){
                    $(document).ready(function () {
                        $( "#microsite_promo_banner_3" ).attr(
                            "accept",
                            "image/x-png,image/gif,image/jpeg,image/jpg,image/png"
                        );
                    });
                  });
           </script>
        ')->setSubDir('microsite/promo_banners');
        $fieldset->addField(
            'promo_banner_associate_url3',
            'text',
            [
            'name' => 'promo_banner_associate_url3',
            'label' => __('Promotional Banner Associate Url 3'),
            'title' => __('Promotional Banner Associate Url 3'),
            'required' => false
            ]
        );
        $fieldset->addField(
            'mobile_promo_banner_1',
            'image',
            [
                'name' => 'mobile_promo_banner_1',
                'label' => __('Mobile Promotional Banner 1'),
                'title' => __('Mobile Promotional Banner 1'),
                'path'=> $model->getMobilePromoBanner1(),
                'required' => false,
                'value' => $model->getMobilePromoBanner1()
            ]
        )->setAfterElementHtml('
            <script>
                require([
                     "jquery",
                ], function($){
                    $(document).ready(function () {
                        $( "#microsite_mobile_promo_banner_1" ).attr(
                            "accept",
                            "image/x-png,image/gif,image/jpeg,image/jpg,image/png"
                        );
                    });
                  });
           </script>
        ')->setSubDir('microsite/promo_banners');
        $fieldset->addField(
            'mobile_banner_associate_url',
            'text',
            [
            'name' => 'mobile_banner_associate_url',
            'label' => __('Mobile Banner Associate Url'),
            'title' => __('Mobile Banner Associate Url'),
            'required' => false
            ]
        );

        $fieldset->addField(
            'mobile_promo_banner_2',
            'image',
            [
                'name' => 'mobile_promo_banner_2',
                'label' => __('Mobile Promotional Banner 2'),
                'title' => __('Mobile Promotional Banner 2'),
                'path'=> $model->getMobilePromoBanner2(),
                'required' => false,
                'value' => $model->getMobilePromoBanner2()
            ]
        )->setAfterElementHtml('
            <script>
                require([
                     "jquery",
                ], function($){
                    $(document).ready(function () {
                        $( "#microsite_mobile_promo_banner_2" ).attr(
                            "accept",
                            "image/x-png,image/gif,image/jpeg,image/jpg,image/png"
                        );
                    });
                  });
           </script>
        ')->setSubDir('microsite/promo_banners');
        $fieldset->addField(
            'mobile_banner_associate_url2',
            'text',
            [
            'name' => 'mobile_banner_associate_url2',
            'label' => __('Mobile Banner Associate Url 2'),
            'title' => __('Mobile Banner Associate Url 2'),
            'required' => false
            ]
        );

        $fieldset->addField(
            'mobile_promo_banner_3',
            'image',
            [
                'name' => 'mobile_promo_banner_3',
                'label' => __('Mobile Promotional Banner 3'),
                'title' => __('Mobile Promotional Banner 3'),
                'path'=> $model->getMobilePromoBanner3(),
                'required' => false,
                'value' => $model->getMobilePromoBanner3()
            ]
        )->setAfterElementHtml('
            <script>
                require([
                     "jquery",
                ], function($){
                    $(document).ready(function () {
                        $( "#microsite_mobile_promo_banner_3" ).attr(
                            "accept",
                            "image/x-png,image/gif,image/jpeg,image/jpg,image/png"
                        );
                    });
                  });
           </script>
        ')->setSubDir('microsite/promo_banners');
        $fieldset->addField(
            'mobile_banner_associate_url3',
            'text',
            [
            'name' => 'mobile_banner_associate_url3',
            'label' => __('Mobile Banner Associate Url 3'),
            'title' => __('Mobile Banner Associate Url 3'),
            'required' => false
            ]
        );
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
        if (isset($data['banner']['value'])) {
            $data['banner'] = $data['banner']['value'];
            unset($data['banner']['value']);
        }
        if (isset($data['promo_banner_1']['value'])) {
            $data['promo_banner_1'] = $data['promo_banner_1']['value'];
            unset($data['promo_banner_1']['value']);
        }
        if (isset($data['promo_banner_2']['value'])) {
            $data['promo_banner_2'] = $data['promo_banner_2']['value'];
            unset($data['promo_banner_2']['value']);
        }
        if (isset($data['promo_banner_3']['value'])) {
            $data['promo_banner_3'] = $data['promo_banner_3']['value'];
            unset($data['promo_banner_3']['value']);
        }

        if (isset($data['mobile_promo_banner_1']['value'])) {
            $data['mobile_promo_banner_1'] = $data['mobile_promo_banner_1']['value'];
            unset($data['mobile_promo_banner_1']['value']);
        }
        if (isset($data['mobile_promo_banner_2']['value'])) {
            $data['mobile_promo_banner_2'] = $data['mobile_promo_banner_2']['value'];
            unset($data['mobile_promo_banner_2']['value']);
        }
        if (isset($data['mobile_promo_banner_3']['value'])) {
            $data['mobile_promo_banner_3'] = $data['mobile_promo_banner_3']['value'];
            unset($data['mobile_promo_banner_3']['value']);
        }

        $form->setValues($data);
        $this->setForm($form);

        return $this;
    }
}
