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

namespace Ktpl\ElasticSearch\Ui\Validator;

use Magento\Ui\DataProvider\AbstractDataProvider;
use Magento\Framework\Data\CollectionFactory;
use Ktpl\ElasticSearch\Model\Config;

/**
 * Class DataProvider
 *
 * @package Ktpl\ElasticSearch\Ui\Validator
 */
class DataProvider extends AbstractDataProvider
{
    /**
     * @var Config
     */
    private $config;

    /**
     * DataProvider constructor.
     *
     * @param Config $config
     * @param CollectionFactory $collectionFactory
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        Config $config,
        CollectionFactory $collectionFactory,
        $name,
        $primaryFieldName,
        $requestFieldName,
        array $meta = [],
        array $data = []
    )
    {
        $this->config = $config;
        $this->collection = $collectionFactory->create();

        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    /**
     * Add filter
     *
     * @param \Magento\Framework\Api\Filter $filter
     * @return mixed|void|null
     */
    public function addFilter(\Magento\Framework\Api\Filter $filter)
    {
        return null;
    }

    /**
     * Get data
     *
     * @return array
     */
    public function getData()
    {
        $data = [
            'items' => [
                [
                    0,
                    'id_field_name' => 0,
                    'limit' => $this->config->getResultsLimit(),
                    'engine' => $this->config->getEngine()
                ]
            ]
        ];

        return $data;
    }
}
