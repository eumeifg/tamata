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

namespace Ktpl\ElasticSearch\Index\Ktpl\Gry\Registry;

use Ktpl\ElasticSearch\Model\Index\AbstractIndex;
use Magento\Framework\App\ObjectManager;

/**
 * Class Index
 *
 * @package Ktpl\ElasticSearch\Index\Ktpl\Gry\Registry
 */
class Index extends AbstractIndex
{
    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return 'Ktpl / Gift Registry';
    }

    /**
     * Get identifier
     *
     * @return string
     */
    public function getIdentifier()
    {
        return 'ktpl_gry_registry';
    }

    /**
     * Get attributes
     *
     * @return array
     */
    public function getAttributes()
    {
        return [
            'uid' => __('ID'),
            'name' => __('Name'),
            'description' => __('Description'),
            'location' => __('Event Location'),
            'firstname' => __('Registrant First Name'),
            'lastname' => __('Registrant Last Name'),
            'co_firstname' => __('Co-Registrant First Name'),
            'co_lastname' => __('Co-Registrant Last Name'),
            'email' => __('Registrant email'),
        ];
    }

    /**
     * Get primary key
     *
     * @return string
     */
    public function getPrimaryKey()
    {
        return 'registry_id';
    }

    /**
     * Build Search Collection
     *
     * @return \Ktpl\Giftr\Model\ResourceModel\Registry\Collection|\Magento\Framework\Data\Collection\AbstractDb
     */
    public function buildSearchCollection()
    {
        $collectionFactory = ObjectManager::getInstance()
            ->create('Ktpl\Giftr\Model\ResourceModel\Registry\CollectionFactory');

        /** @var \Ktpl\Giftr\Model\ResourceModel\Registry\Collection $collection */
        $collection = $collectionFactory->create();
        $collection->addWebsiteFilter()
            ->addIsActiveFilter();

        // Check if a search performed by UID
        /** @var \Ktpl\Giftr\Model\ResourceModel\Registry\Collection $uidCollection */
        $uidCollection = $collectionFactory->create();

        $uidCollection->addFieldToFilter('uid', $this->context->getRequest()->getParam('q'));
        if ($uidCollection->getSize()) {
            $collection->addFieldToFilter('uid', $this->context->getRequest()->getParam('q'));
        } else {
            // Otherwise search only within pulic registries
            $collection->addFieldToFilter('is_public', 1);
        }

        $this->context->getSearcher()->joinMatches($collection, 'main_table.registry_id');

        return $collection;
    }

    /**
     * Get Searchable Entities
     *
     * @param int $storeId
     * @param null $entityIds
     * @param null $lastEntityId
     * @param int $limit
     * @return array|\Ktpl\Giftr\Model\ResourceModel\Registry\Collection|\Magento\Framework\Data\Collection\AbstractDb
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getSearchableEntities($storeId, $entityIds = null, $lastEntityId = null, $limit = 100)
    {
        $websiteId = $this->context->getStoreManager()->getStore($storeId)->getWebsiteId();
        $collectionFactory = $this->context->getObjectManager()
            ->create('Ktpl\Giftr\Model\ResourceModel\Registry\CollectionFactory');

        /** @var \Ktpl\Giftr\Model\ResourceModel\Registry\Collection $collection */
        $collection = $collectionFactory->create();
        $collection->addFieldToFilter('main_table.website_id', $websiteId)
            ->addIsActiveFilter();

        if ($entityIds) {
            $collection->addFieldToFilter('main_table.registry_id', ['in' => $entityIds]);
        }

        $collection->addFieldToFilter('main_table.registry_id', ['gt' => $lastEntityId])
            ->setPageSize($limit)
            ->setOrder('main_table.registry_id');

        return $collection;
    }
}
