<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_OffersImportExport
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\OffersImportExport\Block\Adminhtml\Offers\Export;

class Edit extends \Magento\Backend\Block\Widget\Form\Container
{
    
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    /**
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_blockGroup = 'Magedelight_OffersImportExport';
        $this->_controller = 'adminhtml_offers';

        $this->_formScripts[] = " require([
        'jquery'
        ], function ($) {
           $(document).ready(function(){
                $('#offers_vendor_data').on('click', function() {
                var vendorId = jQuery('#offers_vendor_id').val();
                var url = '".$this->getUrl('vendoroffers/offers/vendordata')."';
                url  = url + 'vendorId/'+vendorId;
                window.location.href = url;
            })
           });
        });";
    
        parent::_construct();

        $this->removeButton('reset');
        $this->removeButton('back');
        $this->removeButton('save');
        // $this->buttonList->update('save', 'label', __('Save Offers1'));
    }
    
     /**
      * Get form save URL
      *
      * @see getFormActionUrl()
      * @return string
      */
    public function getSaveUrl()
    {
        return $this->getUrl('vendoroffers/offers/save', ['_current' => true,'active_tab' => '']);
    }
}
