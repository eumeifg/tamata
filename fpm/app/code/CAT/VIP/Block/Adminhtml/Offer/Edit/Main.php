<?php

namespace CAT\VIP\Block\Adminhtml\Offer\Edit;

use Magento\Backend\Block\Widget\Form\Generic as GenericForm;

class Main extends GenericForm
{

    /**
     * @var \Magedelight\Vendor\Model\Config\Source\Websites
     */
    protected $websites;

    /**
     * @var \Magento\Catalog\Model\ProductRepository
     */
    protected $productRepository;

    /**
     * @var \Magedelight\Vendor\Api\VendorRepositoryInterface
     */
    protected $vendorRepository;

    /**
     * @var \Magedelight\Catalog\Helper\Pricing\Data
     */
    protected $pricingHelper;

    protected $type;
    protected $group;
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magedelight\Catalog\Model\Config\Source\Websites $websites,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        \Magedelight\Vendor\Api\VendorRepositoryInterface $vendorRepository,
        \Magedelight\Catalog\Helper\Pricing\Data $pricingHelper,
        \CAT\VIP\Model\DiscountType $type,
        \Magento\CatalogRule\Model\Rule\CustomerGroupsOptionsProvider $group,

        array $data = []
    ) {
        $this->websites = $websites;
        $this->_productRepository = $productRepository;
        $this->storeManager = $context->getStoreManager();
        $this->vendorRepository = $vendorRepository;
        $this->pricingHelper = $pricingHelper;
        $this->type = $type;
        $this->group = $group;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Prepare form
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        parent::_prepareForm();

        /** @var $model \Ves\Brand\Model\Brand */
        $model = $this->_coreRegistry->registry('vip_offer');

        $productId = $this->_request->getParam('product_id', null);
        $productWebsites = [];
        if ($productId) {
            $product = $this->_productRepository->getById($productId);
            if ($product) {
                $productWebsites = $product->getWebsiteIds();
            }
        }
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();

        $htmlIdPrefix = 'vendor_offer_';
        $form->setHtmlIdPrefix($htmlIdPrefix);

        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('VIP Offer Information')]);
        $isElementDisabled = false;

        if ($model->getId()) {
            $pid = $model->getData('marketplace_product_id', false);
            if ($pid) {
                $fieldset->addField('marketplace_product_id', 'hidden', ['name' => 'marketplace_product_id']);
            }
            $fieldset->addField('vendor_id', 'hidden', ['name' => 'vendor_id']);
            $model->setBusinessName($this->vendorRepository->getById($model->getVendorId())->getBusinessName());
            $isElementDisabled = true;
        }

        $model->setData('product_link', $model->getName());

        $websites = $fieldset->addField(
            'website_id',
            'select',
            [
                    'name' => 'website_id',
                    'class' => 'custom-select',
                    'label' => __('Website'),
                    'title' => __('Website'),
                    'required' => true,
                    'values' => $this->websites->toOptionArray($productWebsites),
                    'disabled' => $isElementDisabled
                ]
        );
        if ($model->getId()) {
            $websiteId = $this->storeManager->getStore($model->getStoreId())->getWebsiteId();
            $fieldset->addField(
                'business_name',
                'link',
                [
                    'name' => 'business_name',
                    'label' => __('Vendor Name'),
                    'title' => __('Vendor Name'),
                    'href' => $this->getUrl(
                        'vendor/index/edit',
                        ['vendor_id' => $model->getVendorId(),'website_id' => $websiteId]
                    ),
                    'target' => '_blank'
                ]
            );
        } else {
            $fieldset->addField(
                'vendor_id',
                'select',
                [
                    'name' => 'vendor_id',
                    'class' => 'custom-select',
                    'label' => __('Vendor'),
                    'title' => __('Vendor'),
                    //'values' => $this->_vendors->toOptionArray(2),
                    'values' =>  ['--Please Select Website--'],
                    'required' => true,
                    'disabled' => $isElementDisabled
                ]
            );
        }

        if (!$isElementDisabled) {
            $form->getElement('vendor_id')->setAfterElementHtml('
                <script type="text/javascript">
                    require([
                         "jquery","customselect"
                    ], function($){
                        $(window).load(function() {
                            $(".custom-select").customselect();

                        });
                      });
                </script>
            ');

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
                       $('#edit_form').on('change', '#vendor_offer_website_id', function(event){
                          $.ajax({
                                url : '" . $this->getVendorListUrl() . "website/' +  $('#vendor_offer_website_id').val()+'/product_id/$productId',
                                type: 'get',
                                dataType: 'json',
                                showLoader:true,
                                success: function(data){
                                    $('#vendor_offer_vendor_id').customselect('destroy');
                                    $('#vendor_offer_vendor_id').html(data.htmlcontent);
                                    $('#vendor_offer_vendor_id').customselect();
                                }
                          });
                       })
                    }
                );
                </script>"
            );
        }

        $fieldset->addField(
            'ind_qty',
            'text',
            [
                'name' => 'ind_qty',
                'label' => __('Individual Qty'),
                'title' => __('Individual Qty'),
                'required' => true
            ]
        );
        $fieldset->addField(
            'global_qty',
            'text',
            [
                'name' => 'global_qty',
                'label' => __('Global Qty'),
                'title' => __('Global Qty'),
                'required' => true
            ]
        );
        $fieldset->addField(
            'discount_type',
            'select',
            [
                    'name' => 'discount_type',
                    'class' => 'custom-select',
                    'label' => __('Type'),
                    'title' => __('Type'),
                    'required' => true,
                    'values' => $this->type->getOptionArray()
                ]
        );
        $fieldset->addField(
            'discount',
            'text',
            [
                'name' => 'discount',
                'label' => __('Discount'),
                'title' => __('Discount'),
                'required' => true
            ]
        );
        $fieldset->addField(
            'customer_group',
            'multiselect',
            [
                'name' => 'customer_group',
                'label' => __('Customer Group'),
                'title' => __('Customer Group'),
                'required' => true,
                'values' => $this->group->toOptionArray()
            ]
        );
       
        $form->setValues($model->getData());
        $this->setForm($form);

        return $this;
    }

    /**
     * @return string
     */
    public function getVendorListUrl()
    {
        return $this->getUrl('viporders/offer/vendorList');
    }
}
