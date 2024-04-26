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

class ClearHistory
{
    /**
     * @var \Magedelight\Abandonedcart\Helper\Data
     */
    protected $helper;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var \Magedelight\Abandonedcart\Model\HistoryFactory
     */
    protected $historyFactory;
    
    /**
     * @param \Magedelight\Abandonedcart\Helper\Data $helper
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magedelight\Abandonedcart\Model\HistoryFactory $historyFactory
     */
    public function __construct(
        \Magedelight\Abandonedcart\Helper\Data $helper,
        \Psr\Log\LoggerInterface $logger,
        \Magedelight\Abandonedcart\Model\HistoryFactory $historyFactory
    ) {
        $this->helper = $helper;
        $this->logger = $logger;
        $this->historyFactory = $historyFactory;
    }

    /**
     * Cron Method Execution
     */
    public function execute()
    {
        if ($this->helper->isAbandonedcartEnabled() && $this->helper->getIfRemoveHistory()) {
            $historyCollection = $this->historyFactory->create()->getCollection();
            $removeHistoryAfter = $this->helper->getKeepHistoryTime();
            
            $historyCollection->getSelect()->where("timestampdiff(DAY,creation_time,NOW()) >= ".$removeHistoryAfter);
            
            foreach ($historyCollection as $history) {
                $this->historyFactory->create()->load($history->getHistoryId())->delete();
            }

            $now = date('Y-m-d H:i:s');
            $this->logger->info(__('Abandoned Cart history removed at '.$now.' as per configuration settings.'));
        }
    }
}
