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
namespace Magedelight\Vendor\Block\Adminhtml\Request;

use Magento\Backend\Block\Widget\Form\Container as FormContainer;
use Magento\Backend\Block\Widget\Context;
use Magento\Framework\Registry;

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
        $this->_blockGroup = 'Magedelight_Vendor';
        $this->_controller = 'adminhtml_request';

        parent::_construct();

        if ($this->_isAllowedAction('Magedelight_Vendor::edit_request')) {
            $this->buttonList->update('save', 'label', __('Save'));
            $this->buttonList->add(
                'saveandcontinue',
                [
                'label' => __('Save and Continue Edit'),
                'class' => 'save',
                'data_attribute' => [
                    'mage-init' => ['button' => ['event' => 'saveAndContinueEdit', 'target' => '#edit_form']]
                    ]
                    ],
                -100
            );
        }

        $this->buttonList->update('delete', 'label', __('Delete'));
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
        return parent::_prepareLayout();
    }
}
