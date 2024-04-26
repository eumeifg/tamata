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

namespace Ktpl\ElasticSearch\Index\Amasty\Faq\Question;

use Ktpl\ElasticSearch\Model\Index\AbstractIndex;

/**
 * Class Index
 *
 * @package Ktpl\ElasticSearch\Index\Amasty\Faq\Question
 */
class Index extends AbstractIndex
{
    /**
     * Get name
     *
     * return string
     */
    public function getName()
    {
        return 'Amasty / FAQ';
    }

    /**
     * Get identifier
     *
     * return string
     */
    public function getIdentifier()
    {
        return 'amasty_faq_question';
    }

    /**
     * Get attributes
     *
     * return array
     */
    public function getAttributes()
    {
        return [
            'title' => __('Title'),
            'short_answer' => __('Short Answer'),
            'answer' => __('Full Answer'),
        ];
    }

    /**
     * Get primary key
     *
     * return string
     */
    public function getPrimaryKey()
    {
        return 'question_id';
    }

    /**
     * Build search collection
     *
     * @return \Amasty\Faq\Model\ResourceModel\Question\Collection|\Magento\Framework\Data\Collection\AbstractDb
     */
    public function buildSearchCollection()
    {
        $collectionFactory = $this->context->getObjectManager()
            ->create('Amasty\Faq\Model\ResourceModel\Question\CollectionFactory');

        /** @var \Amasty\Faq\Model\ResourceModel\Question\Collection $collection */
        $collection = $collectionFactory->create()
            ->addFieldToFilter('status', 1);

        $this->context->getSearcher()->joinMatches($collection, 'main_table.question_id');

        return $collection;
    }

    /**
     * Get searchable entities.
     *
     * @param int $storeId
     * @param null $entityIds
     * @param null $lastEntityId
     * @param int $limit
     * @return \Amasty\Faq\Model\ResourceModel\Question\Collection|array|\Magento\Framework\Data\Collection\AbstractDb
     */
    public function getSearchableEntities($storeId, $entityIds = null, $lastEntityId = null, $limit = 100)
    {
        $collectionFactory = $this->context->getObjectManager()
            ->create('Amasty\Faq\Model\ResourceModel\Question\CollectionFactory');

        /** @var \Amasty\Faq\Model\ResourceModel\Question\Collection $collection */
        $collection = $collectionFactory->create();

        $collection->addStoreFilter([0, $storeId]);

        if ($entityIds) {
            $collection->addFieldToFilter('question_id', ['in' => $entityIds]);
        }

        $collection->addFieldToFilter('main_table.question_id', ['gt' => $lastEntityId])
            ->setPageSize($limit)
            ->setOrder('question_id');

        return $collection;
    }
}
