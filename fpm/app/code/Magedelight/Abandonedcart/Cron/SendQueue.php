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
namespace Magedelight\Abandonedcart\Cron;

class SendQueue
{
    /**
     * @var \Magedelight\Abandonedcart\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magedelight\Abandonedcart\Helper\Data $helper
     * @var \Magedelight\Abandonedcart\Model\EmailQueueFactory
     */
    protected $_emailQueueFactory;

    public function __construct(
        \Magedelight\Abandonedcart\Helper\Data $helper,
        \Magedelight\Abandonedcart\Model\EmailQueueFactory $emailQueueFactory
    ) {
        $this->helper               = $helper;
        $this->_emailQueueFactory   = $emailQueueFactory;
    }

    /**
     * Send Generated Queue Cron Method
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
