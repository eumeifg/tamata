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

namespace Magedelight\Abandonedcart\Controller\Adminhtml\BlackList;

use Magedelight\Abandonedcart\Controller\Adminhtml\Blacklist;
use Magento\Framework\Controller\ResultFactory;

class MassDelete extends Blacklist
{
    /**
     * @var \Magento\Ui\Component\MassAction\Filter
     */
    protected $filter;

    /**
     * @var \Magedelight\Abandonedcart\Model\ResourceModel\Blacklist\CollectionFactory
     */
    protected $collectionFactory;

    /**
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Ui\Component\MassAction\Filter $filter,
        \Magedelight\Abandonedcart\Model\ResourceModel\Blacklist\CollectionFactory $collectionFactory
    ) {
    
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
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
            $item->delete();
        }

        $this->messageManager->addSuccess(__('A total of %1 record(s) have been deleted.', $collectionSize));

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('*/*/');
    }
}
