<?php
 

namespace Ktpl\Pushnotification\Block\Adminhtml\Customer\Edit\Tabs;

use Magento\Customer\Controller\RegistryConstants;

class Pushnotification extends \Magento\Backend\Block\Template implements
    \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * @var string
     */
    protected $_template = 'tab/pushnotification_tab.phtml';

    public function __construct(
        \Mirasvit\Helpdesk\Api\Service\Customer\CustomerManagementInterface $customerManagement,
        \Magento\Framework\Registry $registry,
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Customer\Model\Customer $customerModel,
        array $data = []
    ) {
        parent::__construct($context, $data);

        $this->customerManagement = $customerManagement;
        $this->registry           = $registry;
        $this->customerModel      = $customerModel;
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

    public function getCustomerEmail()
    {   
        $customerId = $this->registry->registry(RegistryConstants::CURRENT_CUSTOMER_ID);
        $customer = $this->customerModel->load($customerId);
        return $customer->getEmail();
    }
}
