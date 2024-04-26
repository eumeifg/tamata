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

namespace Ktpl\ElasticSearch\Plugin\Manadev\ProductCollection\Contracts\FilterResource;

use Magento\Framework\DB\Select;
use Magento\Framework\Search\Adapter\Mysql\TemporaryStorage;

if (class_exists('Manadev\ProductCollection\Contracts\FilterResource')) {
    require_once('ParentFilterExtends.php');
} else {
    require_once('ParentFilterEmpty.php');
}

/**
 * Class ApplyPlugin
 *
 * @package Ktpl\ElasticSearch\Plugin\Manadev\ProductCollection\Contracts\FilterResource
 */
class ApplyPlugin extends ParentFilter
{
    /**
     * {@inheritdoc}
     */
    public function _construct()
    {

    }

    /**
     * Store api documents
     *
     * @param Select $select
     * @param \Manadev\ProductCollection\Contracts\Filter $filter
     * @param $callback
     * @throws \Zend_Db_Exception
     */
    public function apply(Select $select, \Manadev\ProductCollection\Contracts\Filter $filter, $callback)
    {
        $om = \Magento\Framework\App\ObjectManager::getInstance();

        /** @var \Manadev\ProductCollection\Factory $factory */
        $factory = $om->create('Manadev\ProductCollection\Factory');
        /** @var \Magento\Search\Model\AdapterFactory */
        $adapter = $om->create('Magento\Search\Model\AdapterFactory')->create();

        /** @var \Magento\Framework\Search\Adapter\Mysql\TemporaryStorage $storage */
        $storage = $om->create('Magento\Framework\Search\Adapter\Mysql\TemporaryStorage');

        $requestBuilder = $factory->createRequestBuilder();
        $requestBuilder->bindDimension('scope', $this->getStoreId());
        $requestBuilder->bind('search_term', $filter->getText());
        $requestBuilder->setRequestName('quick_search_container');
        $request = $requestBuilder->create();

        $response = $adapter->query($request);

        $table = $storage->storeApiDocuments($response);

        $select->joinInner(['search_result' => $table->getName()],
            'e.entity_id = search_result.' . TemporaryStorage::FIELD_ENTITY_ID, []);
    }
}