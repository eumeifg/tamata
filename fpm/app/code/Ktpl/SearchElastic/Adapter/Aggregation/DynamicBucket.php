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

namespace Ktpl\SearchElastic\Adapter\Aggregation;

use Magento\Framework\Search\Dynamic\DataProviderInterface;
use Magento\Framework\Search\Dynamic\Algorithm\Repository;
use Magento\Framework\Search\Dynamic\EntityStorage;
use Magento\Framework\Search\Dynamic\EntityStorageFactory;
use Magento\Framework\Search\Request\BucketInterface as RequestBucketInterface;

/**
 * Class DynamicBucket
 *
 * @package Ktpl\SearchElastic\Adapter\Aggregation
 */
class DynamicBucket
{
    /**
     * @var Repository
     */
    private $algorithmRepository;

    /**
     * @var EntityStorageFactory
     */
    private $entityStorageFactory;

    /**
     * DynamicBucket constructor.
     *
     * @param Repository $algorithmRepository
     * @param EntityStorageFactory $entityStorageFactory
     */
    public function __construct(Repository $algorithmRepository, EntityStorageFactory $entityStorageFactory)
    {
        $this->algorithmRepository = $algorithmRepository;
        $this->entityStorageFactory = $entityStorageFactory;
    }

    /**
     * Buid dynamic bucket
     *
     * @param RequestBucketInterface $bucket
     * @param array $dimensions
     * @param array $queryResult
     * @param DataProviderInterface $dataProvider
     * @return array
     */
    public function build(
        RequestBucketInterface $bucket,
        array $dimensions,
        array $queryResult,
        DataProviderInterface $dataProvider
    )
    {
        /** @var DynamicBucket $bucket */
        $algorithm = $this->algorithmRepository->get('auto', ['dataProvider' => $dataProvider]);

        $data = $algorithm->getItems($bucket, $dimensions, $this->getEntityStorage($queryResult));
        $resultData = $this->prepareData($data);

        return $resultData;
    }

    /**
     * Get entity storage
     *
     * @param array $queryResult
     * @return EntityStorage
     */
    private function getEntityStorage(array $queryResult)
    {
        $ids = [];
        foreach ($queryResult['hits']['hits'] as $document) {
            $ids[] = $document['_id'];
        }

        return $this->entityStorageFactory->create($ids);
    }

    /**
     * Prepare data
     *
     * @param array $data
     * @return array
     */
    private function prepareData($data)
    {
        $resultData = [];
        foreach ($data as $value) {
            $from = is_numeric($value['from']) ? $value['from'] : '*';
            $to = is_numeric($value['to']) ? $value['to'] : '*';
            unset($value['from'], $value['to']);

            $rangeName = "{$from}_{$to}";
            $resultData[$rangeName] = array_merge(['value' => $rangeName], $value);
        }

        return $resultData;
    }
}
