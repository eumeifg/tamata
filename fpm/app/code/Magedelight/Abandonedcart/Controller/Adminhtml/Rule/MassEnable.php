<?php
/**
 * Magedelight
 * Copyright (C) 2018 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Abandonedcart
 * @copyright Copyright (c) 2018 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Abandonedcart\Controller\Adminhtml\Rule;

use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\App\Action\Context;

class MassEnable extends \Magento\Backend\App\Action
{
    /**
     * @var Filter
     */
    public $filter;
 
    /**
     * @var \Magedelight\Abandonedcart\Model\ResourceModel\Rule\CollectionFactory
     */
    public $collectionFactory;
    
    /**
     * @param Context $context
     * @param \Magento\Ui\Component\MassAction\Filter $filter
     * @param \Magedelight\Abandonedcart\Model\ResourceModel\Rule\CollectionFactory $collectionFactory
     */
    public function __construct(
        Context $context,
        \Magento\Ui\Component\MassAction\Filter $filter,
        \Magedelight\Abandonedcart\Model\ResourceModel\Rule\CollectionFactory $collectionFactory
    ) {
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        parent::__construct($context);
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
            $item->setStatus(\Magedelight\Abandonedcart\Model\Rule::STATUS_ENABLED);
            $this->dataSave($item);
        }

        $this->messageManager->addSuccess(__('A total of %1 record(s) have been enabled.', $collectionSize));

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('*/*/');
    }
    
    private function dataSave($item)
    {
        $item->save();
    }
}
