<?php

namespace CAT\Custom\Controller;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Response\Http as HttpResponse;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\GraphQl\Exception\ExceptionFormatter;
use Magento\Framework\GraphQl\Query\Fields as QueryFields;
use Magento\Framework\GraphQl\Query\QueryProcessor;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Schema\SchemaGeneratorInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\Webapi\Response;
use Magento\GraphQl\Controller\HttpRequestProcessor;
use Magento\GraphQl\Model\Query\ContextFactoryInterface;

class GraphQl extends \Magento\GraphQl\Controller\GraphQl
{
    /**
     * @var \Magento\Framework\Webapi\Response
     * @deprecated 100.3.2
     */
    private $response;

    /**
     * @var SchemaGeneratorInterface
     */
    private $schemaGenerator;

    /**
     * @var SerializerInterface
     */
    private $jsonSerializer;

    /**
     * @var QueryProcessor
     */
    private $queryProcessor;

    /**
     * @var ExceptionFormatter
     */
    private $graphQlError;

    /**
     * @var ContextInterface
     * @deprecated 100.3.3 $contextFactory is used for creating Context object
     */
    private $resolverContext;

    /**
     * @var HttpRequestProcessor
     */
    private $requestProcessor;

    /**
     * @var QueryFields
     */
    private $queryFields;

    /**
     * @var JsonFactory
     */
    private $jsonFactory;

    /**
     * @var HttpResponse
     */
    private $httpResponse;

    /**
     * @var ContextFactoryInterface
     */
    private $contextFactory;

    /**
     * @param Response $response
     * @param SchemaGeneratorInterface $schemaGenerator
     * @param SerializerInterface $jsonSerializer
     * @param QueryProcessor $queryProcessor
     * @param ExceptionFormatter $graphQlError
     * @param ContextInterface $resolverContext
     * @param HttpRequestProcessor $requestProcessor
     * @param QueryFields $queryFields
     * @param JsonFactory|null $jsonFactory
     * @param HttpResponse|null $httpResponse
     * @param ContextFactoryInterface|null $contextFactory
     */
    public function __construct(
        Response $response,
        SchemaGeneratorInterface $schemaGenerator,
        SerializerInterface $jsonSerializer,
        QueryProcessor $queryProcessor,
        ExceptionFormatter $graphQlError,
        ContextInterface $resolverContext,
        HttpRequestProcessor $requestProcessor,
        QueryFields $queryFields,
        JsonFactory $jsonFactory = null,
        HttpResponse $httpResponse = null,
        ContextFactoryInterface $contextFactory = null
    ) {
        parent::__construct($response, $schemaGenerator, $jsonSerializer, $queryProcessor, $graphQlError, $resolverContext, $requestProcessor, $queryFields, $jsonFactory, $httpResponse, $contextFactory);
        $this->response = $response;
        $this->schemaGenerator = $schemaGenerator;
        $this->jsonSerializer = $jsonSerializer;
        $this->queryProcessor = $queryProcessor;
        $this->graphQlError = $graphQlError;
        $this->resolverContext = $resolverContext;
        $this->requestProcessor = $requestProcessor;
        $this->queryFields = $queryFields;
        $this->jsonFactory = $jsonFactory ?: ObjectManager::getInstance()->get(JsonFactory::class);
        $this->httpResponse = $httpResponse ?: ObjectManager::getInstance()->get(HttpResponse::class);
        $this->contextFactory = $contextFactory ?: ObjectManager::getInstance()->get(ContextFactoryInterface::class);
    }

    /**
     * Handle GraphQL request
     *
     * @param RequestInterface $request
     * @return ResponseInterface
     * @since 100.3.0
     */
    public function dispatch(RequestInterface $request) : ResponseInterface
    {
        $statusCode = 200;
        $jsonResult = $this->jsonFactory->create();
        try {
            /** @var Http $request */
            $this->requestProcessor->validateRequest($request);

            $data = $this->getDataFromRequest($request);

            if (function_exists('newrelic_name_transaction')) {
                newrelic_name_transaction('GraphQL-' . $this->getOperationNameFromData($data));
            }

            $query = $data['query'] ?? '';
            $variables = $data['variables'] ?? null;

            // We must extract queried field names to avoid instantiation of unnecessary fields in webonyx schema
            // Temporal coupling is required for performance optimization
            $this->queryFields->setQuery($query, $variables);
            $schema = $this->schemaGenerator->generate();

            $result = $this->queryProcessor->process(
                $schema,
                $query,
                $this->contextFactory->create(),
                $data['variables'] ?? []
            );
        } catch (\Exception $error) {
            $result['errors'] = isset($result) && isset($result['errors']) ? $result['errors'] : [];
            $result['errors'][] = $this->graphQlError->create($error);
            $statusCode = ExceptionFormatter::HTTP_GRAPH_QL_SCHEMA_ERROR_STATUS;
        }

        $jsonResult->setHttpResponseCode($statusCode);
        $jsonResult->setData($result);
        $jsonResult->renderResult($this->httpResponse);
        return $this->httpResponse;
    }

    /**
     * Get data from request body or query string
     *
     * @param RequestInterface $request
     * @return array
     */
    private function getDataFromRequest(RequestInterface $request) : array
    {
        /** @var Http $request */
        if ($request->isPost()) {
            $data = $this->jsonSerializer->unserialize($request->getContent());
        } elseif ($request->isGet()) {
            $data = $request->getParams();
            $data['variables'] = isset($data['variables']) ?
                $this->jsonSerializer->unserialize($data['variables']) : null;
            $data['variables'] = is_array($data['variables']) ?
                $data['variables'] : null;
        } else {
            return [];
        }

        return $data;
    }

    /**
     * @param array $data
     * @return false|mixed|string
     */
    private function getOperationNameFromData($data) {
        $operationName = 'operationNameNotSet';

        try {
            if (!(is_array($data) && isset($data['query']) && is_string($data['query']) && strlen($data['query']))) {
                return $operationName;
            }

            $string = str_replace([' ', '\n', PHP_EOL], '', $data['query']);
            preg_match('/([^\W]+)/', $string, $matches, PREG_OFFSET_CAPTURE);

            $operationName = 'operationNameNotFound';
            if (is_array($matches) && isset($matches[0][0]) && is_string($matches[0][0])) {
                $operationName = $matches[0][0];
            }
            if (stripos($operationName, 'query') === 0) {
                $operationName = substr($operationName, 5); // Remove query prefix
            }
            if (stripos($operationName, 'mutation') === 0) {
                $operationName = substr($operationName, 8); // Remove mutation prefix
            }
        } catch (\Error $error) {
            $operationName = 'operationNameResolutionError';
        }

        return $operationName;
    }
}
