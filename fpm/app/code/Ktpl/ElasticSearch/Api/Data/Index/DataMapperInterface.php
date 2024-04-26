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

namespace Ktpl\ElasticSearch\Api\Data\Index;

use Magento\Framework\Search\Request\Dimension;

/**
 * Interface DataMapperInterface
 *
 * @package Ktpl\ElasticSearch\Api\Data\Index
 */
interface DataMapperInterface
{
    /**
     * @param array $documents
     * @param Dimension[] $dimensions
     * @param string $indexIdentifier
     * @return array
     */
    public function map(array $documents, $dimensions, $indexIdentifier);
}
