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
use Magento\Framework\Controller\ResultFactory;

class MassDelete extends EmailQueue
{
    /**
     * @var \Magento\Ui\Component\MassAction\Filter
     */
    protected $filter;

    /**
     * @var \Magedelight\Abandonedcart\Model\ResourceModel\EmailQueue\CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var \Magedelight\Abandonedcart\Model\HistoryFactory
     */
    protected $historyFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $context,
     * @param \Magento\Framework\Registry $coreRegistry,
     * @param \Magento\Ui\Component\MassAction\Filter $filter
     * @param \Magedelight\Abandonedcart\Model\ResourceModel\EmailQueue\CollectionFactory $collectionFactory
     * @param \Magedelight\Abandonedcart\Model\HistoryFactory $historyFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Ui\Component\MassAction\Filter $filter,
        \Magedelight\Abandonedcart\Model\ResourceModel\EmailQueue\CollectionFactory $collectionFactory,
        \Magedelight\Abandonedcart\Model\HistoryFactory $historyFactory
    ) {
    
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        $this->historyFactory = $historyFactory;
        parent::__construct($context, $coreRegistry);
    }

    /**
     * Execute action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     * @throws \Magento\Framework\Exception\LocalizedException|\Exception
     */
    public function execute()
    {
        $collection = $this->filter->getCollection($this->collectionFactory->create());
        $collectionSize = $collection->getSize();

        foreach ($collection as $item) {

            $historyObj = $this->historyFactory->create()->getCollection()
                ->addFieldToFilter('template_id', $item->gettemplate_id())
                ->addFieldToFilter('quote_id', $item->getquote_id());
            foreach ($historyObj as $rowItem) {
                $rowItem->setstatus(11);
            }
            $historyObj->save();
            $item->delete();
        }

        $this->messageManager->addSuccess(__('A total of %1 record(s) have been deleted.', $collectionSize));

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('*/*/');
    }
}
