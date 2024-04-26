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

namespace Ktpl\ElasticSearch\Index\External\Wordpress\Post;

use Ktpl\ElasticSearch\Model\Index\AbstractIndex;
use Ktpl\ElasticSearch\Model\Index\Context;
use Ktpl\ElasticSearch\Index\External\Wordpress\Post\CollectionFactory as PostCollectionFactory;

/**
 * Class Index
 *
 * @package Ktpl\ElasticSearch\Index\External\Wordpress\Post
 */
class Index extends AbstractIndex
{
    /**
     * @var CollectionFactory
     */
    private $postCollectionFactory;

    /**
     * Index constructor.
     *
     * @param CollectionFactory $postCollectionFactory
     * @param Context $context
     * @param array $dataMappers
     */
    public function __construct(
        PostCollectionFactory $postCollectionFactory,
        Context $context,
        $dataMappers
    )
    {
        $this->postCollectionFactory = $postCollectionFactory;

        parent::__construct($context, $dataMappers);
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return 'External / Wordpress Blog';
    }

    /**
     * Get identifier
     *
     * @return string
     */
    public function getIdentifier()
    {
        return 'external_wordpress_post';
    }

    /**
     * Get attribute id
     *
     * @param string $attributeCode
     * @return false|int|string
     */
    public function getAttributeId($attributeCode)
    {
        $attributes = array_keys($this->getAttributes());

        return array_search($attributeCode, $attributes);
    }

    /**
     * Get attributes
     *
     * @return array
     */
    public function getAttributes()
    {
        return [
            'post_title' => __('Post Title'),
            'post_content' => __('Post Content'),
            'post_excerpt' => __('Post Excerpt'),
        ];
    }

    /**
     * Get primary key
     *
     * @return string
     */
    public function getPrimaryKey()
    {
        return 'ID';
    }

    /**
     * Build Search Collection
     *
     * @return Collection|\Magento\Framework\Data\Collection\AbstractDb
     */
    public function buildSearchCollection()
    {
        $collection = $this->postCollectionFactory->create(['index' => $this]);
        $this->context->getSearcher()->joinMatches($collection, 'ID');
        return $collection;
    }

    /**
     * Get Searchable Entities
     *
     * @param int $storeId
     * @param null $entityIds
     * @param null $lastEntityId
     * @param int $limit
     * @return array|Collection|\Magento\Framework\Data\Collection\AbstractDb
     */
    public function getSearchableEntities($storeId, $entityIds = null, $lastEntityId = null, $limit = 100)
    {
        $collection = $this->postCollectionFactory->create(['index' => $this]);

        if ($entityIds) {
            $collection->addFieldToFilter('ID', ['in' => $entityIds]);
        }

        $collection->addFieldToFilter('ID', ['gt' => $lastEntityId])
            ->setPageSize($limit)
            ->setOrder('ID');

        return $collection;
    }

    /**
     * Return new connection to wordpress database
     *
     * @return \Magento\Framework\DB\Adapter\AdapterInterface
     */
    public function getConnection()
    {
        if ($this->getModel()->getProperty('db_connection_name')) {
            $connectionName = $this->getModel()->getProperty('db_connection_name');

            $connection = $this->context->getResourceConnection()->getConnection($connectionName);

            return $connection;
        }

        return $this->context->getResourceConnection()->getConnection();
    }
}
