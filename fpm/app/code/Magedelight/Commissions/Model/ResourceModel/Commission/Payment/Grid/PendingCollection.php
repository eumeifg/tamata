<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Commissions
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Commissions\Model\ResourceModel\Commission\Payment\Grid;

use Magento\Framework\Data\Collection\EntityFactoryInterface;
use Psr\Log\LoggerInterface;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult;
use Magedelight\Commissions\Model\Source\PaymentStatus as PaymentStatus;

class PendingCollection extends SearchResult
{
    public function __construct(
        EntityFactoryInterface $entityFactory,
        LoggerInterface $logger,
        FetchStrategyInterface $fetchStrategy,
        ManagerInterface $eventManager,
        $mainTable,
        $resourceModel
    ) {
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $mainTable, $resourceModel);
    }
    
    protected function _construct()
    {
        parent::_construct();
        $this->addFilterToMap('vendor_id', 'main_table.vendor_id');
        $this->addFilterToMap('status', 'main_table.status');
        $this->addFilterToMap('created_at', 'main_table.created_at');
    }

        /**
         * Init collection select
         *
         * @return $this
         */
    protected function _initSelect()
    {
        parent::_initSelect();
        
        $this->join(
            [$this->getTable('md_vendor_order')],
            'main_table.vendor_order_id = '.$this->getTable('md_vendor_order').'.vendor_order_id',
            ['grand_total','total_refunded','order_currency_code']
        );
        
        $this->addFieldToFilter('main_table.status', ['eq' => PaymentStatus::PAYMENT_STATUS_PENDING]);

        return $this;
    }
}
