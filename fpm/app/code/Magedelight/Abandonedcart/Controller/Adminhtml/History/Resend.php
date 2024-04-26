<?php
/**
 * Magedelight
 * Copyright (C) 2017 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Abandonedcart
 * @copyright Copyright (c) 2017 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */

namespace Magedelight\Abandonedcart\Controller\Adminhtml\History;

use Magedelight\Abandonedcart\Controller\Adminhtml\History;
 
class Resend extends History
{
    /**
     * @var \Magedelight\Abandonedcart\Model\HistoryFactory
     */
    protected $historyFactory;

    /**
     * @var \Magedelight\Abandonedcart\Helper\Data
     */
    protected $helper;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magedelight\Abandonedcart\Model\HistoryFactory $historyFactory
     */

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magedelight\Abandonedcart\Helper\Data $helper,
        \Magedelight\Abandonedcart\Model\HistoryFactory $historyFactory
    ) {
        $this->historyFactory = $historyFactory;
        $this->helper = $helper;
        parent::__construct($context, $coreRegistry);
    }

    /**
     * Delete Synonym
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $id = $this->getRequest()->getParam('id');
        if ($id) {
            try {
                $historyObj = $this->historyFactory->create();
                $historyObj->load($id);
                //echo "<pre>"; print_r($historyObj->getData()); die;
                if ($send = $this->helper->prepareAndSendAbandonedcartMailFromHistory($historyObj)) {
                    if ($send === \Magedelight\Abandonedcart\Model\Config\Source\EmailStatus::BLACKLISTED) {
                        $this->messageManager->addError(__('Email is blacklisted or customer is unsubscribed!'));
                    } else {
                        $this->messageManager->addSuccess(__('Email is sent successfully!'));
                    }
                } else {
                    $this->messageManager->addError(__('Something went wrong while mail sending!'));
                }
                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
                return $resultRedirect->setPath('*/*/');
            }
        }
        $this->messageManager->addError(__('We can\'t find item to resend.'));
        return $resultRedirect->setPath('*/*/');
    }
}
