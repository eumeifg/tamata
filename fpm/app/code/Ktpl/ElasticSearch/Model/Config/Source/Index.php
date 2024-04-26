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

namespace Ktpl\ElasticSearch\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;
use Ktpl\ElasticSearch\Api\Data\IndexInterface;
use Ktpl\ElasticSearch\Api\Repository\IndexRepositoryInterface;

/**
 * Class Index
 *
 * @package Ktpl\ElasticSearch\Model\Config\Source
 */
class Index implements ArrayInterface
{
    /**
     * @var IndexRepositoryInterface
     */
    private $indexRepository;

    /**
     * Index constructor.
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
     * Get index options
     *
     * @param bool $onlyUnused
     * @return array
     */
    public function toOptionArray($onlyUnused = false)
    {
        $options = [];
        foreach ($this->indexRepository->getList() as $instance) {
            $identifier = $instance->getIdentifier();
            if (!$onlyUnused
                || !$this->indexRepository->getCollection()
                    ->getItemByColumnValue(IndexInterface::IDENTIFIER, $identifier)
            ) {
                $options[] = [
                    'label' => (string)$instance,
                    'value' => $identifier,
                ];
            }
        }

        return $options;
    }
}
