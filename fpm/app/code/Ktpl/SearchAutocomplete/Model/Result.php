<?php
/**
 * KrishTechnolabs
 *
 * PHP version 7
 *
 * @category  KrishTechnolabs
 * @package   Ktpl_SearchAutocomplete
 * @author    Dhara Bhatti <dhara.bhatti@krishtechnolabs.com>
 * @copyright 2019 (c) KrishTechnolabs (https://www.KrishTechnolabs.com/)
 * @license   https://www.krishtechnolabs.com/LICENSE.txt Krish License
 * @link      https://www.krishtechnolabs.com/
 */

namespace Ktpl\SearchAutocomplete\Model;

use Magento\Catalog\Model\Layer\Resolver as LayerResolver;
use Magento\Search\Helper\Data as SearchHelper;
use Magento\Search\Model\QueryFactory;
use Ktpl\SearchAutocomplete\Api\Repository\IndexRepositoryInterface;

/**
 * Class Result
 *
 * @package Ktpl\SearchAutocomplete\Model
 */
class Result
{
    /**
     * @var bool
     */
    private static $isLayerCreated = false;
    /**
     * @var IndexRepositoryInterface
     */
    private $indexRepository;
    /**
     * @var LayerResolver
     */
    private $layerResolver;
    /**
     * @var \Magento\Search\Model\Query
     */
    private $query;
    /**
     * @var Config
     */
    private $config;
    /**
     * @var SearchHelper
     */
    private $searchHelper;
    /**
     * @var QueryFactory
     */
    private $queryFactory;

    /**
     * Result constructor.
     * @param IndexRepositoryInterface $indexRepository
     * @param LayerResolver $layerResolver
     * @param QueryFactory $queryFactory
     * @param Config $config
     * @param SearchHelper $searchHelper
     */
    public function __construct(
        IndexRepositoryInterface $indexRepository,
        LayerResolver $layerResolver,
        QueryFactory $queryFactory,
        Config $config,
        SearchHelper $searchHelper
    )
    {
        $this->indexRepository = $indexRepository;
        $this->layerResolver = $layerResolver;
        $this->queryFactory = $queryFactory;
        $this->config = $config;
        $this->searchHelper = $searchHelper;
    }

    /**
     * Initialize
     *
     * @return void
     */
    public function init()
    {
        $this->query = $this->queryFactory->get();
        if (!self::$isLayerCreated) {
            try {
                $this->layerResolver->create(LayerResolver::CATALOG_LAYER_SEARCH);
            } catch (\Exception $e) {
            } finally {
                self::$isLayerCreated = true;
            }
        }
    }

    /**
     * Convert all results to array
     *
     * @return array
     */
    public function toArray()
    {
        $result = [
            'totalItems' => 0,
            'query' => $this->query->getQueryText(),
            'indices' => [],
            'noResults' => false,
            'urlAll' => $this->searchHelper->getResultUrl($this->query->getQueryText()),
            'optimize' => boolval($this->config->isOptimizeMobile()),
        ];

        $customInstances = [
            'magento_search_query',
            'magento_catalog_categoryproduct',
        ];

        $totalItems = 0;

        foreach ($this->indexRepository->getIndices() as $index) {
            $identifier = $index->getIdentifier();

            if (!$this->config->getIndexOptionValue($identifier, 'is_active')) {
                continue;
            }

            $index->addData($this->config->getIndexOptions($identifier));

            $instance = $this->indexRepository->getInstance($identifier);
            if (!$instance) {
                continue;
            }
            $instance->setIndex($index)
                ->setLimit($this->config->getIndexOptionValue($identifier, 'limit'))
                ->setRepository($this->indexRepository);

            $items = $instance->getItems();
            $size = $instance->getSize();

            $result['indices'][] = [
                'identifier' => $identifier == 'catalogsearch_fulltext' ? 'magento_catalog_product' : $identifier,
                'title' => (string)__($index->getTitle()),
                'order' => (int)$this->config->getIndexOptionValue($identifier, 'order'),
                'items' => $items,
                'totalItems' => $size,
                'isShowTotals' => in_array($identifier, $customInstances) ? false : true,
            ];

            $totalItems += $size;

            if (!in_array($identifier, $customInstances)) {
                $result['totalItems'] += $size;
            }
        }

        usort($result['indices'], function ($a, $b) {
            return $a['order'] - $b['order'];
        });

        if ($this->config->getAutocompleteLayout() == Config::LAYOUT_2_COLUMNS) {
            foreach ($result['indices'] as $key => $index) {
                if ($index['identifier'] == 'magento_catalog_product') {
                    $productFirst = $result['indices'][$key];
                    unset($result['indices'][$key]);
                    array_unshift($result['indices'], $productFirst);
                }
            }
        }

        $result['textAll'] = __('Show all %1 results →', $result['totalItems']);
        $result['textEmpty'] = __('Sorry, nothing found for "%1".', $result['query']);

        $result['noResults'] = $totalItems ? false : true;

        $this->query->setNumResults($result['totalItems']);

        return $result;
    }
}
