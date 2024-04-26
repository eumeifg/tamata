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

namespace Ktpl\SearchElastic;

use Elasticsearch\ClientBuilder;

if (php_sapi_name() == "cli") {
    return;
}

$configFile = dirname(dirname(dirname(__DIR__))) . '/etc/autocomplete.json';

if (stripos(__DIR__, 'vendor') !== false) {
    $configFile = dirname(dirname(dirname(dirname(dirname(__DIR__))))) . '/app/etc/autocomplete.json';
}

if (!file_exists($configFile)) {
    return;
}

$config = \Zend_Json::decode(file_get_contents($configFile));

if ($config['engine'] !== 'elastic') {
    return;
}

/**
 * Class ElasticAutocomplete
 *
 * @package Ktpl\SearchElastic
 */
class ElasticAutocomplete
{
    /**
     * @var array
     */
    private $config;

    /**
     * ElasticAutocomplete constructor.
     *
     * @param array $config
     */
    public function __construct(
        array $config
    )
    {
        $this->config = $config;
    }

    /**
     * Process
     *
     * @return array
     */
    public function process()
    {
        $result = [];
        $totalItems = 0;

        foreach ($this->config['indexes'][$this->getStoreId()] as $identifier => $config) {
            $query = [
                'index' => $config['index'],
                'type' => 'doc',
                'size' => $config['limit'],
                'body' => [
                    'query' => [
                        'bool' => [
                            'must' => [
                                [
                                    'query_string' => [
                                        'fields' => $this->getWeights($identifier),
                                        'query' => $this->getQuery(),
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ];

            try {
                $response = $this->getClient()->search($query);
                $total = $response['hits']['total'];
                $items = $this->mapHits($response['hits']['hits'], $config);

                if ($total && $items) {
                    $result['indices'][] = [
                        'identifier' => $identifier == 'catalogsearch_fulltext' ? 'magento_catalog_product' : $identifier,
                        'isShowTotals' => true,
                        'order' => $config['order'],
                        'title' => $config['title'],
                        'totalItems' => $total,
                        'items' => $items,
                    ];
                    $totalItems += $total;
                }
            } catch (\Exception $e) {
            }
        }

        $result['query'] = $this->getQueryText();
        $result['totalItems'] = $totalItems;
        $result['noResults'] = $totalItems == 0;
        $result['textEmpty'] = sprintf($this->config['textEmpty'], $this->getQueryText());
        $result['textAll'] = sprintf($this->config['textAll'], $result['totalItems']);
        $result['urlAll'] = $this->config['urlAll'][$this->getStoreId()] . $this->getQueryText();

        return $result;
    }

    /**
     * Get store id
     *
     * @return int
     */
    private function getStoreId()
    {
        return filter_input(INPUT_GET, 'store_id') != null ? filter_input(INPUT_GET, 'store_id') : array_keys($this->config['indexes'][$this->getStoreId()])[0];
    }

    /**
     * Get weights
     *
     * @param $identifier
     * @return array
     */
    private function getWeights($identifier)
    {
        $weights = [
            'options^1',
        ];
        foreach ($this->config['indexes'][$this->getStoreId()][$identifier]['fields'] as $f => $w) {
            $weights[] = $f . '^' . pow(2, $w);
        }

        return $weights;
    }

    /**
     * Get query
     *
     * @return string
     */
    private function getQuery()
    {
        $terms = array_filter(explode(" ", $this->getQueryText()));

        $conditions = [];
        foreach ($terms as $term) {
            $term = $this->escape($term);
            $conditions[] = "($term OR *$term*)";
        }

        return implode(" AND ", $conditions);
    }

    /**
     * Get query text
     *
     * @return string
     */
    private function getQueryText()
    {
        return filter_input(INPUT_GET, 'q') != null ? filter_input(INPUT_GET, 'q') : '';
    }

    /**
     * Escape
     * @param $value
     * @return string|string[]|null
     */
    private function escape($value)
    {
        $pattern = '/(\+|-|\/|&&|\|\||!|\(|\)|\{|}|\[|]|\^|"|~|\*|\?|:|\\\)/';
        $replace = '\\\$1';

        return preg_replace($pattern, $replace, $value);
    }

    /**
     * Get client
     *
     * @return \Elasticsearch\Client
     */
    private function getClient()
    {
        $client = ClientBuilder::fromConfig([
            'hosts' => [$this->config['host'] . ':' . $this->config['port']],
        ]);

        return $client;
    }

    /**
     * Map hits
     *
     * @param $hits
     * @param $config
     * @return array
     */
    private function mapHits($hits, $config)
    {
        $items = [];
        foreach ($hits as $hit) {
            if (count($items) > $config['limit']) {
                break;
            }

            if (!isset($hit['_source'])
                || !isset($hit['_source']['autocomplete'])
                || !is_array($hit['_source']['autocomplete'])) {
                continue;
            }

            $item = [
                'title' => null,
                'url' => null,
                'sku' => null,
                'image' => null,
                'description' => null,
                'price' => null,
                'rating' => null,
            ];

            $item = array_merge($item, $hit['_source']['autocomplete']);

            $item['cart'] = [
                'visible' => false,
                'params' => [
                    'action' => null,
                    'data' => [
                        'product' => null,
                        'uenc' => null,
                    ],
                ],
            ];

            if (!isset($item['name']) || !$item['name']) {
                continue;
            }

            $items[] = $item;
        }

        return $items;
    }
}

$result = (new ElasticAutocomplete($config))->process();

//s start
exit(\Zend_Json::encode($result));
//s end
/** m start
 * return \Zend_Json::encode($result);
 * m end */
