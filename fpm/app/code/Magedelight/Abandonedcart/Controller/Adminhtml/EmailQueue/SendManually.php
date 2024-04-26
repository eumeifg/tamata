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

namespace Magedelight\Abandonedcart\Controller\Adminhtml\EmailQueue;

use Magedelight\Abandonedcart\Controller\Adminhtml\EmailQueue;
 
class SendManually extends EmailQueue
{
    
    /**
     * @var \Magedelight\Abandonedcart\Model\EmailQueueFactory
     */
    protected $emailqueueFactory;

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
     * @param \Magedelight\Abandonedcart\Model\EmailQueueFactory $emailqueueFactory
     * @param \Magedelight\Abandonedcart\Helper\Data $helper
     * @param \Magedelight\Abandonedcart\Model\HistoryFactory $HistoryFactory
     */

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magedelight\Abandonedcart\Helper\Data $helper,
        \Magedelight\Abandonedcart\Model\EmailQueueFactory $emailqueueFactory,
        \Magedelight\Abandonedcart\Model\HistoryFactory $HistoryFactory
    ) {
        $this->emailqueueFactory = $emailqueueFactory;
        $this->historyFactory = $HistoryFactory;
        $this->helper = $helper;
        parent::__construct($context, $coreRegistry);
    }

    /**
     * Send Abandoned cart email manually
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();

        if (!$this->helper->isAbandonedcartEnabled()) {
            $this->messageManager->addError(__('Please enable extension to send abandoned cart emails!'));
            return $resultRedirect->setPath('*/*/');
        }

        $id = $this->getRequest()->getParam('id');
        if ($id) {
            try {
                $queueObj = $this->emailqueueFactory->create();
                $queueObj->load($id);
                if ($this->helper->prepareAndSendAbandonedcartMail($queueObj) != false) {
                    $this->messageManager->addSuccess(__('Email is sent successfully!'));
                } else {
                    $this->messageManager->addError(__('Email not sent, check history for details!'));
                }
                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
                return $resultRedirect->setPath('*/*/');
            }
        }
        $this->messageManager->addError(__('We can\'t find EmailQueue to cancel.'));
        return $resultRedirect->setPath('*/*/');
    }
}
