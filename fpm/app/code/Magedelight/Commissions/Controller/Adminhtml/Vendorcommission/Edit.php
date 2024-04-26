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
namespace Magedelight\Commissions\Controller\Adminhtml\Vendorcommission;

class Edit extends \Magedelight\Commissions\Controller\Adminhtml\Vendorcommission
{
    /**
     * @var \Magedelight\Commissions\Model\Vendorcommission
     */
    protected $vendorCommission;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;
    /**
     * @var \Magento\Backend\Model\Session
     */
    protected $session;

    /**
     * @param \Magedelight\Commissions\Model\Vendorcommission $vendorCommission
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Backend\Model\Session $session
     */

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magedelight\Commissions\Model\VendorcommissionFactory $vendorcommissionFactory,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magedelight\Commissions\Model\Vendorcommission $vendorCommission,
        \Magento\Framework\Registry $registry,
        \Magento\Backend\Model\Session $session
    ) {
        parent::__construct($context, $vendorcommissionFactory, $resultPageFactory);
        $this->vendorCommission = $vendorCommission;
        $this->registry = $registry;
        $this->session = $session;
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magedelight_Commissions::view_detail');
    }

    public function execute()
    {
        $id = $this->getRequest()->getParam('vendor_commission_id');
        $this->_view->loadLayout();
        if ($id) {
            $this->vendorCommission->load($id);
            if (!$this->vendorCommission->getId()) {
                $this->messageManager->addError(__('This Vendor commission type no longer exists.'));
                $this->_redirect('*/*/');
                return;
            }
            $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Edit Vendor commission'));
            $this->_view->getPage()->getLayout()->unsetChild("page.main.actions", "store_switcher");
        } else {
            $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Add New Vendor commission'));
        }
        $data = $this->session->getFormData(true);
        if (!empty($data)) {
            $this->vendorCommission->setData($data);
        }
        $this->registry->register('md_commissions_vendorcommission', $this->vendorCommission);
        $this->_view->getLayout()->initMessages();
        $this->_view->renderLayout();
    }
}
