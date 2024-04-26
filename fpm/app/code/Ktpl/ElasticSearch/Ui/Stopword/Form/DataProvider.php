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

namespace Ktpl\ElasticSearch\Ui\Stopword\Form;

use Magento\Ui\DataProvider\AbstractDataProvider;
use Ktpl\ElasticSearch\Api\Data\StopwordInterface;
use Ktpl\ElasticSearch\Api\Repository\StopwordRepositoryInterface;

/**
 * Class DataProvider
 *
 * @package Ktpl\ElasticSearch\Ui\Stopword\Form
 */
class DataProvider extends AbstractDataProvider
{
    /**
     * @var StopwordRepositoryInterface
     */
    private $stopwordRepository;

    /**
     * DataProvider constructor.
     *
     * @param StopwordRepositoryInterface $repository
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        StopwordRepositoryInterface $repository,
        $name,
        $primaryFieldName,
        $requestFieldName,
        array $meta = [],
        array $data = []
    )
    {
        $this->stopwordRepository = $repository;
        $this->collection = $this->stopwordRepository->getCollection();

        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    /**
     * Get data
     *
     * @return array
     */
    public function getData()
    {
        $result = [];

        foreach ($this->stopwordRepository->getCollection() as $stopword) {
            $data = [
                StopwordInterface::ID => $stopword->getId(),
                StopwordInterface::TERM => $stopword->getTerm(),
                StopwordInterface::STORE_ID => $stopword->getStoreId(),
            ];
            $result[$stopword->getId()] = $data;
        }

        return $result;
    }
}
