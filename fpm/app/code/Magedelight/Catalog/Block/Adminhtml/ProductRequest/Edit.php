<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Catalog
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Catalog\Block\Adminhtml\ProductRequest;

use Magento\Backend\Block\Widget\Form\Container as FormContainer;
use Magento\Backend\Block\Widget\Context;
use Magento\Framework\Registry;
use Magedelight\Catalog\Model\ProductRequest;

class Edit extends FormContainer
{
 /**
  * Core registry
  *
  * @var \Magento\Framework\Registry
  */
    protected $coreRegistry = null;

    /**
     * constructor
     *
     * @param Context $context
     * @param Registry $registry
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        array $data = []
    ) {

        $this->coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * Initialize Requestzones edit block
     *
     * @return void
     */
    protected function _construct()
    {

        $this->_objectId = 'id';
        $this->_blockGroup = 'Magedelight_Catalog';
        $this->_controller = 'adminhtml_productRequest';

        parent::_construct();
        $this->buttonList->remove('save');
        
        $objId = $this->getRequest()->getParam($this->_objectId);
        if (!empty($objId)) {
            $model = $this->coreRegistry->registry('vendor_product_request');
            
            if (empty($model->getData('parent_id'))) {
                if (((int)$model->getData(ProductRequest::STATUS_PARAM_NAME)) !== ProductRequest::STATUS_APPROVED) {
                    $this->addButton(
                        'approve',
                        [
                            'label' => __('Approve'),
                            'class' => 'approve primary',
                            'onclick' => 'approve()'
                        ]
                    );
                }
                if (((int)$model->getData(ProductRequest::STATUS_PARAM_NAME)) !== ProductRequest::STATUS_APPROVED &&
                    $model->getData('is_requested_for_edit') != 1
                ) {
                    $this->addButton(
                        'approveandlist',
                        [
                            'label' => __('Approve and List'),
                            'class' => 'approve primary',
                            'onclick' => 'approveAndEdit()'
                        ]
                    );
                }
                if (((int)$model->getData(ProductRequest::STATUS_PARAM_NAME)) !== ProductRequest::STATUS_DISAPPROVED) {
                    $this->addButton(
                        'disapprove',
                        [
                            'label' => __('Disapprove'),
                            'class' => 'disapprove primary',
                            'onclick' => 'disApprove()'
                        ]
                    );
                }
            }
        }

        //$this->buttonList->update('delete', 'label', __('Delete'));
    }

    /**
     * Get edit form container header text
     *
     * @return string
     */
    public function getHeaderText()
    {
        $request = $this->coreRegistry->registry('vendor_product_request');
        if ($request->getId()) {
            return __("Edit Product Request '%1'", $this->escapeHtml($request->getName()));
        } else {
            return __('New Product Request');
        }
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
                if (tinyMCE.getInstanceById('request_content') == null) {
                    tinyMCE.execCommand('mceAddControl', false, 'request_content');
                } else {
                    tinyMCE.execCommand('mceRemoveControl', false, 'request_content');
                }
            };
        ";
        $model = $this->coreRegistry->registry('vendor_product_request');
        if (empty($model->getData('parent_id'))) {
            if (((int)$model->getData(ProductRequest::STATUS_PARAM_NAME)) !== ProductRequest::STATUS_APPROVED) {
                $this->_formScripts[] = '
                    function approve() {
                        require([
                            "jquery",
                            "Magento_Ui/js/modal/confirm"
                        ], function($,confirmation) { // Variable that represents the `confirm` function
                            if($("#edit_form").valid()){
                                confirmation({
                                    title: "Approve Product",
                                    content: "Please Press Ok to approve the product!",
                                    actions: {
                                        confirm: function(){
                                            $("#productrequest_status").val(1);
                                            $("#edit_form").submit();
                                        }
                                    } 
                                });
                            }
                        });
                    };
                ';
            }
            if (((int)$model->getData(ProductRequest::STATUS_PARAM_NAME)) !== ProductRequest::STATUS_APPROVED) {
                $this->_formScripts[] = '
                    function approveAndEdit() {
                        require([
                            "jquery",
                            "Magento_Ui/js/modal/confirm"
                        ], function($,confirmation) { // Variable that represents the `confirm` function
                            if($("#edit_form").valid()){
                                confirmation({
                                    title: "Approve And List Product",
                                    content: "Please Press Ok to approve and list the product!",
                                    actions: {
                                        confirm: function(){
                                            $("#productrequest_status").val(1);
                                            $("#productrequest_can_list").val(1);
                                            $("#edit_form").submit();
                                        }
                                    } 
                                });
                            }
                        });
                    };
                ';
            }
            if (((int)$model->getData(ProductRequest::STATUS_PARAM_NAME)) !== ProductRequest::STATUS_DISAPPROVED) {
                $this->_formScripts[] = '
                    function disApprove() {
                        require([
                            "jquery",
                            "Magento_Ui/js/modal/prompt",
                            "Magento_Ui/js/modal/alert"
                        ], function($,prompt,alert) { // Variable that represents the `prompt` function
                            prompt({
                                title: "Disapprove Product",
                                content: "Please enter the reason to disapprove.",
                                actions: {
                                    confirm: function(msg){
                                        if (msg != null && msg != "") {
                                            $("#productrequest_status").val(2);
                                            $("#productrequest_disapprove_message").val(msg);
                                            $(".field-disapprove_message").show();
                                            $("#edit_form").submit();
                                        } else {
                                            alert({
                                                title: "Disapprove Product",
                                                content: "Please enter the reason to disapprove product.",
                                                actions: {
                                                    always: function(){}
                                                }
                                            });
                                        }
                                    }
                                } 
                            });
                        });
                    };
                ';
            }
        }
        return parent::_prepareLayout();
    }
    
    /**
     * Get URL for back (reset) button
     *
     * @return string
     */
    public function getBackUrl()
    {
        $params = [
            'existing' => $this->getRequest()->getParam('existing', 0),
            'status' => $this->getRequest()->getParam('status', 0)
        ];
        return $this->getUrl('*/*/', $params);
    }
}
