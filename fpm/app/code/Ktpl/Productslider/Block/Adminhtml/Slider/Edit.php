<?php
/**
 * Ktpl
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the KrishTechnolabs.com license that is
 * available through the world-wide-web at this URL:
 * https://https://www.KrishTechnolabs.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Ktpl
 * @package     Ktpl_Productslider
 * @copyright   Copyright (c) Ktpl (https://https://www.KrishTechnolabs.com//)
 * @license     https://https://www.KrishTechnolabs.com/LICENSE.txt
 */

namespace Ktpl\Productslider\Block\Adminhtml\Slider;

use Magento\Backend\Block\Widget\Context;
use Magento\Backend\Block\Widget\Form\Container;
use Magento\Framework\Registry;

/**
 * Class Edit
 * @package Ktpl\Productslider\Block\Adminhtml\Slider
 */
class Edit extends Container
{
    /**
     * Core registry
     *
     * @var Registry
     */
    protected $_coreRegistry;

    /**
     * Edit constructor.
     * @param Registry $coreRegistry
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        Registry $coreRegistry,
        Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);

        $this->_coreRegistry = $coreRegistry;
    }

    /**
     * Retrieve text for header element depending on loaded Slider
     *
     * @return string
     */
    public function getHeaderText()
    {
        $slider = $this->getSlider();
        if ($slider->getId()) {
            return __("Edit Slider '%1'", $this->escapeHtml($slider->getName()));
        }

        return __('New Slider');
    }

    /**
     * Get Slider
     *
     * @return mixed
     */
    public function getSlider()
    {
        return $this->_coreRegistry->registry('ktpl_productslider_slider');
    }

    /**
     * Initialize Slider edit block
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_objectId = 'id';
        $this->_blockGroup = 'Ktpl_Productslider';
        $this->_controller = 'adminhtml_slider';

        parent::_construct();

        $this->buttonList->add(
            'save-and-continue',
            [
                'label'          => __('Save and Continue Edit'),
                'class'          => 'save',
                'data_attribute' => [
                    'mage-init' => [
                        'button' => [
                            'event'  => 'saveAndContinueEdit',
                            'target' => '#edit_form'
                        ]
                    ]
                ]
            ],
            -100
        );

        $this->_formScripts[] = "
        require(['jquery'], function ($){
            $('#slider_product_type').on('change', function(){
                showHideProductTab();
            });
            showHideProductTab();

            function showHideProductTab(){
                if($('#slider_product_type').val() == 'custom'){
                    $('#slider_tabs_products').parent().show();
                } else {
                    $('#slider_tabs_products').parent().hide();
                }
            }
        });
        ";
    }
}
