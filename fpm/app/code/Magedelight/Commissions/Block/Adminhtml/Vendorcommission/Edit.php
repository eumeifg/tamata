<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Commissions
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Commissions\Block\Adminhtml\Vendorcommission;

class Edit extends \Magento\Backend\Block\Widget\Form\Container
{
    protected function _construct()
    {
        $this->_objectId = 'vendor_commission_id';
        $this->_blockGroup = 'Magedelight_Commissions';
        $this->_controller = 'adminhtml_vendorcommission';

        parent::_construct();

        $this->buttonList->update('save', 'label', __('Save'));
        $this->buttonList->update('delete', 'label', __('Delete'));

        $this->buttonList->add(
            'saveandcontinue',
            [
                'label' => __('Save and Continue'),
                'class' => 'save',
                'data_attribute' => [
                    'mage-init' => ['button' => ['event' => 'saveAndContinueEdit', 'target' => '#edit_form']]
                ]
            ],
            -100
        );

        $this->buttonList->update(
            'save',
            'onclick',
            'deleteConfirm(' . '\'' . __(
                'Are you sure you want to do this?'
            ) . '\' ' . '\'' . $this->getUrl(
                '*/*/save',
                [$this->_objectId => $this->getRequest()->getParam($this->_objectId), 'ret' => 'pending']
            ) . '\'' . ')'
        );

        $this->_formScripts[] = "
            function toggleEditor() {
                if (tinyMCE.getInstanceById('block_content') == null) {
                    tinyMCE.execCommand('mceAddControl', false, 'hello_content');
                } else {
                    tinyMCE.execCommand('mceRemoveControl', false, 'hello_content');
                }
            }
        ";
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magedelight_Commissions::edit');
    }

    public function getHeaderText()
    {
        if ($this->_coreRegistry->registry('md_commissions_vendorcommission')->getId()) {
            return __("Edit Item '%1'", $this->escapeHtml(
                $this->_coreRegistry->registry('md_commissions_vendorcommission')->getTitle()
            ));
        } else {
            return __('New Item');
        }
    }
}
