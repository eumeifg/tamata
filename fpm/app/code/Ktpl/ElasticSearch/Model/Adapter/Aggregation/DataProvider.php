<?php
/**
 * KrishTechnolabs
 *
 * PHP version 7
 *
 * @category  KrishTechnolabs
 * @package   Ktpl_ElasticSearch
 * @author    Dhara Bhatti <dhara.bhatti@krishtechnolabs.com>
 * @copyright 2019 (c) KrishTechnolabs (https://www.KrishTechnolabs.com/)
 * @license   https://www.krishtechnolabs.com/LICENSE.txt Krish License
 * @link      https://www.krishtechnolabs.com/
 */

namespace Ktpl\ElasticSearch\Model\Adapter\Aggregation;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\DB\Select;
use Magento\Framework\Search\Adapter\Mysql\Aggregation\DataProviderInterface;
use Magento\Framework\Search\Request\BucketInterface;

/**
 * Aggregations data provider for native mysql engine (not products indexes not have aggregations)
 *
 * @package Ktpl\ElasticSearch\Model\Adapter\Aggregation
 */
class DataProvider implements DataProviderInterface
{
    /**
     * Get data set
     *
     * @param BucketInterface $bucket
     * @param array $dimensions
     * @param Table $entityIdsTable
     * @return Select|void
     */
    public function getDataSet(
        BucketInterface $bucket,
        array $dimensions,
        Table $entityIdsTable
    )
    {
    }

    /**
     * {@inheritdoc}
     *
     * @param Select $select
     * @return array|void
     */
    public function execute(Select $select)
    {
    }
}
