<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-helpdesk
 * @version   1.1.96
 * @copyright Copyright (C) 2019 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\Helpdesk\Controller\Adminhtml\Ticket;

use Magento\Framework\Controller\ResultFactory;
use Mirasvit\Helpdesk\Model\Config as Config;

class Archive extends \Mirasvit\Helpdesk\Controller\Adminhtml\Ticket
{
    /**
     * Move ticket to Archive
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Redirect $resultPage */
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        if ($this->getRequest()->getParam('id') > 0) {
            try {
                $ticket = $this->ticketFactory->create()->load($this->getRequest()->getParam('id'));
                $stateBefore = $ticket->getState();
                $ticket
                    ->setFolder(Config::FOLDER_ARCHIVE)
                    ->save();

                $this->helpdeskHistory->changeTicket(
                    $ticket,
                    $stateBefore,
                    $ticket->getState(),
                    Config::USER,
                    ['user' => $this->context->getAuth()->getUser()]
                );

                $this->messageManager->addSuccess(
                    __('Ticket was moved to archive')
                );
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
                $resultPage->setPath('*/*/edit', ['id' => $this->getRequest()
                    ->getParam('id'), ]);
                return $resultPage;
            }
        }
        $resultPage->setPath('*/*/');
        return $resultPage;
    }
}
