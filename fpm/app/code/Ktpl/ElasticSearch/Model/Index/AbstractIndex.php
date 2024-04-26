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

namespace Ktpl\ElasticSearch\Model\Index;

use Magento\Framework\DataObject;
use Ktpl\ElasticSearch\Api\Data\Index\DataMapperInterface;
use Ktpl\ElasticSearch\Api\Data\Index\InstanceInterface;
use Ktpl\ElasticSearch\Api\Data\IndexInterface;

/**
 * Class AbstractIndex
 *
 * @package Ktpl\ElasticSearch\Model\Index
 */
abstract class AbstractIndex extends DataObject implements InstanceInterface
{
    /**
     * @var Context
     */
    protected $context;
    /**
     * @var \Magento\Framework\Data\Collection\AbstractDb[]
     */
    protected $searchCollection = [];
    /**
     * @var DataMapperInterface[]
     */
    private $dataMappers;

    /**
     * AbstractIndex constructor.
     *
     * @param Context $context
     * @param array $dataMappers
     */
    public function __construct(
        Context $context,
        $dataMappers = []
    )
    {
        $this->context = $context->getInstance();
        $this->context->getIndexer()->setIndex($this);
        $this->context->getSearcher()->setIndex($this);

        ksort($dataMappers);
        $this->dataMappers = $dataMappers;

        parent::__construct();
    }

    /**
     * Get indexer
     *
     * @return Indexer
     */
    public function getIndexer()
    {
        return $this->context->getIndexer();
    }

    /**
     * Get index name
     *
     * @return string
     */
    public function getIndexName()
    {
        if ($this->getIdentifier() == 'catalogsearch_fulltext') {
            return $this->getIdentifier();
        }

        $identifier = $this->getIdentifier() . '_' . $this->getModel()->getId();

        return InstanceInterface::INDEX_PREFIX . $identifier;
    }

    /**
     * Get data mappers
     *
     * @param string $engine
     * @return array|DataMapperInterface[]
     */
    public function getDataMappers($engine)
    {
        $mappers = [];

        foreach ($this->dataMappers as $key => $mapper) {
            if (strpos($key, 'engine-') === false) {
                // global mapper
                $mappers[$key] = $mapper;
            } elseif (strpos($key, "engine-$engine") !== false) {
                // engine based mapper
                $mappers[$key] = $mapper;
            }
        }

        return $mappers;
    }

    /**
     * Get text
     *
     * @return string
     */
    public function __toString()
    {
        return __($this->getName())->getText();
    }

    /**
     * Get query response
     *
     * @return \Magento\Framework\Search\Response\QueryResponse|\Magento\Framework\Search\ResponseInterface
     */
    public function getQueryResponse()
    {
        return $this->context->getSearcher()->getQueryResponse();
    }

    /**
     * Search collection
     *
     * @return \Magento\Framework\Data\Collection\AbstractDb
     */
    public function getSearchCollection()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

        /** @var \Magento\Store\Model\StoreManagerInterface $storeManager */
        $storeManager = $objectManager->get('Magento\Store\Model\StoreManagerInterface');

        /** @var \Magento\Store\Model\App\Emulation $emulation */
        $emulation = $objectManager->create('Magento\Store\Model\App\Emulation');

        $storeId = $this->getData('store_id') ? $this->getData('store_id') : 0;

        if (!isset($this->searchCollection[$storeId])) {
            $isEmulation = false;
            if ($storeId && $storeId != $storeManager->getStore()->getId()
            ) {
                $emulation->startEnvironmentEmulation($storeId);
                $isEmulation = true;
            }

            $this->searchCollection[$storeId] = $this->buildSearchCollection();

            if ($isEmulation) {
                $this->searchCollection[$storeId]->getSize();
                // get size before switch to default store
                $emulation->stopEnvironmentEmulation();
            }
        }

        return $this->searchCollection[$storeId];
    }

    /**
     * Wights of attributes
     *
     * @return array
     */
    public function getAttributeWeights()
    {
        if ($this->getModel()) {
            return $this->getModel()->getAttributes();
        }

        return $this->getAttributes();
    }

    /**
     * Attribute id
     *
     * @param string $attributeCode
     * @return int
     */
    public function getAttributeId($attributeCode)
    {
        $attributes = array_keys($this->getAttributes());

        return array_search($attributeCode, $attributes);
    }

    /**
     * Reindex all stores
     *
     * @param int $storeId
     * @return bool
     */
    public function reindexAll($storeId = null)
    {
        $this->context->getIndexer()->reindexAll($storeId);

        $index = $this->getModel();
        $index->setStatus(IndexInterface::STATUS_READY);
        $index->save();

        return true;
    }

    /**
     * Callback on model save after
     *
     * @return $this
     */
    public function afterModelSave()
    {
        return $this;
    }

    /**
     * Attribute code by id
     *
     * @param int $attributeId
     * @return string
     */
    public function getAttributeCode($attributeId)
    {
        $keys = array_keys($this->getAttributes());

        return isset($keys[$attributeId]) ? $keys[$attributeId] : 'option';
    }
}
