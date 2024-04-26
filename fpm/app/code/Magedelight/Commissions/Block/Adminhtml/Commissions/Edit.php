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
namespace Magedelight\Commissions\Block\Adminhtml\Commissions;

use Magedelight\Commissions\Api\Data\CommissionInterface;
use Magento\Backend\Block\Widget\Context;
use Magento\Backend\Block\Widget\Form\Container as FormContainer;
use Magento\Framework\Registry;

/**
 * @author Rocket Bazaar Core Team
 */
class Edit extends FormContainer
{
    const COLUMN_NAME = 'product_category';

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry = null;
    /**
     * @var CommissionInterface
     */
    protected $commission;

    /**
     * constructor
     *
     * @param Context $context
     * @param Registry $registry
     * @param Commission $commission
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        CommissionInterface $commission,
        array $data = []
    ) {
        $this->coreRegistry = $registry;
        $this->commission = $commission;
        parent::__construct($context, $data);
    }

    /**
     * Initialize commission edit block
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_objectId = 'commission_id';
        $this->_blockGroup = 'Magedelight_Commissions';
        $this->_controller = 'adminhtml_commissions';
        parent::_construct();
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

        $this->buttonList->update('delete', 'label', __('Delete'));
    }

    /**
     * Get edit form container header text
     *
     * @return string
     */
    public function getHeaderText()
    {
        $commission = $this->coreRegistry->registry('md_commissions_commission');
        if ($commission->getId()) {
            return __("Edit Commission '%1'", $this->escapeHtml($commission->getTitle()));
        } else {
            return __('New Commissions');
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
                if (tinyMCE.getInstanceById('commission_content') == null) {
                    tinyMCE.execCommand('mceAddControl', false, 'commission_content');
                } else {
                    tinyMCE.execCommand('mceRemoveControl', false, 'commission_content');
                }
            };
        ";

        $script = '
            require([
            "jquery" // jquery Library
            ], function($){
                jQuery( document ).ready(function() {
        ';
        $commissionColl = $this->commission->getResourceCollection();
        $commissionColl->addFieldToSelect(self::COLUMN_NAME);

        $commission = $this->coreRegistry->registry('md_commissions_commission');
        if ($commission) {
            $commissionColl->addFieldToFilter(self::COLUMN_NAME, ['neq' => $commission->getProductCategory()]);
            $commissionColl->addFieldToFilter('website_id', ['eq' => $commission->getWebsiteId()]);
        } else {
            $commissionColl->addFieldToFilter('website_id', ['eq' => $this->getRequest()->getParam("website", 1)]);
        }

        foreach ($commissionColl as $comm) {
            $script .= 'jQuery("#commission_product_category option[Value = ' .
                $comm->getData(self::COLUMN_NAME) . ']").attr("disabled",true);';
        }
        $script .=    '    
                });
            });
        ';
        $this->_formScripts[] = $script;
        return parent::_prepareLayout();
    }
}
