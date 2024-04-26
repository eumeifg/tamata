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

namespace Ktpl\SearchElastic\Model\Indexer;

use Magento\Framework\Indexer\ScopeResolver\IndexScopeResolver;
use Magento\Framework\Search\Request\Dimension;
use Magento\Framework\Search\Request\IndexScopeResolverInterface;

/**
 * Class TemporaryResolver
 *
 * @package Ktpl\SearchElastic\Model\Indexer
 */
class TemporaryResolver implements IndexScopeResolverInterface
{
    /**
     * @var string|null
     */
    static $suffix = null;

    /**
     * @var IndexScopeResolver
     */
    private $indexScopeResolver;

    /**
     * TemporaryResolver constructor.
     *
     * @param IndexScopeResolver $indexScopeResolver
     */
    public function __construct(IndexScopeResolver $indexScopeResolver)
    {
        $this->indexScopeResolver = $indexScopeResolver;

        self::$suffix = '_tmp';
    }

    /**
     * Resolve index
     *
     * @param string $index
     * @param array $dimensions
     * @return string
     */
    public function resolve($index, array $dimensions)
    {
        $indexName = $this->indexScopeResolver->resolve($index, $dimensions);
        $indexName .= self::$suffix;

        return $indexName;
    }
}
