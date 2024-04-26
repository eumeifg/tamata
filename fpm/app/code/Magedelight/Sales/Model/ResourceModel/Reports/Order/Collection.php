<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Sales
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Sales\Model\ResourceModel\Reports\Order;

/**
 * Reports orders collection
 *
 * @author Rocket Bazaar Core Team
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Collection extends \Magento\Reports\Model\ResourceModel\Order\Collection
{

    /**
     * @var \Magedelight\Backend\Model\Auth\Session
     */
    protected $authSession;

    protected $_qtySoldExpression;

    protected $_vendorQtySoldExpression;

    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactory $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\Model\ResourceModel\Db\VersionControl\Snapshot $entitySnapshot,
        \Magento\Framework\DB\Helper $coreResourceHelper,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Sales\Model\Order\Config $orderConfig,
        \Magento\Sales\Model\ResourceModel\Report\OrderFactory $reportOrderFactory,
        \Magedelight\Backend\Model\Auth\Session $authSession,
        \Magento\Authorization\Model\UserContextInterface $userContext,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
    ) {
        $this->authSession = $authSession;
        $this->userContext = $userContext;
        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $entitySnapshot,
            $coreResourceHelper,
            $scopeConfig,
            $storeManager,
            $localeDate,
            $orderConfig,
            $reportOrderFactory,
            $connection,
            $resource
        );
    }

    /**
     * Get range expression
     *
     * @param string $range
     * @return \Zend_Db_Expr
     */
    protected function _getRangeExpression($range)
    {
        switch ($range) {
            case '24h':
                $expression = $this->getConnection()->getConcatSql(
                    [
                        $this->getConnection()->getDateFormatSql('{{attribute}}', '%Y-%m-%d %H:'),
                        $this->getConnection()->quote('00'),
                    ]
                );
                break;
            case '7d':
            case '1m':
                $expression = $this->getConnection()->getDateFormatSql('{{attribute}}', '%Y-%m-%d');
                break;
            case '1y':
            case '2y':
            case 'custom':
            default:
                $expression = $this->getConnection()->getDateFormatSql('{{attribute}}', '%Y-%m');
                break;
        }

        return $expression;
    }

    /**
     * Calculate From and To dates (or times) by given period
     *
     * @param string $range
     * @param string $customStart
     * @param string $customEnd
     * @param bool $returnObjects
     * @return array
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @throws \Exception
     */
    public function getDateRange($range, $customStart, $customEnd, $returnObjects = false)
    {
        $dateEnd = new \DateTime();
        $dateStart = new \DateTime();

        // go to the end of a day
        $dateEnd->setTime(23, 59, 59);

        $dateStart->setTime(0, 0, 0);

        switch ($range) {
            case '24h':
                $dateEnd = new \DateTime();
                $dateEnd->modify('+1 hour');
                $dateStart = clone $dateEnd;
                $dateStart->modify('-1 day');
                break;

            case '7d':
                // substract 6 days we need to include
                // only today and not hte last one from range
                $dateStart->modify('-6 days');
                break;

            case '1m':
                $dateStart->setDate(
                    $dateStart->format('Y'),
                    $dateStart->format('m'),
                    $this->_scopeConfig->getValue(
                        'reports/dashboard/mtd_start',
                        \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                    )
                );
                break;

            case 'custom':
                $dateStart = $customStart ? $customStart : $dateEnd;
                $dateEnd = $customEnd ? $customEnd : $dateEnd;
                break;

            case '1y':
            case '2y':
                $startMonthDay = explode(
                    ',',
                    $this->_scopeConfig->getValue(
                        'reports/dashboard/ytd_start',
                        \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                    )
                );
                $startMonth = isset($startMonthDay[0]) ? (int)$startMonthDay[0] : 1;
                $startDay = isset($startMonthDay[1]) ? (int)$startMonthDay[1] : 1;
                $dateStart->setDate($dateStart->format('Y'), $startMonth, $startDay);
                if ($range == '2y') {
                    $dateStart->modify('-1 year');
                }
                break;
        }

        if ($returnObjects) {
            return [$dateStart, $dateEnd];
        } else {
            return ['from' => $dateStart, 'to' => $dateEnd, 'datetime' => true];
        }
    }

    /**
     * Add revenue
     *
     * @param bool $convertCurrency
     * @return $this
     */
    public function addRevenueToSelect($convertCurrency = false)
    {
        $expr = $this->getTotalsExpression(
            !$convertCurrency,
            $this->getConnection()->getIfNullSql('rvo.base_subtotal_refunded', 0),
            $this->getConnection()->getIfNullSql('rvo.base_subtotal_canceled', 0),
            $this->getConnection()->getIfNullSql('rvo.base_discount_canceled', 0)
        );
        $this->getSelect()->columns(['revenue' => $expr]);

        return $this;
    }

    /**
     * Get SQL expression for totals
     *
     * @param int $storeId
     * @param string $baseSubtotalRefunded
     * @param string $baseSubtotalCanceled
     * @param string $baseDiscountCanceled
     * @return string
     */
    protected function getTotalsExpression(
        $storeId,
        $baseSubtotalRefunded,
        $baseSubtotalCanceled,
        $baseDiscountCanceled
    ) {
        $template = ($storeId != 0)
            ? '(rvo.base_subtotal - %2$s - %1$s - ABS(rvo.base_discount_amount) - %3$s)'
            : '((rvo.base_subtotal - %1$s - %2$s - ABS(rvo.base_discount_amount) - %3$s) '
                . ' * main_table.base_to_global_rate)';
        return sprintf($template, $baseSubtotalRefunded, $baseSubtotalCanceled, $baseDiscountCanceled);
    }

    /**
     * Add period filter by created_at attribute
     *
     * @param string $period
     * @return $this
     */
    public function addCreateAtPeriodFilter($period)
    {
        list($from, $to) = $this->getDateRange($period, 0, 0, true);

        $this->checkIsLive($period);

        if ($this->isLive()) {
            $fieldToFilter = 'created_at';
        } else {
            $fieldToFilter = 'period';
        }

        $this->addFieldToFilter(
            $fieldToFilter,
            [
                'from' => $from->format(\Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT),
                'to' => $to->format(\Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT)
            ]
        );

        return $this;
    }

    /**
     * Add item count expression
     *
     * @return $this
     */
    public function getVendorItemCountExpr()
    {
        $this->getSelect()->join(
            ["item_table" => "sales_order_item"],
            "rvo.order_id = item_table.order_id",
            ["qty_ordered","qty_canceled","qty_refunded"]
        );
        $expr = $this->_getItemQtyExpression();
        $this->getSelect()->columns(['items_count' => "SUM({$expr})"], 'item_table');
        return $this;
    }

    /**
     * Calculate lifitime sales
     *
     * @param int $isFilter
     * @return $this
     */
    public function calculateSales($vendorId = 0, $isFilter = 0)
    {
        if ($vendorId == 0) {
            /** checked to make sure vendor id is not null when accessed from API */
            if ($this->authSession->getUser() == null) {
                $vendorId = $this->userContext->getUserId();
            } else {
                $vendorId = $this->authSession->getUser()->getVendorId();
            }
        }
        $statuses = $this->_orderConfig->getStateStatuses([\Magento\Sales\Model\Order::STATE_NEW]);

        if (empty($statuses)) {
            $statuses = [0];
        }
        $connection = $this->getConnection();

        $this->setMainTable('md_vendor_order');
        $this->removeAllFieldsFromSelect();

        $expr = $this->_getSalesAmountExpression();

        /*if ($isFilter == 0) {
            $expr = '(' . $expr . ') * main_table.base_to_global_rate';
        }*/

        $this->getSelect()->joinLeft(
            ["main_order" => "sales_order"],
            "main_table.order_id = main_order.entity_id",
            ["main_order.state"]
        );

        $this->getSelect()->columns(
            ['lifetime' => "SUM({$expr})", 'average' => "AVG({$expr})", 'orders_count' => "COUNT(*)"]
        )->where(
            'main_table.status NOT IN(?)',
            $statuses
        )->where(
            'main_table.vendor_id = ?',
            $vendorId
        )/*->where(
            'main_order.state NOT IN(?)',
            [\Magento\Sales\Model\Order::STATE_NEW, \Magento\Sales\Model\Order::STATE_PENDING_PAYMENT]
        )*/;
        return $this;
    }

    /**
     * Get sales amount expression
     *
     * @return string
     */
    protected function _getItemQtySoldExpression()
    {
        if (null === $this->_qtySoldExpression) {
            $connection = $this->getConnection();
            $expressionTransferObject = new \Magento\Framework\DataObject(
                [
                    'expression' => '%s - %s - %s',
                    'arguments' => [
                        $connection->getIfNullSql('item_table.qty_ordered', 0),
                        $connection->getIfNullSql('item_table.qty_canceled', 0),
                        $connection->getIfNullSql('item_table.qty_refunded', 0)
                    ],
                ]
            );

            $this->_eventManager->dispatch(
                'vendor_products_prepare_qty_sold_expression',
                ['collection' => $this, 'expression_object' => $expressionTransferObject]
            );
            $this->_qtySoldExpression = vsprintf(
                $expressionTransferObject->getExpression(),
                $expressionTransferObject->getArguments()
            );
        }

        return $this->_qtySoldExpression;
    }

    public function calculateProductsSold()
    {
        $connection = $this->getConnection();

        /** checked to make sure vendor id is not null when accessed from API */
        if ($this->authSession->getUser() == null) {
            $vendorId = $this->userContext->getUserId();
        } else {
            $vendorId = $this->authSession->getUser()->getVendorId();
        }

        /*$this->setMainTable('sales_order_item');*/
        $this->removeAllFieldsFromSelect();
        $this->getSelect()->joinLeft(
            ["item_table" => "sales_order_item"],
            "main_table.entity_id = item_table.order_id",
            ["vendor_id"]
        );

        $expr = $this->_getItemQtySoldExpression();

        $this->getSelect()->columns(
            ['product_sold' => "SUM({$expr})"]
        )->where(
            'main_table.is_confirmed = ?',
            1
        )->where(
            'item_table.vendor_id = ?',
            $vendorId
        );
        return $this;
    }

    /**
     * Prepare report summary
     *
     * @param string $range
     * @param mixed $customStart
     * @param mixed $customEnd
     * @param int $isFilter
     * @return $this
     */
    public function prepareSummary($range, $customStart, $customEnd, $isFilter = 0)
    {
        $this->checkIsLive($range);

        if ($this->_isLive) {
            $this->_prepareSummaryLive($range, $customStart, $customEnd, $isFilter);
        } else {
            $this->_prepareSummaryAggregated($range, $customStart, $customEnd, $isFilter);
        }

        return $this;
    }

    /**
     * Check range for live mode
     *
     * @param string $range
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function checkIsLive($range)
    {
        $this->_isLive = (bool)(!$this->_scopeConfig->getValue(
            'sales/dashboard/use_aggregated_data',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        ));
        return $this;
    }

    /**
     * Prepare report summary from live data
     *
     * @param string $range
     * @param mixed $customStart
     * @param mixed $customEnd
     * @param int $isFilter
     * @return $this
     */
    protected function _prepareSummaryLive($range, $customStart, $customEnd, $isFilter = 0)
    {
        $this->setMainTable('md_vendor_order');
        $connection = $this->getConnection();

        /**
         * Reset all columns, because result will group only by 'created_at' field
         */
        $this->getSelect()->reset(\Magento\Framework\DB\Select::COLUMNS);

        $expression = $this->_getSalesAmountExpression();
        if ($isFilter == 0) {
            /* $this->getSelect()->columns(
                 [
                     'revenue' => new \Zend_Db_Expr(
                         sprintf(
                             'SUM((%s) * %s)',
                             $expression,
                             $connection->getIfNullSql('main_table.base_to_global_rate', 0)
                         )
                     ),
                 ]
             );*/
            $this->getSelect()->columns(['revenue' => new \Zend_Db_Expr(sprintf('SUM(%s)', $expression))]);
        } else {
            $this->getSelect()->columns(['revenue' => new \Zend_Db_Expr(sprintf('SUM(%s)', $expression))]);
        }

        $dateRange = $this->getDateRange($range, $customStart, $customEnd);

        $tzRangeOffsetExpression = $this->_getTZRangeOffsetExpression(
            $range,
            'main_table.created_at',
            $dateRange['from'],
            $dateRange['to']
        );

        $this->getSelect()->joinLeft(
            ["main_order" => "sales_order"],
            "main_table.order_id = main_order.entity_id",
            ["main_order.state"]
        );

        $this->getSelect()->columns(
            ['quantity' => 'COUNT(main_table.order_id)', 'range' => $tzRangeOffsetExpression]
        )->where(
            'main_order.state NOT IN (?)',
            [\Magento\Sales\Model\Order::STATE_PENDING_PAYMENT, \Magento\Sales\Model\Order::STATE_NEW]
        )->order(
            'range',
            \Magento\Framework\DB\Select::SQL_ASC
        )->group(
            $tzRangeOffsetExpression
        );

        $this->addFieldToFilter('main_table.created_at', $dateRange);
        $this->addFieldToFilter('main_table.vendor_id', $this->authSession->getVendorId());
        $this->addFieldToFilter('main_table.status', ['nin' => [\Magedelight\Sales\Model\Order::STATUS_CANCELED]]);
        $this->addFieldToFilter('main_order.is_confirmed', ['eq' => 1]);

        return $this;
    }

    /**
     * Prepare report summary from aggregated data
     *
     * @param string $range
     * @param string|null $customStart
     * @param string|null $customEnd
     * @return $this
     * @throws \Exception
     */
    protected function _prepareSummaryAggregated($range, $customStart, $customEnd)
    {
        $this->setMainTable('sales_order_aggregated_created');
        /**
         * Reset all columns, because result will group only by 'created_at' field
         */
        $this->getSelect()->reset(\Magento\Framework\DB\Select::COLUMNS);
        $rangePeriod = $this->_getRangeExpressionForAttribute($range, 'main_table.period');

        $tableName = $this->getConnection()->quoteIdentifier('main_table.period');
        $rangePeriodAggregateStmt = str_replace($tableName, "MIN({$tableName})", $rangePeriod);

        $this->getSelect()->columns(
            [
                'revenue' => 'SUM(main_table.total_revenue_amount)',
                'quantity' => 'SUM(main_table.orders_count)',
                'range' => $rangePeriodAggregateStmt,
            ]
        )->order(
            'range'
        )->group(
            $rangePeriod
        );

        $this->getSelect()->where(
            $this->_getConditionSql('main_table.period', $this->getDateRange($range, $customStart, $customEnd))
        );

        $statuses = $this->_orderConfig->getStateStatuses(\Magento\Sales\Model\Order::STATE_CANCELED);

        if (empty($statuses)) {
            $statuses = [0];
        }
        $this->addFieldToFilter('main_table.order_status', ['nin' => $statuses]);

        return $this;
    }

    /**
     * Retrieve range expression adapted for attribute
     *
     * @param string $range
     * @param string $attribute
     * @return string
     */
    protected function _getRangeExpressionForAttribute($range, $attribute)
    {
        $expression = $this->_getRangeExpression($range);
        return str_replace('{{attribute}}', $this->getConnection()->quoteIdentifier($attribute), $expression);
    }

    /**
     * Retrieve query for attribute with timezone conversion
     *
     * @param string $range
     * @param string $attribute
     * @param string|null $from
     * @param string|null $to
     * @return string
     */
    protected function _getTZRangeOffsetExpression($range, $attribute, $from = null, $to = null)
    {
        return str_replace(
            '{{attribute}}',
            $this->_reportOrderFactory->create()->getStoreTZOffsetQuery($this->getMainTable(), $attribute, $from, $to),
            $this->_getRangeExpression($range)
        );
    }

    /**
     * Calculate totals live report
     *
     * @param int $isFilter
     * @return $this
     */
    protected function _calculateTotalsLive($isFilter = 0)
    {
        $this->setMainTable('sales_order');
        $this->removeAllFieldsFromSelect();

        $connection = $this->getConnection();

        $baseTaxInvoiced = $connection->getIfNullSql('rvo.base_tax_invoiced', 0);
        $baseTaxRefunded = $connection->getIfNullSql('rvo.base_tax_refunded', 0);
        $baseShippingInvoiced = $connection->getIfNullSql('rvo.base_shipping_invoiced', 0);
        $baseShippingRefunded = $connection->getIfNullSql('rvo.base_shipping_refunded', 0);

        $revenueExp = $this->_getSalesAmountExpression1();
        $taxExp = sprintf('%s - %s', $baseTaxInvoiced, $baseTaxRefunded);
        $shippingExp = sprintf('%s - %s', $baseShippingInvoiced, $baseShippingRefunded);

        if ($isFilter == 0) {
            $rateExp = $connection->getIfNullSql('main_table.base_to_global_rate', 0);
            $this->getSelect()->columns(
                [
                    'revenue' => new \Zend_Db_Expr(sprintf('SUM((%s) * %s)', $revenueExp, $rateExp)),
                    'tax' => new \Zend_Db_Expr(sprintf('SUM((%s) * %s)', $taxExp, $rateExp)),
                    'shipping' => new \Zend_Db_Expr(sprintf('SUM((%s) * %s)', $shippingExp, $rateExp)),
                ]
            );
        } else {
            $this->getSelect()->columns(
                [
                    'revenue' => new \Zend_Db_Expr(sprintf('SUM(%s)', $revenueExp)),
                    'tax' => new \Zend_Db_Expr(sprintf('SUM(%s)', $taxExp)),
                    'shipping' => new \Zend_Db_Expr(sprintf('SUM(%s)', $shippingExp)),
                ]
            );
        }

        $this->getSelect()->columns(
            ['quantity' => 'COUNT(rvo.order_id)']
        )->where(
            'main_table.state NOT IN (?)',
            [\Magento\Sales\Model\Order::STATE_PENDING_PAYMENT, \Magento\Sales\Model\Order::STATE_NEW]
        );

        return $this;
    }

    /**
     * Get sales amount expression
     *
     * @return string
     */
    protected function _getSalesAmountExpression1()
    {
        if (null === $this->_salesAmountExpression) {
            $connection = $this->getConnection();

            $expressionTransferObject = new \Magento\Framework\DataObject(
                [
                    'expression' => '%s - %s - %s - (%s - %s - %s)',
                    'arguments' => [
                        $connection->getIfNullSql('rvo.base_total_invoiced', 0),
                        $connection->getIfNullSql('rvo.base_tax_invoiced', 0),
                        $connection->getIfNullSql('rvo.base_shipping_invoiced', 0),
                        $connection->getIfNullSql('rvo.base_total_refunded', 0),
                        $connection->getIfNullSql('rvo.base_tax_refunded', 0),
                        $connection->getIfNullSql('rvo.base_shipping_refunded', 0),
                    ],
                ]
            );

            $this->_eventManager->dispatch(
                'sales_prepare_amount_expression',
                ['collection' => $this, 'expression_object' => $expressionTransferObject]
            );
            $this->_salesAmountExpression = vsprintf(
                $expressionTransferObject->getExpression(),
                $expressionTransferObject->getArguments()
            );
        }

        return $this->_salesAmountExpression;
    }
}
