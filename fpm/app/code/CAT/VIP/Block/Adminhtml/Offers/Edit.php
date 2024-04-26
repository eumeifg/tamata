<?php
namespace CAT\VIP\Block\Adminhtml\Offers;

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
        $this->_blockGroup = 'CAT_VIP';
        $this->_controller = 'adminhtml_offers';

        $this->_formScripts[] = " require([
        'jquery'
        ], function ($) {
           $(document).ready(function(){
                $('#offers_sample').on('click', function () {
                    var selectedWebsiteId = $('#offers_website_id :selected').val();
                    var selectedVendorId = $('#offers_vendor_id :selected').val();
                    var url = '".$this->getUrl('viporders/offer/download/')."';
                    window.location.href = url;
                })
           });
        });";
    
        parent::_construct();
        $this->removeButton('reset');
        $this->removeButton('back');
        $this->buttonList->update('save', 'label', __('Save Offers'));
    }
    
     /**
      * Get form save URL
      *
      * @see getFormActionUrl()
      * @return string
      */
    public function getSaveUrl()
    {
        return $this->getUrl('viporders/offer/importsave', ['_current' => true,'active_tab' => '']);
    }
}
