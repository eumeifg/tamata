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
namespace Magedelight\Vendor\Block\Adminhtml\Category;

use Magedelight\Vendor\Model\Category\Request\Source\Status as RequestStatus;

class Edit extends \Magento\Backend\Block\Widget\Form\Container
{

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->registry = $registry;
        parent::__construct($context, $data);
    }

    protected function _construct()
    {
        $this->_objectId = 'request_id';
        $this->_blockGroup = 'Magedelight_Vendor';
        $this->_controller = 'adminhtml_category';

        parent::_construct();

        $model = $this->registry->registry('category_request');

        $objId = $this->getRequest()->getParam($this->_objectId);
        if (!empty($objId)) {
            if (((int)$model->getData(RequestStatus::STATUS_PARAM_NAME)) == RequestStatus::STATUS_PENDING) {
                $this->addButton(
                    'approve',
                    [
                        'label' => __('Approve'),
                        'class' => 'approve primary',
                        'onclick' => 'approve()'
                    ]
                );
            }

            if (((int)$model->getData(RequestStatus::STATUS_PARAM_NAME)) == RequestStatus::STATUS_PENDING) {
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

        $this->buttonList->remove('save');

//        if ($this->registry->registry('category_request')->getStatus() != RequestStatus::STATUS_PENDING) {
//            $this->buttonList->remove('save');
//        }

        $this->_formScripts[] = "
            require([
                'Magedelight_Vendor/js/request'
            ], function (request) {
                var config = {'deniedStatusValue':" . RequestStatus::STATUS_DENIED . "}
                request(config);
            });
        ";
    }

    protected function _prepareLayout()
    {
        $model = $this->registry->registry('category_request');

        if (((int)$model->getData(RequestStatus::STATUS_PARAM_NAME)) == RequestStatus::STATUS_PENDING) {
            $this->_formScripts[] = '
                    function approve() {
                        require([
                            "jquery",
                            "Magento_Ui/js/modal/confirm"
                        ], function($,confirmation) { // Variable that represents the `confirm` function
                            if($("#edit_form").valid()){
                                confirmation({
                                    title: "Approve Category",
                                    content: "Please Press Ok to approve the category!",
                                    actions: {
                                        confirm: function(){
                                            $("#request_status").val(1);
                                            $("#edit_form").submit();
                                        }
                                    } 
                                });
                            }
                        });
                    };
                ';
        }
        if (((int)$model->getData(RequestStatus::STATUS_PARAM_NAME)) == RequestStatus::STATUS_PENDING) {
            $this->_formScripts[] = '
                    function disApprove() {
                        require([
                            "jquery",
                            "Magento_Ui/js/modal/prompt",
                            "Magento_Ui/js/modal/alert"
                        ], function($,prompt,alert) { // Variable that represents the `prompt` function
                            prompt({
                                title: "Disapprove Category",
                                content: "Please enter the reason to disapprove.",
                                actions: {
                                    confirm: function(msg){
                                        if (msg != null && msg != "") {
                                            $("#request_status_description").val(msg);
                                            $("#request_status").val(2);
                                            $("#edit_form").submit();
                                        } else {
                                            alert({
                                                title: "Disapprove Category",
                                                content: "Please enter the reason to disapprove category.",
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

        return parent::_prepareLayout();
    }

    public function getHeaderText()
    {
        if ($this->registry->registry('category_request')->getId()) {
            return __(
                "Category Request '%1'",
                $this->escapeHtml($this->_coreRegistry->registry('category_request')->getId())
            );
        } else {
            return __('Category Request');
        }
    }
}
