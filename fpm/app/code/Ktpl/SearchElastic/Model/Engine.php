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

namespace Ktpl\SearchElastic\Model;

use Elasticsearch\ClientBuilder;
use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Eav\Model\Config as EavConfig;
use Psr\Log\LoggerInterface;

/**
 * Class Engine
 *
 * @package Ktpl\SearchElastic\Model
 */
class Engine
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var EavConfig
     */
    private $eavConfig;

    /**
     * @var string
     */
    private $host;

    /**
     * @var int
     */
    private $port;

    /**
     * @var \Elasticsearch\Client
     */
    private $client;

    /**
     * Engine constructor.
     *
     * @param Config $config
     * @param EavConfig $eavConfig
     * @param LoggerInterface $logger
     */
    public function __construct(
        Config $config,
        EavConfig $eavConfig,
        LoggerInterface $logger
    )
    {
        $this->config = $config;
        $this->eavConfig = $eavConfig;
        $this->logger = $logger;

        $this->host = $this->config->getHost();
        $this->port = $this->config->getPort();
        if (class_exists('Elasticsearch\ClientBuilder')) {
            $this->client = ClientBuilder::fromConfig([
                'hosts' => [
                    $this->host . ':' . $this->port,
                ],
            ]);
        } else {
            $this->client = false;
        }
    }

    /**
     * Save documents
     *
     * @param string $indexName
     * @param array $documents
     */
    public function saveDocuments($indexName, array $documents)
    {
        $this->ensureIndex($indexName);

        foreach ($documents as $id => $document) {
            try {
                $exists = $this->getClient()->exists([
                    'index' => $this->config->getIndexName($indexName),
                    'type' => Config::DOCUMENT_TYPE,
                    'id' => $id,
                ]);

                if ($exists) {
                    $this->getClient()
                        ->delete([
                            'index' => $this->config->getIndexName($indexName),
                            'type' => Config::DOCUMENT_TYPE,
                            'id' => $id,
                        ]);
                }

                $this->getClient()
                    ->create([
                        'index' => $this->config->getIndexName($indexName),
                        'type' => Config::DOCUMENT_TYPE,
                        'id' => $id,
                        'body' => $document,
                    ]);
            } catch (\Exception $e) {

            }
        }
    }

    /**
     * Ensure index
     *
     * @param string $indexName
     * @return bool
     */
    public function ensureIndex($indexName)
    {
        try {
            if (!$this->isIndexExists($indexName)) {
                $this->getClient()->indices()->create([
                    'index' => $this->config->getIndexName($indexName),
                    'body' => [
                        'settings' => [
                            'index.mapping.total_fields.limit' => 1000000,
                            'max_result_window' => 1000000,
                            'analysis' => [
                                'analyzer' => [
                                    'custom' => [
                                        'type' => 'custom',
                                        'tokenizer' => 'whitespace',
                                        'filter' => [
                                            'word',
                                            'lowercase',
                                            'asciifolding',
                                        ],
                                    ],
                                    'custom_raw' => [
                                        'type' => 'custom',
                                        'tokenizer' => 'keyword',
                                        'filter' => [
                                        ],
                                    ],
                                ],
                                'filter' => [
                                    'word' => [
                                        'type' => 'word_delimiter',
                                        'generate_word_parts' => false,
                                        'generate_number_parts' => false,
                                        'catenate_words' => false,
                                        'catenate_numbers' => false,
                                        'catenate_all' => false,
                                        'split_on_case_change' => false,
                                        'preserve_original' => true,
                                        'split_on_numerics' => false,
                                        'stem_english_possessive' => true,
                                    ],
                                ],
                            ],
                        ],
                    ],
                ]);
                $this->getClient()->cluster()->health([
                    'index' => $this->config->getIndexName($indexName),
                    'wait_for_active_shards' => 1,
                ]);
            }
        } catch (\Exception $e) {
            $this->logger->error($e);
        }

        $this->ensureDocumentType($indexName);

        return true;
    }

    /**
     * Check if index exists
     *
     * @param string $indexName
     * @return bool
     */
    private function isIndexExists($indexName)
    {
        return $this->getClient()->indices()->exists([
            'index' => $this->config->getIndexName($indexName),
        ]);
    }

    /**
     * Get client
     *
     * @return \Elasticsearch\Client
     */
    public function getClient()
    {
        if ($this->client) {
            return $this->client;
        } else {
            throw new \Exception('Elasticsearch library is not installed. Run "composer require elasticsearch/elasticsearch:~5.1" to fix this issue');
        }
    }

    /**
     * Ensure document type
     *
     * @param string $indexName
     * @return bool
     */
    public function ensureDocumentType($indexName)
    {
        try {
            if (!$this->isMappingExists($indexName)) {
                $mapping = [
                    'index' => $this->config->getIndexName($indexName),
                    'type' => Config::DOCUMENT_TYPE,
                    'update_all_types' => true,
                    'body' => [
                        'properties' => [
                            'children' => [
                                'type' => 'nested',
                            ],
                            'price_raw' => [
                                'type' => 'float',
                            ],
                            'description_raw' => [
                                'type' => 'text',
                            ],
                            'short_description_raw' => [
                                'type' => 'text',
                            ],
                        ],
                        'dynamic_templates' => [
                            [
                                'raw_string_mapping' => [
                                    'match' => '*_raw',
                                    'match_mapping_type' => 'string',
                                    'mapping' => [
                                        'type' => 'text',
                                        'analyzer' => 'custom_raw',
                                        'index' => true,
                                        'ignore_above' => 256,
                                        'fielddata' => true,
                                        'ignore_malformed' => true,
                                    ],
                                ],
                            ],
                            [
                                'raw_mapping' => [
                                    'match' => '*_raw',
                                    'mapping' => [
                                        'analyzer' => 'custom_raw',
                                        'index' => true,
                                        'ignore_above' => 256,
                                        'fielddata' => true,
                                        'ignore_malformed' => true,
                                    ],
                                ],
                            ],
                            [
                                'string' => [
                                    'match_mapping_type' => 'string',
                                    'mapping' => [
                                        'analyzer' => 'custom',
                                        'type' => 'text',
                                        'ignore_malformed' => true,
                                    ],
                                ],
                            ],
                        ],
                    ],
                ];

                $attributeCodes = $this->eavConfig->getEntityAttributeCodes(
                    ProductAttributeInterface::ENTITY_TYPE_CODE
                );

                foreach ($attributeCodes as $attributeCode) {
                    $attribute = $this->eavConfig->getAttribute(
                        ProductAttributeInterface::ENTITY_TYPE_CODE,
                        $attributeCode
                    );

                    if ($attribute->getBackendType() == 'decimal') {
                        $mapping['body']['properties'][$attribute->getAttributeCode() . '_raw'] = [
                            'type' => 'float',
                        ];
                    }
                }

                $this->getClient()->indices()->putMapping($mapping);
            }
        } catch (\Exception $e) {
            $this->logger->error($e);
        }

        return true;
    }

    /**
     * Check of mapping exists
     *
     * @param string $indexName
     * @return bool
     */
    private function isMappingExists($indexName)
    {
        try {
            $mapping = $this->getClient()->indices()->getMapping([
                'index' => $this->config->getIndexName($indexName),
                'type' => Config::DOCUMENT_TYPE,
            ]);
        } catch (\Exception $e) {
            return false;
        }

        return $mapping ? true : false;
    }

    /**
     * Delete documents
     *
     * @param $indexName
     * @param array $documents
     */
    public function deleteDocuments($indexName, array $documents)
    {
        $this->ensureIndex($indexName);

        foreach ($documents as $document) {
            try {
                $exists = $this->getClient()->exists([
                    'index' => $this->config->getIndexName($indexName),
                    'type' => Config::DOCUMENT_TYPE,
                    'id' => $document,
                ]);

                if ($exists) {
                    $this->getClient()
                        ->delete([
                            'index' => $this->config->getIndexName($indexName),
                            'type' => Config::DOCUMENT_TYPE,
                            'id' => $document,
                        ]);
                }
            } catch (\Exception $e) {
                $this->logger->error($e);
            }
        }
    }

    /**
     * Clean documents
     *
     * @param $indexName
     */
    public function cleanDocuments($indexName)
    {
        $this->ensureIndex($indexName);

        try {
            $this->removeIndex($indexName);

            $this->ensureIndex($indexName);
        } catch (\Exception $e) {
            $this->logger->error($e);
        }
    }

    /**
     * Remove index
     *
     * @param $indexName
     */
    public function removeIndex($indexName)
    {
        if ($this->isIndexExists($indexName)) {
            try {
                $this->getClient()->indices()->close([
                    'index' => $this->config->getIndexName($indexName),
                ]);
            } catch (\Exception $e) {
            }
            try {
                $this->getClient()->indices()->delete([
                    'index' => $this->config->getIndexName($indexName),
                ]);
            } catch (\Exception $e) {
                $this->logger->error($e);
            }
        }
    }

    /**
     * Move index
     *
     * @param $oldIndexName
     * @param $newIndexName
     * @throws \Exception
     */
    public function moveIndex($oldIndexName, $newIndexName)
    {
        if ($oldIndexName === $newIndexName) {
            return;
        }

        $this->ensureIndex($oldIndexName);

        $this->removeIndex($newIndexName);
        $this->ensureIndex($newIndexName);

        $esOldIndex = $this->config->getIndexName($oldIndexName);
        $esNewIndex = $this->config->getIndexName($newIndexName);

        $mapping = $this->getClient()->indices()->getMapping([
            'index' => $esOldIndex,
        ]);

        $this->getClient()->indices()->putMapping([
            'index' => $esNewIndex,
            'type' => Config::DOCUMENT_TYPE,
            'body' => $mapping[$esOldIndex]['mappings'][Config::DOCUMENT_TYPE],
        ]);

        $this->getClient()->indices()->refresh([
            'index' => $esOldIndex,
        ]);

        try {
            $this->getClient()->reindex([
                'wait_for_completion' => true,
                'timeout' => '10m',
                'refresh' => true,
                'body' => [
                    'conflicts' => 'proceed',
                    'source' => [
                        'index' => $esOldIndex,
                    ],
                    'dest' => [
                        'index' => $esNewIndex,
                        'op_type' => 'create',
                    ],
                ],
            ]);
        } catch (\Exception $e) {
            $this->logger->error($e);
        }
    }

    /**
     * Engine status
     *
     * @param string &$output
     * @return bool
     * @throws \Exception
     */
    public function status(&$output = '')
    {
        if (!$this->isAvailable($output)) {
            return false;
        }

        $indices = $this->client->cat()->indices();
        if (is_array($indices)) {
            foreach ($indices as $info) {
                $output .= $info['docs.count'] . ":" . $info['index'] . PHP_EOL;
            }
        }
        $output .= PHP_EOL;

        $stats = $this->client->info();
        if (is_array($stats) && isset($stats['version']) && isset($stats['version']['number'])) {
            $version = $stats['version']['number'];

            if (version_compare($version, '5.2.0') == -1) {
                $output .= 'Wrong version.' . PHP_EOL;
                $output .= 'Required version is 5.2.*' . PHP_EOL;
                $output .= 'Current version is ' . $version . PHP_EOL;

                return false;
            }
        }

        $output .= $this->prettyPrint($stats);

        $output .= $this->prettyPrint($this->client->indices()->stats());

        try {
            $mapping = $this->client->indices()->getMapping([
                'index' => '*',
            ]);
            $output .= $this->prettyPrint($mapping);

            $settings = $this->client->indices()->getSettings([
                'index' => '*',
            ]);
            $output .= $this->prettyPrint($settings);
        } catch (\Exception $e) {
            $output .= $e->getMessage();
        }

        return true;
    }

    /**
     * Check if engine available
     *
     * @param string &$output
     * @return bool
     * @throws \Exception
     */
    public function isAvailable(&$output = '')
    {
        $output .= '';

        try {
            if ($this->client && $this->client->ping()) {
                return true;
            } else {
                $output .= 'Engine is not available';
            }
        } catch (\Exception $e) {
            $output .= 'Status: ' . $e->getCode();
        }

        return false;
    }

    /**
     * Print mapping or settings
     *
     * @param array $array
     * @param int $offset
     * @return string
     */
    private function prettyPrint($array, $offset = 0)
    {
        $str = "";
        if (is_array($array)) {
            foreach ($array as $key => $val) {
                if (is_array($val)) {
                    $str .= str_repeat(' ', $offset) . $key . ': ' . PHP_EOL . $this->prettyPrint($val, $offset + 5);
                } else {
                    $str .= str_repeat(' ', $offset) . $key . ': ' . $val . PHP_EOL;
                }
            }
        }
        $str .= '</ul>';

        return $str;
    }

    /**
     * Reset engine
     *
     * @param string &$output
     * @return bool
     * @throws \Exception
     */
    public function reset(&$output = '')
    {
        if (!$this->isAvailable($output)) {
            return false;
        }

        $out = $this->client->indices()->delete([
            'index' => '*',
        ]);

        $output .= $this->prettyPrint($out);

        return true;
    }
}
