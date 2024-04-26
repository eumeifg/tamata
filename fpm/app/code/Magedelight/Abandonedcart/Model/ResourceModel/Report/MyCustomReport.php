<?php

namespace Magedelight\Abandonedcart\Model\ResourceModel\Report;

class MyCustomReport extends \Magento\Sales\Model\ResourceModel\Report\AbstractReport
{
    const AGGREGATION_DAILY = 'md_abandonedcart_report_daily';
    const AGGREGATION_MONTHLY = 'md_abandonedcart_report_monthly';
    const AGGREGATION_YEARLY = 'md_abandonedcart_report_yearly';

    protected $resource;
    protected $timezone;

    /**
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Magento\Reports\Model\FlagFactory $reportsFlagFactory
     * @param \Magento\Framework\Stdlib\DateTime\Timezone\Validator $timezoneValidator
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $dateTime
     * @param array $ignoredProductTypes
     * @param string $connectionName
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Reports\Model\FlagFactory $reportsFlagFactory,
        \Magento\Framework\Stdlib\DateTime\Timezone\Validator $timezoneValidator,
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone,
        \Magedelight\Abandonedcart\Model\ResourceModel\Report\CollectionFactory $reportsCollectionFactory,
        $connectionName = null
    ) {
        parent::__construct(
            $context,
            $logger,
            $localeDate,
            $reportsFlagFactory,
            $timezoneValidator,
            $dateTime,
            $connectionName
        );

        $this->resource = $resource;
        $this->timezone = $timezone;
        $this->reportsCollectionFactory = $reportsCollectionFactory;
    }

    /**
     * Model initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(self::AGGREGATION_DAILY, 'id');
    }

    /**
     * Aggregate Orders data by order created at
     *
     * @param string|int|\DateTime|array|null $from
     * @param string|int|\DateTime|array|null $to
     * @return $this
     * @throws \Exception
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function aggregate($from = null, $to = null)
    {
        $mainTable = $this->getMainTable();
        $connection = $this->resource->getConnection('sales');
        try {
            $this->truncateTable();
            $collection =  $this->reportsCollectionFactory->create();
            $collection->getSelect()->group(['quote_id','product_id'])->order('report_id DESC');
            $insertBatches = [];
            if ($collection) {
                foreach ($collection as $info) {
                    $insertBatches[] = [
                        'period'             => $info['created_at'],
                        'store_id'           => $info['store_id'],
                        'reference_id'       => $info['reference_id'],
                        'quote_id'           => $info['quote_id'],
                        'customer_id'        => $info['customer_id'],
                        'first_name'         => $info['first_name'],
                        'last_name'          => $info['last_name'],
                        'email'              => $info['email'],
                        'product_id'         => $info['product_id'],
                        'product_sku'        => $info['product_sku'],
                        'product_name'       => $info['product_name'],
                        'qty_ordered'        => $info['product_qty']
                    ];
                }
            }

            $tableName = $this->resource->getTableName(self::AGGREGATION_DAILY);
            foreach (array_chunk($insertBatches, 100) as $batch) {
                $connection->insertMultiple($tableName, $batch);
            }
            $this->updateReportMonthlyYearly(
                $connection,
                'month',
                'qty_ordered',
                $mainTable,
                $this->getTable(self::AGGREGATION_MONTHLY)
            );
            $this->updateReportMonthlyYearly(
                $connection,
                'year',
                'qty_ordered',
                $mainTable,
                $this->getTable(self::AGGREGATION_YEARLY)
            );
            
            $this->_setFlagData(\Magedelight\Abandonedcart\Model\Flag::REPORT_MYCUSTOMREPORT_FLAG_CODE);
        } catch (\Exception $e) {
            throw $e;
        }

        return $this;
    }

    public function truncateTable()
    {
        $tables = [
            $this->resource->getTableName(self::AGGREGATION_DAILY),
            $this->resource->getTableName(self::AGGREGATION_MONTHLY),
            $this->resource->getTableName(self::AGGREGATION_YEARLY),
        ];
        $connection = $this->resource->getConnection();

        foreach ($tables as $table) {
            $connection->truncateTable($table);
        }
    }

    public function updateReportMonthlyYearly($connection, $type, $column, $mainTable, $aggregationTable)
    {
        $periodSubSelect = $connection->select();
        $ratingSubSelect = $connection->select();
        $ratingSelect = $connection->select();

        switch ($type) {
            case 'year':
                $periodCol = $connection->getDateFormatSql('t.period', '%Y-01-01');
                break;
            case 'month':
                $periodCol = $connection->getDateFormatSql('t.period', '%Y-%m-01');
                break;
            default:
                $periodCol = 't.period';
                break;
        }

        $columns = [
            'period' => 't.period',
            'store_id' => 't.store_id',
            'reference_id' => 't.reference_id',
            'quote_id' => 't.quote_id',
            'customer_id' => 't.customer_id',
            'first_name' => 't.first_name',
            'last_name' => 't.last_name',
            'email' => 't.email',
            'product_id' => 't.product_id',
            'product_sku' => 't.product_sku',
            'product_name' => 't.product_name',
        ];

        if ($type == 'day') {
            $columns['id'] = 't.id';  // to speed-up insert on duplicate key update
        }

        $cols = array_keys($columns);
        $cols['total_qty'] = new \Zend_Db_Expr('SUM(t.' . $column . ')');
        $periodSubSelect->from(
            ['t' => $mainTable],
            $cols
        )->group(
            ['t.store_id', $periodCol, 't.product_id' , 't.quote_id']
        )->order(
            ['t.store_id', $periodCol, 'total_qty DESC']
        );

        $cols = $columns;
        $cols[$column] = 't.total_qty';
        //$cols['first_name'] = 't.first_name';

        $cols['prevStoreId'] = new \Zend_Db_Expr('(@prevStoreId := t.`store_id`)');
        $cols['prevPeriod'] = new \Zend_Db_Expr("(@prevPeriod := {$periodCol})");
        $ratingSubSelect->from($periodSubSelect, $cols);

        $cols = $columns;
        $cols['period'] = $periodCol;
        $cols[$column] = 't.' . $column;
        
        $ratingSelect->from($ratingSubSelect, $cols);

        $sql = $ratingSelect->insertFromSelect($aggregationTable, array_keys($cols));
        $connection->query("SET @pos = 0, @prevStoreId = -1, @prevPeriod = '0000-00-00'");
        $connection->query($sql);
        return $this;
    }
}
