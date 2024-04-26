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

namespace Ktpl\SearchElastic\Model\Indexer\Scope;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Registry;
use Ktpl\SearchElastic\Model\Engine;
use Ktpl\SearchElastic\Model\Indexer\IndexerHandler;
use Ktpl\ElasticSearch\Service\CompatibilityService;

/**
 * Class IndexSwitcher
 *
 * @package Ktpl\SearchElastic\Model\Indexer\Scope
 */
class IndexSwitcher extends IndexSwitcherParent
{
    /**
     * @var Resource
     */
    private $resource;

    /**
     * @var ScopeProxy / IndexScopeResolver
     */
    private $resolver;

    /**
     * @var State
     */
    private $state;

    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var Engine
     */
    private $engine;

    /**
     * IndexSwitcher constructor.
     *
     * @param ResourceConnection $resource
     * @param StateMediator $state
     * @param Registry $registry
     * @param Engine $engine
     */
    public function __construct(
        ResourceConnection $resource,
        StateMediator $state,
        Registry $registry,
        Engine $engine
    )
    {
        $this->resource = $resource;
        $this->state = $state->get();
        $this->registry = $registry;
        $this->engine = $engine;
        if (CompatibilityService::is22() || CompatibilityService::is23()) {
            $this->resolver = CompatibilityService::getObjectManager()
                ->create('Magento\CatalogSearch\Model\Indexer\Scope\ScopeProxy');
        } else {
            $this->resolver = CompatibilityService::getObjectManager()
                ->create('Magento\Framework\Indexer\ScopeResolver\IndexScopeResolver');
        }
    }

    /**
     * {@inheritdoc}
     *
     * @param array $dimensions
     * @throws IndexTableNotExistException
     */
    public function switchIndex(array $dimensions)
    {
        if (StateMediator::USE_TEMPORARY_INDEX === $this->state->getState()) {
            $index = $this->registry->registry(IndexerHandler::ACTIVE_INDEX);

            $temporalIndexTable = $this->resolver->resolve($index, $dimensions);

            $this->state->useRegularIndex();

            $tableName = $this->resolver->resolve($index, $dimensions);

            $this->engine->removeIndex($tableName);
            $this->engine->moveIndex($temporalIndexTable, $tableName);

            $this->state->useTemporaryIndex();
        }
    }
}
