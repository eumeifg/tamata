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

namespace Ktpl\ElasticSearch\Service;

use Ktpl\ElasticSearch\Api\Data\SynonymInterface;
use Ktpl\ElasticSearch\Api\Repository\SynonymRepositoryInterface;
use Ktpl\ElasticSearch\Api\Service\SynonymServiceInterface;

/**
 * Class SynonymService
 *
 * @package Ktpl\ElasticSearch\Service
 */
class SynonymService implements SynonymServiceInterface
{
    /**
     * @var SynonymRepository Collection
     */
    private static $complexSynonyms;

    /**
     * @var SynonymRepositoryInterface
     */
    private $synonymRepository;

    /**
     * SynonymService constructor.
     *
     * @param SynonymRepositoryInterface $synonymRepository
     */
    public function __construct(
        SynonymRepositoryInterface $synonymRepository
    )
    {
        $this->synonymRepository = $synonymRepository;
    }

    /**
     * Get synonyms
     *
     * @param array $terms
     * @param int $storeId
     * @return array
     */
    public function getSynonyms(array $terms, $storeId)
    {
        $result = [];

        $collection = $this->synonymRepository->getCollection();

        foreach ($terms as $term) {
            $collection->getSelect()
                ->orWhere('term = ?', $term);
        }

        /** @var SynonymInterface $model */
        foreach ($collection as $model) {
            $synonyms = explode(',', $model->getSynonyms());

            foreach ($terms as $term) {
                if ($model->getTerm() === $term) {
                    $result[$term] = $synonyms;
                }
            }
        }

        return $result;
    }

    /**
     * Get complex synonyms
     *
     * @param $storeId
     * @return SynonymInterface[]|\Ktpl\ElasticSearch\Model\ResourceModel\Synonym\Collection|SynonymRepository
     */
    public function getComplexSynonyms($storeId)
    {
        if (!self::$complexSynonyms) {
            self::$complexSynonyms = $this->synonymRepository->getCollection();

            self::$complexSynonyms->getSelect()
                ->where("term like '% %'");
        }

        return self::$complexSynonyms;
    }
}
