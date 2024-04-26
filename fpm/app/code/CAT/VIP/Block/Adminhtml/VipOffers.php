<?php
namespace CAT\VIP\Block\Adminhtml;

class VipOffers extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * Block constructor
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_controller = 'adminhtml_VipOffers';
        $this->_blockGroup = 'CAT_VIP';
        $this->_blockRequest = 'CAT_VIP';
        $this->_headerText = __('VIP Offers');
        parent::_construct();
        
        $this->removeButton('add');
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
}
