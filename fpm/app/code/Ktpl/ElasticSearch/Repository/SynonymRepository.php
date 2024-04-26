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

use Magento\Framework\Exception\NoSuchEntityException;
use Ktpl\ElasticSearch\Api\Data\SynonymInterface;
use Ktpl\ElasticSearch\Api\Repository\SynonymRepositoryInterface;
use Ktpl\ElasticSearch\Api\Data\SynonymInterfaceFactory;
use Ktpl\ElasticSearch\Model\ResourceModel\Synonym\CollectionFactory as SynonymCollectionFactory;

/**
 * Class SynonymRepository
 *
 * @package Ktpl\ElasticSearch\Repository
 */
class SynonymRepository implements SynonymRepositoryInterface
{
    /**
     * @var SynonymInterfaceFactory
     */
    private $synonymFactory;

    /**
     * @var SynonymCollectionFactory
     */
    private $synonymCollectionFactory;

    /**
     * SynonymRepository constructor.
     *
     * @param SynonymInterfaceFactory $synonymFactory
     * @param SynonymCollectionFactory $synonymCollectionFactory
     */
    public function __construct(
        SynonymInterfaceFactory $synonymFactory,
        SynonymCollectionFactory $synonymCollectionFactory
    )
    {
        $this->synonymFactory = $synonymFactory;
        $this->synonymCollectionFactory = $synonymCollectionFactory;
    }

    /**
     * Get collection
     *
     * @return SynonymInterface[]|\Ktpl\ElasticSearch\Model\ResourceModel\Synonym\Collection
     */
    public function getCollection()
    {
        return $this->synonymCollectionFactory->create();
    }

    /**
     * Get synonym by id
     *
     * @param int $id
     * @return SynonymInterface|\Ktpl\ElasticSearch\Model\Synonym
     * @throws NoSuchEntityException
     */
    public function get($id)
    {
        /** @var \Ktpl\ElasticSearch\Model\Synonym $synonym */
        $synonym = $this->create();
        $synonym->load($id);

        if (!$synonym->getId()) {
            throw NoSuchEntityException::singleField(SynonymInterface::ID, $id);
        }

        return $synonym;
    }

    /**
     * Create synonym factory instance
     *
     * @return SynonymInterface
     */
    public function create()
    {
        return $this->synonymFactory->create();
    }

    /**
     * Delete synonym
     *
     * @param SynonymInterface $synonym
     * @return $this|SynonymRepositoryInterface
     * @throws \Exception
     */
    public function delete(SynonymInterface $synonym)
    {
        /** @var \Ktpl\ElasticSearch\Model\Synonym $synonym */
        $synonym->delete();

        return $this;
    }

    /**
     * Save synonym
     *
     * @param SynonymInterface $synonym
     * @return $this|SynonymRepositoryInterface
     * @throws \Exception
     */
    public function save(SynonymInterface $synonym)
    {
        /** @var \Ktpl\ElasticSearch\Model\Synonym $synonym */

        $synonyms = $synonym->getSynonyms();
        $synonyms = array_unique(array_filter(explode(',', $synonyms)));

        foreach ($synonyms as $key => $term) {
            $term = trim(strtolower($term));

            if (strlen($term) == 0) {
                throw new \Exception(__('The length of synonym must be greater than 1.'));
            }

            $synonyms[$key] = $term;
        }

        $synonym->setTerm(trim(strtolower($synonym->getTerm())));
        $synonym->setSynonyms(implode(',', $synonyms));
        $synonym->save();

        return $this;
    }
}
