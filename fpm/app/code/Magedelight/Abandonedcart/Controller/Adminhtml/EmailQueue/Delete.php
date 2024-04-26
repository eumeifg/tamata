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
 
class Delete extends EmailQueue
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
     * @param \Magedelight\Abandonedcart\Model\HistoryFactory $historyFactory
     */

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magedelight\Abandonedcart\Model\EmailQueueFactory $emailqueueFactory,
        \Magedelight\Abandonedcart\Model\HistoryFactory $historyFactory
    ) {
        $this->emailqueueFactory = $emailqueueFactory;
        $this->historyFactory = $historyFactory;
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
                $model = $this->emailqueueFactory->create();
                $model->load($id);
                $historyObj = $this->historyFactory->create()->getCollection()
                    ->addFieldToFilter('template_id', $model->gettemplate_id())
                    ->addFieldToFilter('quote_id', $model->getquote_id());

                foreach ($historyObj as $item) {
                    $item->setstatus(11);
                }
                $historyObj->save();
                $model->delete();
                $this->messageManager->addSuccess(__('Email Queue item was deleted successfully.'));
                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
                return $resultRedirect->setPath('*/*/edit', ['id' => $id]);
            }
        }
        $this->messageManager->addError(__('We can\'t find EmailQueue to delete.'));
        return $resultRedirect->setPath('*/*/');
    }
}
