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
namespace Magedelight\Abandonedcart\Controller\Index;

class SendQueue extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magedelight\Abandonedcart\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magedelight\Abandonedcart\Model\EmailQueueFactory
     */
    protected $_emailQueueFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magedelight\Abandonedcart\Model\EmailQueueFactory $emailqueueFactory
     * @param \Magedelight\Abandonedcart\Helper\Data $helper
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magedelight\Abandonedcart\Helper\Data $helper,
        \Magedelight\Abandonedcart\Model\EmailQueueFactory $emailQueueFactory
    ) {
        $this->helper               = $helper;
        $this->_emailQueueFactory   = $emailQueueFactory;
    
        parent::__construct($context);
    }

    /**
     * Send Abandoned cart emails queue
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        if ($this->helper->isAbandonedcartEnabled()) {
            $date = date('Y-m-d H:i:s');
            $queueCollection = $this->_emailQueueFactory->create()
                ->getCollection()
                ->addFieldToFilter('is_sent', 0);

            foreach ($queueCollection as $queue) {
                if ($date > $queue->getScheduleAt()) {
                    if (!$this->helper->prepareAndSendAbandonedcartMail($queue)) {
                        continue;
                    }
                }
            }
        }
    }
}
