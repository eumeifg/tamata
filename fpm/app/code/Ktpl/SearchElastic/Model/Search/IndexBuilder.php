<?php
/**
 * KrishTechnolabs
 *
 * PHP version 7
 *
 * @category  KrishTechnolabs
 * @package   Ktpl_SearchElastic
 * @author    Dhara Bhatti <dhara.bhatti@krishtechnolabs.com>
 * @copyright 2019 (c) KrishTechnolabs (https://www.KrishTechnolabs.com/)
 * @license   https://www.krishtechnolabs.com/LICENSE.txt Krish License
 * @link      https://www.krishtechnolabs.com/
 */

namespace Ktpl\SearchElastic\Model\Search;

use Magento\Framework\Search\RequestInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Search\Adapter\Mysql\IndexBuilderInterface;
use Magento\CatalogSearch\Model\Search\TableMapper;

/**
 * Class IndexBuilder
 *
 * @package Ktpl\SearchElastic\Model\Search
 */
class IndexBuilder implements IndexBuilderInterface
{
    /**
     * @var ResourceConnection
     */
    private $resource;

    /**
     * @var \Magento\CatalogSearch\Model\Search\TableMapper
     */
    private $tableMapper;

    /**
     * IndexBuilder constructor.
     *
     * @param ResourceConnection $resource
     * @param TableMapper $tableMapper
     */
    public function __construct(
        ResourceConnection $resource,
        TableMapper $tableMapper
    )
    {
        $this->resource = $resource;
        $this->tableMapper = $tableMapper;
    }

    /**
     * Build index builder
     *
     * @param RequestInterface $request
     * @return \Magento\Framework\DB\Select|void
     */
    public function build(RequestInterface $request)
    {
    }
}
