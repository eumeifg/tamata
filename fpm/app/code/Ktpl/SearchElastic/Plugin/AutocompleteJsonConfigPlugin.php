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

namespace Ktpl\SearchElastic\Plugin;

use Ktpl\ElasticSearch\Api\Repository\IndexRepositoryInterface;
use Ktpl\SearchElastic\Model\Config;
use Magento\Framework\Indexer\ScopeResolver\IndexScopeResolver;
use Magento\Framework\Search\Request\Dimension;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class AutocompleteJsonConfigPlugin
 *
 * @package Ktpl\SearchElastic\Plugin
 */
class AutocompleteJsonConfigPlugin
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var IndexRepositoryInterface
     */
    private $indexRepository;

    /**
     * @var IndexScopeResolver
     */
    private $resolver;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * AutocompleteJsonConfigPlugin constructor.
     *
     * @param Config $config
     * @param IndexRepositoryInterface $indexRepository
     * @param IndexScopeResolver $resolver
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Config $config,
        IndexRepositoryInterface $indexRepository,
        IndexScopeResolver $resolver,
        StoreManagerInterface $storeManager
    )
    {
        $this->config = $config;
        $this->indexRepository = $indexRepository;
        $this->resolver = $resolver;
        $this->storeManager = $storeManager;
    }

    /**
     * Add identifier
     *
     * @param $subject
     * @param $config
     * @return array
     */
    public function afterGenerate($subject, $config)
    {
        if ($config['engine'] !== 'elastic') {
            return $config;
        }

        $config = array_merge($config, $this->getEngineConfig());

        foreach ($this->storeManager->getStores() as $store) {
            foreach ($config['indexes'][$store->getId()] as $identifier => $data) {
                $data = array_merge($data, $this->getEngineIndexConfig(
                    $identifier,
                    new Dimension('scope', $store->getId())
                ));

                $config['indexes'][$store->getId()][$identifier] = $data;
            }
        }

        return $config;
    }

    /**
     * Get engine config
     *
     * @return array
     */
    public function getEngineConfig()
    {
        return [
            'host' => $this->config->getHost(),
            'port' => $this->config->getPort(),
            'available' => true,
        ];
    }

    /**
     * Get engine index config
     *
     * @param string $identifier
     * @param $dimension
     * @return array
     */
    public function getEngineIndexConfig($identifier, $dimension)
    {
        $instance = $this->indexRepository->getInstance($identifier);

        $indexName = $this->config->getIndexName(
            $this->resolver->resolve($instance->getIndexName(), [$dimension])
        );

        $result = [];
        $result['index'] = $indexName;
        $result['fields'] = $instance->getAttributeWeights();

        return $result;
    }
}
