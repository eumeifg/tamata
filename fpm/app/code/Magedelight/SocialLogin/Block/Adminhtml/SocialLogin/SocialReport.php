<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magedelight\SocialLogin\Block\Adminhtml\SocialLogin;

/**
 * Adminhtml sales report by category page content block
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class SocialReport extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * Template file
     *
     * @var string
     */
    protected $_template = 'socialreport/grid/container.phtml';

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
      
        $this->_blockGroup = 'Magedelight_SocialLogin';
        $this->_controller = 'adminhtml_SocialLogin_SocialLogin';
        $this->_headerText = __('Social Login Report');
        parent::_construct();

        $this->buttonList->remove('add');
        $this->addButton(
            'filter_form_submit',
            ['label' => __('Show SocialLogin Report'), 'onclick' => 'filterFormSubmit()', 'class' => 'primary']
        );
    }

    /**
     * Get filter URL
     *
     * @return string
     */
    public function getFilterUrl()
    {
        $this->getRequest()->setParam('filter', null);
        return $this->getUrl('*/*/sociallogin', ['_current' => true]);
    }
}
