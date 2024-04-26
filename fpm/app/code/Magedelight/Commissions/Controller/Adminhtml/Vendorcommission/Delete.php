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

class Delete extends \Magedelight\Commissions\Controller\Adminhtml\Vendorcommission
{
    /**
     * @var Magedelight\Commissions\Model\Vendorcommission
     */
    protected $vendorCommission;
    
    /**
     * @param Magedelight\Commissions\Model\Vendorcommission $vendorCommission
     */

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magedelight\Commissions\Model\VendorcommissionFactory $vendorcommissionFactory,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magedelight\Commissions\Model\Vendorcommission $vendorCommission
    ) {
        parent::__construct($context, $vendorcommissionFactory, $resultPageFactory);
        $this->vendorCommission = $vendorCommission;
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magedelight_Commissions::manage_delete');
    }

    public function execute()
    {
        $vendorcommissionId = $this->getRequest()->getParam('vendor_commission_id');
        try {
            $vendorcommission = $this->vendorCommission->load($vendorcommissionId);
            $vendorcommission->delete();
            $this->messageManager->addSuccess(
                __('Delete successfully !')
            );
        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
        }
        $this->_redirect('*/*/');
    }
}
