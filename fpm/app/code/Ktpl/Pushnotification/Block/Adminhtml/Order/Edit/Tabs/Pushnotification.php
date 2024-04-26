<?php
 

namespace Ktpl\Pushnotification\Block\Adminhtml\Order\Edit\Tabs;

use Magento\Order\Controller\RegistryConstants;


class Pushnotification extends \Magento\Backend\Block\Template implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * @var string
     */
    protected $_template = 'tab/pushnotification_order_tab.phtml';

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function getTabLabel()
    {
        return __('Push Notification');
    }

    /**
     * {@inheritdoc}
     */
    public function getTabTitle()
    {
        return __('Push Notification');
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * Tab should be loaded trough Ajax call
     *
     * @return bool
     */
    public function isAjaxLoaded()
    {
        return false;
    }

    public function getOrder()
    {
        return $this->_coreRegistry->registry('current_order');
    }
    public function getOrderId()
    {
        return $this->getOrder()->getEntityId();
    }
    public function getOrderIncrementId()
    {
        return $this->getOrder()->getIncrementId();
    }
    public function getOrderEmail()
    {   
        return $this->getOrder()->getCustomerEmail();
    }
    public function getCustomerId()
    {   
        return $this->getOrder()->getCustomerId();
    }
    
}
