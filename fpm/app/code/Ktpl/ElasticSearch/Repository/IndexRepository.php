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

namespace Ktpl\ElasticSearch\Repository;

use Magento\Framework\EntityManager\EntityManager;
use Magento\Framework\Exception\NoSuchEntityException;
use Ktpl\ElasticSearch\Api\Data\Index\InstanceInterface;
use Ktpl\ElasticSearch\Api\Data\IndexInterface;
use Ktpl\ElasticSearch\Api\Repository\IndexRepositoryInterface;
use Ktpl\ElasticSearch\Api\Data\IndexInterfaceFactory;
use Ktpl\ElasticSearch\Model\ResourceModel\Index\CollectionFactory as IndexCollectionFactory;
use Magento\Framework\ObjectManagerInterface;

/**
 * Class IndexRepository
 *
 * @package Ktpl\ElasticSearch\Repository
 */
class IndexRepository implements IndexRepositoryInterface
{
    /**
     * @var array
     */
    private static $indexCache = [];

    /**
     * @var array
     */
    private static $instanceCache = [];

    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var IndexInterfaceFactory
     */
    private $indexFactory;

    /**
     * @var IndexCollectionFactory
     */
    private $indexCollectionFactory;

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var string[]
     */
    private $indicesPool = [];

    /**
     * IndexRepository constructor.
     *
     * @param EntityManager $entityManager
     * @param IndexInterfaceFactory $indexFactory
     * @param IndexCollectionFactory $indexCollectionFactory
     * @param ObjectManagerInterface $objectManager
     * @param array $indices
     */
    public function __construct(
        EntityManager $entityManager,
        IndexInterfaceFactory $indexFactory,
        IndexCollectionFactory $indexCollectionFactory,
        ObjectManagerInterface $objectManager,
        $indices = []
    )
    {
        $this->entityManager = $entityManager;
        $this->indexFactory = $indexFactory;
        $this->indexCollectionFactory = $indexCollectionFactory;
        $this->objectManager = $objectManager;
        $this->indicesPool = $indices;
    }

    /**
     * Get collection
     *
     * @return IndexInterface[]|\Ktpl\ElasticSearch\Model\ResourceModel\Index\Collection
     */
    public function getCollection()
    {
        return $this->indexCollectionFactory->create();
    }

    /**
     * Delete index
     *
     * @param IndexInterface $index
     * @return $this|IndexRepositoryInterface
     * @throws \Exception
     */
    public function delete(IndexInterface $index)
    {
        $this->entityManager->delete($index);

        return $this;
    }

    /**
     * Save index
     *
     * @param IndexInterface $index
     * @return IndexInterface|\Ktpl\ElasticSearch\Model\Index
     * @throws \Exception
     */
    public function save(IndexInterface $index)
    {
        /** @var \Ktpl\ElasticSearch\Model\Index $index */

        $reindexRequired = false;

        if ($index->dataHasChangedFor(IndexInterface::ATTRIBUTES_SERIALIZED)) {
            $reindexRequired = true;
        }

        if ($index->dataHasChangedFor(IndexInterface::PROPERTIES_SERIALIZED)) {
            $reindexRequired = true;
        }

        if ($reindexRequired) {
            $index->setStatus(IndexInterface::STATUS_INVALID);
        }

        $this->entityManager->save($index);

        return $index;
    }

    /**
     * Get index instance
     *
     * @param IndexInterface|string $index
     * @return false|InstanceInterface
     * @throws \Exception
     */
    public function getInstance($index)
    {
        if (is_object($index)) {
            $identifier = $index->getIdentifier();

            $instance = $this->getInstanceByIdentifier($identifier);

            if (!$instance) {
                throw new \Exception(__("Instance for '%1' not found", $identifier));
            }

            $instance
                ->setData($index->getData())
                ->setModel($index);

            return $instance;
        } else {
            $index = str_replace(InstanceInterface::INDEX_PREFIX, '', $index);

            $instance = $this->getInstanceByIdentifier($index);
            $model = $this->get($index);

            $instance
                ->setData($model->getData())
                ->setModel($model);

            return $instance;
        }
    }

    /**
     * Get index instance by identifier
     *
     * @param string $identifier
     * @return InstanceInterface|false
     */
    private function getInstanceByIdentifier($identifier)
    {
        if (!array_key_exists($identifier, self::$instanceCache)) {
            self::$instanceCache[$identifier] = false;

            foreach ($this->indicesPool as $class) {
                if ($this->objectManager->get($class)->getIdentifier() == $identifier) {
                    self::$instanceCache[$identifier] = $this->objectManager->create($class);
                }
            }
        }

        return self::$instanceCache[$identifier];
    }

    /**
     * Get index by id
     *
     * @param int|string $id
     * @return IndexInterface|mixed
     */
    public function get($id)
    {
        $id = str_replace(InstanceInterface::INDEX_PREFIX, '', $id);

        if (!array_key_exists($id, self::$indexCache)) {
            $index = $this->create();

            if (is_numeric($id)) {
                $this->entityManager->load($index, $id);
            } else {
                /** @var \Ktpl\ElasticSearch\Model\Index $index */
                $index = $index->load($id, IndexInterface::IDENTIFIER);
            }

            self::$indexCache[$id] = $index;
        }

        return self::$indexCache[$id];
    }

    /**
     * Create index factory instance
     *
     * @return IndexInterface
     */
    public function create()
    {
        return $this->indexFactory->create();
    }

    /**
     * Get index list
     *
     * @return array|InstanceInterface[]
     */
    public function getList()
    {
        $result = [];

        foreach ($this->indicesPool as $class) {
            $result[] = $this->objectManager->get($class);
        }

        return $result;
    }
}
