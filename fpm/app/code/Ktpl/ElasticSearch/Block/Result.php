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

namespace Ktpl\ElasticSearch\Block;

use Magento\Framework\Encryption\UrlCoder;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Search\Model\QueryFactory;
use Ktpl\ElasticSearch\Api\Data\IndexInterface;
use Ktpl\ElasticSearch\Api\Repository\IndexRepositoryInterface;
use Ktpl\ElasticSearch\Api\Service\IndexServiceInterface;
use Ktpl\ElasticSearch\Model\Config;
use Ktpl\ElasticSearch\Model\ResourceModel\Index\CollectionFactory as IndexCollectionFactory;

/**
 * Class Result
 *
 * @package Ktpl\ElasticSearch\Block
 */
class Result extends Template
{
    /**
     * @var IndexCollectionFactory
     */
    protected $indexRepository;

    /**
     * @var IndexServiceInterface
     */
    protected $indexService;

    /**
     * @var QueryFactory
     */
    protected $searchQueryFactory;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var IndexInterface[]
     */
    protected $indices;

    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var UrlCoder
     */
    private $urlCoder;

    /**
     * Result constructor.
     *
     * @param Context $context
     * @param IndexRepositoryInterface $indexRepository
     * @param IndexServiceInterface $indexService
     * @param QueryFactory $queryFactory
     * @param Config $config
     * @param Registry $registry
     * @param UrlCoder $urlCoder
     */
    public function __construct(
        Context $context,
        IndexRepositoryInterface $indexRepository,
        IndexServiceInterface $indexService,
        QueryFactory $queryFactory,
        Config $config,
        Registry $registry,
        UrlCoder $urlCoder
    )
    {
        $this->indexRepository = $indexRepository;
        $this->indexService = $indexService;
        $this->config = $config;
        $this->searchQueryFactory = $queryFactory;
        $this->registry = $registry;
        $this->urlCoder = $urlCoder;

        parent::__construct($context);
    }

    /**
     * Get current content
     *
     * @return string
     */
    public function getCurrentContent()
    {
        $index = $this->getCurrentIndex();
        $html = $this->getContentBlock($index)->toHtml();

        return $html;
    }

    /**
     * Current index model
     *
     * @return IndexInterface
     */
    public function getCurrentIndex()
    {
        $indexId = $this->getRequest()->getParam('index');

        if ($indexId) {
            foreach ($this->getIndices() as $index) {
                if ($index->getId() == $indexId) {
                    return $index;
                }
            }
        }

        return $this->getFirstMatchedIndex();
    }

    /**
     * List of enabled indexes
     *
     * @return IndexInterface[]
     */
    public function getIndices()
    {
        if ($this->indices == null) {
            $result = [];

            $collection = $this->indexRepository->getCollection()
                ->addFieldToFilter(IndexInterface::IS_ACTIVE, 1)
                ->setOrder(IndexInterface::POSITION, 'asc');

            /** @var IndexInterface $index */
            foreach ($collection as $index) {
                $result[] = $this->indexRepository->get($index->getId());
            }

            $this->indices = $result;
        }

        return $this->indices;
    }

    /**
     * First matched index model
     *
     * @return IndexInterface
     */
    public function getFirstMatchedIndex()
    {
        foreach ($this->getIndices() as $index) {
            if (($index->getData('store_id') == false
                || $index->getData('store_id') == $this->getCurrentStore()->getId())
            ) {
                return $index;
            }
        }

        return $this->getIndices()[0];
    }

    /**
     * Get current store
     *
     * @return \Magento\Store\Api\Data\StoreInterface
     */
    public function getCurrentStore()
    {
        return $this->_storeManager->getStore();
    }

    /**
     * Block for index model
     *
     * @param IndexInterface $index
     * @return \Magento\Framework\View\Element\AbstractBlock
     * @throws \Exception
     */
    public function getContentBlock($index)
    {
        /** @var \Magento\Framework\View\Element\Template $block */
        $block = $this->getChildBlock($index->getIdentifier());

        if ($index->getIdentifier() == 'catalogsearch_fulltext') {
            $block = $this->_layout->getBlock('search.result');
        }

        if (!$block) {
            throw new \Exception(__('Child block %1 not exists', $index->getIdentifier()));
        }

        $block->setIndex($index);

        return $block;
    }

    /**
     * Current index size
     *
     * @return int
     */
    public function getCurrentIndexSize()
    {
        return $this->getSearchCollection($this->getCurrentIndex())->getSize();
    }

    /**
     * Get search collection
     *
     * @param IndexInterface $index
     * @return \Magento\Framework\Data\Collection
     */
    public function getSearchCollection(IndexInterface $index)
    {
        return $this->indexService->getSearchCollection($index);
    }

    /**
     * Index url
     *
     * @param \Ktpl\ElasticSearch\Model\Index $index
     * @return string
     */
    public function getIndexUrl($index)
    {
        $query = [
            'index' => $index->getId(),
            'p' => null,
        ];

        if ($index->hasData('store_id')
            && $index->getData('store_id') != $this->getCurrentStore()->getId()
        ) {
            $query['q'] = $this->getRequest()->getParam('q');
            $query['___store'] = $this->getRequest()->getParam('q');
            $query['___from_store'] = $this->getCurrentStore()->getCode();

            $uenc = $this->_storeManager->getStore($index->getData('store_id'))->getUrl('catalogsearch/result',
                ['_query' => $query]);

            return $this->getUrl('stores/store/switch', [
                '_query' => [
                    '___store' => $index->getData('store_code'),
                    '___from_store' => $this->getCurrentStore()->getCode(),
                    'uenc' => $this->urlCoder->encode($uenc),
                ],
            ]);
        }

        $params = [
            '_current' => true,
            '_query' => $query,
        ];

        if ($index->hasData('store_id')) {
            $params['_scope'] = $index->getData('store_id');
        }

        return $this->getUrl('*/*/*', $params);
    }

    /**
     * Get minimum collection size
     *
     * @return int
     */
    public function getMinCollectionSize()
    {
        return (int)Config::MIN_COLLECTION_SIZE;
    }

    /**
     * Save number of results + highlight
     *
     * @param string $html
     * @return string
     */
    protected function _afterToHtml($html)
    {
        $numResults = 0;

        foreach ($this->getIndices() as $index) {
            $numResults += $this->getSearchCollection($index)->getSize();
        }

        $this->registry->register('QueryTotalCount', $numResults, true);

        $this->searchQueryFactory->get()
            ->saveNumResults($numResults);

        return $html;
    }
}
