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

namespace Ktpl\SearchElastic\Adapter\Query;

use Magento\Framework\Search\Request\QueryInterface;
use Magento\Framework\Search\Request\Query\Match;
use Ktpl\ElasticSearch\Api\Service\QueryServiceInterface;
use Ktpl\ElasticSearch\Model\Config;

/**
 * Class MatchQuery
 *
 * @package Ktpl\SearchElastic\Adapter\Query
 */
class MatchQuery
{
    /**
     * @var QueryServiceInterface
     */
    private $queryService;

    /**
     * @var Config
     */
    private $_config;

    /**
     * MatchQuery constructor.
     *
     * @param QueryServiceInterface $queryService
     */
    public function __construct(
        QueryServiceInterface $queryService,
        Config $config
    )
    {
        $this->queryService = $queryService;
        $this->_config = $config;
    }

    /**
     * Build match query
     *
     * @param array $query
     * @param QueryInterface $matchQuery
     * @return array
     */
    public function build(array $query, QueryInterface $matchQuery)
    {
        /** @var Match $matchQuery */

        $searchQuery = $this->queryService->build($matchQuery->getValue());

        $fields = ['options' => 1];
        foreach ($matchQuery->getMatches() as $match) {
            $field = $match['field'];
            if ($field == '*') {
                continue;
            }

            $boost = isset($match['boost']) ? intval((string)$match['boost']) : 1; //sometimes boots is a Phrase
            $fields[$field] = $boost;
        }

        $queryStringParams = [
            'fields' => array_keys($fields),
            'query' => $this->compileQuery($searchQuery)
        ];

        if ($this->_config->isFuzzySearchEnabled()) {
            $queryStringParams['fuzziness'] = '3';
        }

        $query['bool']['must'][]['query_string'] = $queryStringParams;

        foreach ($fields as $field => $boost) {
            if ($boost > 1) {
                $qs = array_filter(explode(' ', $matchQuery->getValue()));

                foreach ($qs as $q) {
                    $query['bool']['should'][]['wildcard'][$field] = [
                        'value' => '*' . strtolower($q) . '*',
                        'boost' => pow(2, $boost),
                    ];
                }
            }
        }

        return $query;
    }

    /**
     * Compile match query
     *
     * @param $query
     * @return string
     */
    private function compileQuery($query)
    {
        $compiled = [];
        foreach ($query as $directive => $value) {
            switch ($directive) {
                case '$like':
                    $compiled[] = '(' . $this->compileQuery($value) . ')';
                    break;

                case '$!like':
                    $compiled[] = '(NOT ' . $this->compileQuery($value) . ')';
                    break;

                case '$and':
                    $and = [];
                    foreach ($value as $item) {
                        $and[] = $this->compileQuery($item);
                    }
                    $compiled[] = '(' . implode(' AND ', $and) . ')';
                    break;

                case '$or':
                    $or = [];
                    foreach ($value as $item) {
                        $or[] = $this->compileQuery($item);
                    }
                    $compiled[] = '(' . implode(' OR ', $or) . ')';
                    break;

                case '$term':
                    $phrase = $this->escape($value['$phrase']);
                    $fuzzyPhrase = ($this->_config->isFuzzySearchEnabled()) ? $phrase . "~" : $phrase;
                    switch ($value['$wildcard']) {
                        case Config::WILDCARD_INFIX:
                            $compiled[] = "$fuzzyPhrase OR *$phrase*";
                            break;
                        case Config::WILDCARD_PREFIX:
                            $compiled[] = "$fuzzyPhrase OR *$phrase";
                            break;
                        case Config::WILDCARD_SUFFIX:
                            $compiled[] = "$fuzzyPhrase OR $phrase*";
                            break;
                        case Config::WILDCARD_DISABLED:
                            $compiled[] = $fuzzyPhrase;
                            break;
                    }
                    break;
            }
        }

        return implode(' AND ', $compiled);
    }

    /**
     * Escape query pattern
     *
     * @param string $value
     * @return string
     */
    private function escape($value)
    {
        $pattern = '/(\+|-|\/|&&|\|\||!|\(|\)|\{|}|\[|]|\^|"|~|\*|\?|:|\\\)/';
        $replace = '\\\$1';

        return preg_replace($pattern, $replace, $value);
    }
}
