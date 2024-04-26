<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_ProductAlert
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\ProductAlert\Controller\Adminhtml\Stock;

use Magento\Framework\Controller\ResultFactory;

class Delete extends \Magento\Backend\App\Action
{
    protected $stock;
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\ProductAlert\Model\Stock $stock
    ) {
        parent::__construct($context);
        $this->stock = $stock;
    }

    public function execute()
    {
        $alertId = (int)$this->getRequest()->getParam('alert_stock_id');

        if (!$alertId) {
            $this->messageManager->addError(
                __(
                    'An error occurred while deleting the item from Subscriptions.'
                )
            );
        } else {
            $alert = $this->stock->load($alertId);
            if ($alert && $alert->getId()) {
                try {
                    $alert->delete();
                    $this->messageManager->addSuccess(
                        __('The item has been deleted from Subscriptions.')
                    );
                } catch (\Exception $e) {
                    $this->messageManager->addError(
                        __(
                            'An error occurred while deleting the item from Subscriptions.'
                        )
                    );
                }
            }
        }
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setUrl($this->_redirect->getRefererUrl());
        return $resultRedirect;
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed(
            'Magedelight_ProductAlert::stock'
        );
    }
}
