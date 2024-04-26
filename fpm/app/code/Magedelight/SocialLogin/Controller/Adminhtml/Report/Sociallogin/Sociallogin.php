<?php

namespace Magedelight\SocialLogin\Controller\Adminhtml\Report\Sociallogin;

use Magento\Reports\Model\Flag;

class Sociallogin extends \Magento\Reports\Controller\Adminhtml\Report\Sales
{
    
    /**
     * Category report action
     *
     * @return void
     */
    public function execute()
    {
       
        $this->_initAction()->_setActiveMenu(
            'Magedelight_SocialLogin::report_sociallogin'
        )->_addBreadcrumb(
            __('Social Login Report'),
            __('Social Login Report')
        );
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Social Login Report'));
       
        
        $gridBlock = $this->_view->getLayout()->getBlock('adminhtml_SocialLogin_SocialLogin.grid');
        
        $filterFormBlock = $this->_view->getLayout()->getBlock('grid.filter.form');
        
        $this->_initReportAction([$gridBlock, $filterFormBlock]);
        
        $this->_view->renderLayout();
    }
}
