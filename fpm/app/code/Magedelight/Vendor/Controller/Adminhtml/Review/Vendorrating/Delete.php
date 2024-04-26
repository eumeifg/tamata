<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Vendor
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Vendor\Controller\Adminhtml\Review\Vendorrating;

class Delete extends \Magedelight\Vendor\Controller\Adminhtml\Review\Vendorrating
{
    protected $_vendorratingFactory;
    protected $resultPageFactory;
    protected $vendorRating;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magedelight\Vendor\Model\VendorratingFactory $vendorratingFactory
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magedelight\Vendor\Model\VendorratingFactory $vendorratingFactory,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magedelight\Vendor\Model\Vendorrating $vendorRating
    ) {
        parent::__construct($context, $vendorratingFactory, $resultPageFactory);
        $this->_vendorratingFactory = $vendorratingFactory;
        $this->resultPageFactory = $resultPageFactory;
        $this->vendorRating = $vendorRating;
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magedelight_Vendor::delete_vendorrating');
    }

    public function execute()
    {
        $vendorratingId = $this->getRequest()->getParam('id');
        try {
            $vendorrating = $this->vendorRating->load($vendorratingId);
            $vendorrating->delete();
            $this->messageManager->addSuccess(
                __('Delete successfully !')
            );
        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
        }
        $this->_redirect('*/*/');
    }
}
