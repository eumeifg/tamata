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
namespace Magedelight\Shippingmatrix\Controller\Sellerhtml\Rates;

use Magento\Backend\App\Action;
use Magento\TestFramework\ErrorLog\Logger;

/**
 * Description of Delete
 *
 * @author Rocket Bazaar Core Team
 */
class Delete extends \Magedelight\Backend\App\Action
{

    /**
     * @var \Magedelight\Shippingmatrix\Model\Shippingmatrix
     */
    protected $shippingMetrix;

    /**
     * @param \Magedelight\Backend\App\Action\Context $context
     * @param \Magedelight\Shippingmatrix\Model\Shippingmatrix $shippingMetrix
     */
    public function __construct(
        \Magedelight\Backend\App\Action\Context $context,
        \Magedelight\Shippingmatrix\Model\Shippingmatrix $shippingMetrix
    ) {
        $this->shippingMetrix = $shippingMetrix;
        parent::__construct($context);
    }

    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($id) {
            try {
                $model = $this->shippingMetrix;
                $model->load($id);
                $model->delete();
                $this->messageManager->addSuccess(__('The Item has been deleted.'));
                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
                return $resultRedirect->setPath('*/*/');
            }
        }
        $this->messageManager->addError(__('We can\'t find a Item to delete.'));
        return $resultRedirect->setPath('*/*/');
    }
    
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magedelight_Vendor::shippingmethod');
    }
}
