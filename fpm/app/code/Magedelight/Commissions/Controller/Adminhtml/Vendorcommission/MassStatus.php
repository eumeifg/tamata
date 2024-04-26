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

class MassStatus extends \Magedelight\Commissions\Controller\Adminhtml\Vendorcommission
{
    /**
     * @var \Magedelight\Commissions\Model\Vendorcommission
     */
    protected $vendorCommission;

    /**
     * @param \Magedelight\Commissions\Model\Vendorcommission $vendorCommission
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
        return $this->_authorization->isAllowed('Magedelight_Commissions::edit');
    }

    public function execute()
    {
        $vendorcommissionIds = $this->getRequest()->getParam('vendorcommission');
        $status = $this->getRequest()->getParam('vendor_status');
        if (!is_array($vendorcommissionIds) || empty($vendorcommissionIds)) {
            $this->messageManager->addError(__('Please select Vendor commission(s).'));
        } else {
            try {
                foreach ($vendorcommissionIds as $vendorcommissionId) {
                    $vendorcommission = $this->vendorCommission->load($vendorcommissionId);
                    $vendorcommission->setData('vendor_status', $status)
                            ->save();
                }
                $this->messageManager->addSuccess(
                    __('A total of %1 record(s) have been updated.', count($vendorcommissionIds))
                );
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/');
    }
}
