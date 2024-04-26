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

namespace Ktpl\ElasticSearch\Plugin;

use Magento\Framework\Search\Request\Config\FilesystemReader;
use Ktpl\ElasticSearch\Api\Data\Index\InstanceInterface;
use Ktpl\ElasticSearch\Api\Repository\IndexRepositoryInterface;

/**
 * Class RequestConfigPlugin
 *
 * @package Ktpl\ElasticSearch\Plugin
 */
class RequestConfigPlugin
{
    /**
     * @var IndexRepositoryInterface
     */
    private $indexRepository;

    /**
     * RequestConfigPlugin constructor.
     *
     * @param IndexRepositoryInterface $indexRepository
     */
    public function __construct(
        IndexRepositoryInterface $indexRepository
    )
    {
        $this->indexRepository = $indexRepository;
    }

    /**
     * Generate request array
     *
     * @param FilesystemReader $fsReader
     * @param array $requests
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterRead(
        FilesystemReader $fsReader,
        $requests
    )
    {
        // add requests for all possible search indices
        foreach ($this->indexRepository->getList() as $instance) {
            $requests[$instance->getIdentifier()] = $this->generateRequest($instance);

            if ($instance->getIdentifier() == 'catalogsearch_fulltext') {
                $requests = $this->updateNativeRequest($instance, $requests);
            }
        }

        // add requests for added indices (with fields weights)
        foreach ($this->indexRepository->getCollection() as $index) {
            $instance = $this->indexRepository->getInstance($index);
            $requests[$instance->getIdentifier()] = $this->generateRequest($instance);
        }

        return $requests;
    }

    /**
     * Generate request
     *
     * @param InstanceInterface $index
     * @return array
     */
    private function generateRequest($index)
    {
        $identifier = $index->getIdentifier();

        $request = [
            'dimensions' => [
                'scope' => [
                    'name' => 'scope',
                    'value' => 'default',
                ],
            ],
            'query' => $identifier,
            'index' => $identifier,
            'from' => '0',
            'size' => '1000',
            'filters' => [],
            'aggregations' => [],
            'queries' => [
                $identifier => [
                    'type' => 'boolQuery',
                    'name' => $identifier,
                    'boost' => 1,
                    'queryReference' => [
                        [
                            'clause' => 'should',
                            'ref' => 'search_query',
                        ],
                    ],
                ],
                'search_query' => [
                    'type' => 'matchQuery',
                    'name' => $identifier,
                    'value' => '$search_term$',
                    'match' => [
                        [
                            'field' => '*',
                        ],
                    ],
                ],
            ],
        ];

        foreach ($index->getAttributeWeights() as $attribute => $boost) {
            if($attribute == 'price'){
                /* Resolve search issue in v2.3.4.*/
               continue; 
            }
            $request['queries']['search_query']['match'][] = [
                'field' => $attribute,
                'boost' => $boost,
            ];
        }

        return $request;
    }

    /**
     * Update navigate request
     *
     * @param InstanceInterface $index
     * @param array $requests
     * @return array
     */
    private function updateNativeRequest($index, $requests)
    {
        $requests['quick_search_container']['queries']['search']['match'] = [[
            'field' => '*',
        ]];

        foreach ($index->getAttributeWeights() as $attribute => $boost) {
            if($attribute == 'price'){
               /* Resolve search issue in v2.3.4.*/
               continue; 
            }
            $requests['quick_search_container']['queries']['search']['match'][] = [
                'field' => $attribute,
                'boost' => $boost,
            ];
        }

        return $requests;
    }
}
