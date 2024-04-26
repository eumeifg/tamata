<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Shippingmatrix
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Shippingmatrix\Controller\Adminhtml\Shippingmethod;

class Delete extends \Magedelight\Shippingmatrix\Controller\Adminhtml\Shippingmethod
{

    /**
     * @var \Magedelight\Shippingmatrix\Model\ShippingMethodFactory
     */
    protected $shippingMethodFactory;
    
    /**
     * @var \Magedelight\Shippingmatrix\Model\ShippingmatrixFactory
     */
    protected $shippingmatrixFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magedelight\Shippingmatrix\Model\ShippingMethodFactory $shippingMethodFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magedelight\Shippingmatrix\Model\ShippingMethodFactory $shippingMethodFactory,
        \Magedelight\Shippingmatrix\Model\ShippingmatrixFactory $shippingmatrixFactory
    ) {
        $this->shippingMethodFactory = $shippingMethodFactory;
        $this->shippingmatrixFactory = $shippingmatrixFactory;
        parent::__construct($context);
    }

    /**
     * Check Grid List Permission.
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magedelight_Shippingmatrix::delete');
    }
    
    public function execute()
    {
         // check if we know what should be deleted
        $id = $this->getRequest()->getParam('shipping_method_id');
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($id) {
            try {
                /* Cannot delete shipping method if used by any rates. */
                if ($this->checkIfShippingMethodUsed($id)) {
                    $this->messageManager->addError(__('You cannot delete this shipping method as it is used in one of the shipping rate(s).'));
                } else {
                    $shipping_method = $this->shippingMethodFactory->create()->load($id);
                    $shipping_method->delete();
                    $this->messageManager->addSuccess(
                        __('The record has been deleted.')
                    );
                }
                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
                return $resultRedirect->setPath('*/*/');
            }
        }
        /* display error message */
        $this->messageManager->addError(__('We can\'t find a record to delete.'));
        /* go to grid */
        return $resultRedirect->setPath('*/*/');
    }
    
    protected function checkIfShippingMethodUsed($shipping_method_id)
    {
        $collection = $this->shippingmatrixFactory->create()->getCollection()->addFieldToFilter('shipping_method', $shipping_method_id)->addFieldToSelect('pk')->getFirstItem();
        if ($collection->getPk()) {
            return  true;
        }
        return  false;
    }
}
