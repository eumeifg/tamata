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

use Magento\Framework\App\RequestInterface;
use Magento\Framework\Option\ArrayInterface;
use Ktpl\ElasticSearch\Api\Data\IndexInterface;
use Ktpl\ElasticSearch\Api\Repository\IndexRepositoryInterface;
use Ktpl\ElasticSearch\Model\Config;

/**
 * Class IndexTree
 *
 * @package Ktpl\ElasticSearch\Model\Config\Source
 */
class IndexTree implements ArrayInterface
{
    /**
     * @var IndexRepositoryInterface
     */
    private $indexRepository;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * IndexTree constructor.
     *
     * @param IndexRepositoryInterface $indexRepository
     * @param RequestInterface $request
     */
    public function __construct(
        IndexRepositoryInterface $indexRepository,
        RequestInterface $request
    ) {
        $this->indexRepository = $indexRepository;
        $this->request         = $request;
    }

    /**
     * Get index tree options
     * @return array
     */
    public function toOptionArray()
    {
        $options = [];

        $identifiers = $this->indexRepository->getCollection()
            ->getColumnValues('identifier');

        $current = $this->indexRepository->get($this->request->getParam(IndexInterface::ID));

        foreach ($this->indexRepository->getList() as $instance) {
            if (in_array($instance->getIdentifier(), $identifiers)
                && in_array($instance->getIdentifier(), Config::DISALLOWED_MULTIPLE)
                && (!$current || $current->getIdentifier() != $instance->getIdentifier())) {
                continue;
            }

            $identifier = $instance->getIdentifier();
            $group      = trim(explode('/', $instance->getName())[0]);
            $name       = trim(explode('/', $instance->getName())[1]);

            if (!isset($options[$group])) {
                $options[$group] = [
                    'label'    => $group,
                    'value'    => $group,
                    'optgroup' => [],
                ];
            }

            $options[$group]['optgroup'][] = [
                'label' => (string)$name,
                'value' => $identifier,
            ];
        }

        return array_values($options);
    }
}
