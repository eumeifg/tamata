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

namespace Ktpl\ElasticSearch\Api\Service;

use Ktpl\ElasticSearch\Api\Data\SynonymInterface;

/**
 * Interface SynonymServiceInterface
 * @package Ktpl\ElasticSearch\Api\Service
 */
interface SynonymServiceInterface
{
    /**
     * Get synonyms
     *
     * @param array $terms
     * @param int $storeId
     * @return array
     */
    public function getSynonyms(array $terms, $storeId);

    /**
     * Get complex synonyms
     *
     * @param $storeId
     * @return SynonymInterface[]
     */
    public function getComplexSynonyms($storeId);
}
