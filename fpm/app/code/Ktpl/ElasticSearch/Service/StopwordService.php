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

use Ktpl\ElasticSearch\Api\Repository\StopwordRepositoryInterface;
use Ktpl\ElasticSearch\Api\Service\StopwordServiceInterface;

/**
 * Class StopwordService
 *
 * @package Ktpl\ElasticSearch\Service
 */
class StopwordService implements StopwordServiceInterface
{
    /**
     * @var StopwordRepositoryInterface
     */
    private $stopwordRepository;

    /**
     * StopwordService constructor.
     *
     * @param StopwordRepositoryInterface $stopwordRepository
     */
    public function __construct(
        StopwordRepositoryInterface $stopwordRepository
    )
    {
        $this->stopwordRepository = $stopwordRepository;
    }

    /**
     * Check if stopword exists
     *
     * @param string $term
     * @param int $storeId
     * @return bool
     */
    public function isStopword($term, $storeId)
    {
        $collection = $this->stopwordRepository->getCollection()
            ->addFieldToFilter('term', $term)
            ->addFieldToFilter('store_id', [0, $storeId]);

        return $collection->getSize() ? true : false;
    }
}
