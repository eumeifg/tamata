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
 
class Cancel extends EmailQueue
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
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magedelight\Abandonedcart\Model\EmailQueueFactory $emailqueueFactory
     */

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magedelight\Abandonedcart\Model\EmailQueueFactory $emailqueueFactory,
        \Magedelight\Abandonedcart\Model\HistoryFactory $HistoryFactory
    ) {
        $this->emailqueueFactory = $emailqueueFactory;
        $this->historyFactory = $HistoryFactory;
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
                $queueObj = $this->emailqueueFactory->create();
                $queueObj->load($id);
                $historyObj = $this->historyFactory->create();
                $historyObj->setData(
                    [
                            'first_name'    => $queueObj->getFirstName(),
                            'last_name'     => $queueObj->getLastName(),
                            'email'         => $queueObj->getEmail(),
                            'customer_id'   => $queueObj->getCustomerId(),
                            'email_content' => $queueObj->getEmailContent(),
                            'template_id'   => $queueObj->getTemplateId(),
                            'template_code' => $queueObj->getTemplateCode(),
                            'variables'     => $queueObj->getVariables(),
                            'reference_id'  => $queueObj->getReferenceId(),
                            'schedule_id'   => $queueObj->getScheduleId(),
                            'quote_id'      => $queueObj->getQuoteId(),
                            'schedule_at'   => $queueObj->getScheduleAt(),
                            'is_sent'       => \Magedelight\Abandonedcart\Model\Config\Source\IsSentStatus::NO,
                            'status'        => \Magedelight\Abandonedcart\Model\Config\Source\EmailStatus::CANCELED,
                        ]
                );

                $historyObj->save();
                $queueObj->delete();
                $this->messageManager->addSuccess(__('Email Queue item was cancelled 
                and saved to history successfully.'));
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
