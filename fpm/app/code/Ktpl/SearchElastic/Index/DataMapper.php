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

namespace Ktpl\SearchElastic\Index;

use Ktpl\ElasticSearch\Api\Data\Index\DataMapperInterface;
use Ktpl\ElasticSearch\Api\Repository\IndexRepositoryInterface;

/**
 * Class DataMapper
 *
 * @package Ktpl\SearchElastic\Index
 */
class DataMapper implements DataMapperInterface
{
    /**
     * @var IndexRepositoryInterface
     */
    private $indexRepository;

    /**
     * DataMapper constructor.
     *
     * @param IndexRepositoryInterface $indexRepository
     */
    public function __construct(
        IndexRepositoryInterface $indexRepository
    )
    {
        $this->indexRepository = $indexRepository;
    }

    /**
     * Map data
     *
     * @param array $documents
     * @param \Magento\Framework\Search\Request\Dimension[] $dimensions
     * @param string $indexIdentifier
     * @return array
     */
    public function map(array $documents, $dimensions, $indexIdentifier)
    {
        $instance = $this->indexRepository->getInstance($indexIdentifier);

        foreach ($documents as $id => $doc) {
            $map = [
                'id' => $id,
                $instance->getPrimaryKey() => $id,
            ];
            foreach ($doc as $attribute => $value) {
                if (is_int($attribute)) {
                    $attribute = $instance->getAttributeCode($attribute);
                }
                if (isset($map[$attribute])) {
                    $map[$attribute] .= ' ' . $value;
                } else {
                    $map[$attribute] = $value;
                }
            }

            $documents[$id] = $map;
        }

        return $documents;
    }
}
