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

namespace Ktpl\ElasticSearch\Ui\Synonym\Form;

use Magento\Ui\DataProvider\AbstractDataProvider;
use Ktpl\ElasticSearch\Api\Data\SynonymInterface;
use Ktpl\ElasticSearch\Api\Repository\SynonymRepositoryInterface;

/**
 * Class DataProvider
 *
 * @package Ktpl\ElasticSearch\Ui\Synonym\Form
 */
class DataProvider extends AbstractDataProvider
{
    /**
     * @var SynonymRepositoryInterface
     */
    private $synonymRepository;

    /**
     * DataProvider constructor.
     *
     * @param SynonymRepositoryInterface $repository
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        SynonymRepositoryInterface $repository,
        $name,
        $primaryFieldName,
        $requestFieldName,
        array $meta = [],
        array $data = []
    )
    {
        $this->synonymRepository = $repository;
        $this->collection = $this->synonymRepository->getCollection();

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

        foreach ($this->synonymRepository->getCollection() as $synonym) {
            $data = [
                SynonymInterface::ID => $synonym->getId(),
                SynonymInterface::TERM => $synonym->getTerm(),
                SynonymInterface::SYNONYMS => $synonym->getSynonyms(),
                SynonymInterface::STORE_ID => $synonym->getStoreId(),
            ];
            $result[$synonym->getId()] = $data;
        }

        return $result;
    }
}
