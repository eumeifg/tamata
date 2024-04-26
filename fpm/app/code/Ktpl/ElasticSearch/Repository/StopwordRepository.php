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
use Ktpl\ElasticSearch\Api\Data\StopwordInterface;
use Ktpl\ElasticSearch\Api\Repository\StopwordRepositoryInterface;
use Ktpl\ElasticSearch\Api\Data\StopwordInterfaceFactory;
use Ktpl\ElasticSearch\Model\ResourceModel\Stopword\CollectionFactory as StopwordCollectionFactory;

/**
 * Class StopwordRepository
 *
 * @package Ktpl\ElasticSearch\Repository
 */
class StopwordRepository implements StopwordRepositoryInterface
{
    /**
     * @var StopwordInterfaceFactory
     */
    private $stopwordFactory;

    /**
     * @var StopwordCollectionFactory
     */
    private $stopwordCollectionFactory;

    /**
     * StopwordRepository constructor.
     *
     * @param StopwordInterfaceFactory $stopwordFactory
     * @param StopwordCollectionFactory $stopwordCollectionFactory
     */
    public function __construct(
        StopwordInterfaceFactory $stopwordFactory,
        StopwordCollectionFactory $stopwordCollectionFactory
    )
    {
        $this->stopwordFactory = $stopwordFactory;
        $this->stopwordCollectionFactory = $stopwordCollectionFactory;
    }

    /**
     * Get collection
     *
     * @return StopwordInterface[]|\Ktpl\ElasticSearch\Model\ResourceModel\Stopword\Collection
     */
    public function getCollection()
    {
        return $this->stopwordCollectionFactory->create();
    }

    /**
     * Get Stopword by id
     *
     * @param int $id
     * @return StopwordInterface|\Ktpl\ElasticSearch\Model\Stopword
     * @throws NoSuchEntityExceptionStopword
     */
    public function get($id)
    {
        /** @var \Ktpl\ElasticSearch\Model\Stopword $stopword */
        $stopword = $this->create();
        $stopword->load($id);

        if (!$stopword->getId()) {
            throw NoSuchEntityException::singleField('stopword_id', $id);
        }

        return $stopword;
    }

    /**
     * Create Stopword factory instance
     *
     * @return StopwordInterface
     */
    public function create()
    {
        return $this->stopwordFactory->create();
    }

    /**
     * Delete Stopword
     *
     * @param StopwordInterface $stopword
     * @return $this|StopwordRepositoryInterface
     * @throws \Exception
     */
    public function delete(StopwordInterface $stopword)
    {
        /** @var \Ktpl\ElasticSearch\Model\Stopword $stopword */
        $stopword->delete();

        return $this;
    }

    /**
     * Save Stopword
     *
     * @param StopwordInterface $stopword
     * @return $this|StopwordRepositoryInterface
     * @throws \Exception
     */
    public function save(StopwordInterface $stopword)
    {
        /** @var \Ktpl\ElasticSearch\Model\Stopword $stopword */

        $stopword->setTerm(trim(strtolower($stopword->getTerm())));

        $stopword->save();

        return $this;
    }
}
