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

namespace Ktpl\ElasticSearch\Controller\Adminhtml\Validator;

use Ktpl\ElasticSearch\Controller\Adminhtml\Validator;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Backend\App\Action\Context;
use Magento\Search\Model\SearchEngine;
use Magento\CatalogSearch\Model\Advanced\Request\BuilderFactory as RequestBuilderFactory;
use Magento\Framework\App\ScopeResolverInterface;

/**
 * Class ValidateSearchSpeed
 *
 * @package Ktpl\ElasticSearch\Controller\Adminhtml\Validator
 */
class ValidateSearchSpeed extends Validator
{

    /**
     * @var JsonFactory
     */
    private $resultJsonFactory;

    /**
     * @var SearchEngine
     */
    protected $searchEngine;

    /**
     * @var RequestBuilderFactory
     */
    protected $requestBuilderFactory;

    /**
     * @var ScopeResolverInterface
     */
    protected $scopeResolver;

    /**
     * ValidateSearchSpeed constructor.
     *
     * @param Context $context
     * @param JsonFactory $resultJsonFactory
     * @param SearchEngine $searchEngine
     * @param RequestBuilderFactory $requestBuilderFactory
     * @param ScopeResolverInterface $scopeResolver
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        SearchEngine $searchEngine,
        RequestBuilderFactory $requestBuilderFactory,
        ScopeResolverInterface $scopeResolver
    )
    {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->searchEngine = $searchEngine;
        $this->requestBuilderFactory = $requestBuilderFactory;
        $this->scopeResolver = $scopeResolver;

        parent::__construct($context);
    }

    /**
     * {@inheritdoc}
     *
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Json|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $response = $this->resultJsonFactory->create();
        if ($this->getRequest()->getParam('q') && !empty($this->getRequest()->getParam('q'))) {
            $query = $this->getRequest()->getParam('q');
        } else {
            $status = self::STATUS_ERROR;
            $result = '<p>Please specify search term</p>';
            return $response->setData(['result' => $result, 'status' => $status]);
        }

        $start = microtime(true);

        $requestBuilder = $this->requestBuilderFactory->create();
        $requestBuilder->bind('search_term', $query);
        $requestBuilder->bindDimension('scope', $this->scopeResolver->getScope());
        $requestBuilder->setRequestName('catalogsearch_fulltext');

        $queryRequest = $requestBuilder->create();
        $collection = $this->searchEngine->search($queryRequest);

        $ids = [];
        foreach ($collection->getIterator() as $item) {
            $ids[] = $item->getId();
        }

        $result = 'Total: ' . count($ids) . ' results in ' . round(microtime(true) - $start, 4);

        return $response->setData(['result' => $result . ' sec']);
    }
}
