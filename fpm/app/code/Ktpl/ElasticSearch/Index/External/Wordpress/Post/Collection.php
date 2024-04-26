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

use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Data\Collection\EntityFactoryInterface;
use Magento\Framework\Event\ManagerInterface as EventManagerInterface;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Psr\Log\LoggerInterface;

/**
 * Class Collection
 *
 * @package Ktpl\ElasticSearch\Index\External\Wordpress\Post
 */
class Collection extends AbstractCollection
{
    /**
     * Constructor
     *
     * @param EntityFactoryInterface $entityFactory
     * @param LoggerInterface $logger
     * @param FetchStrategyInterface $fetchStrategy
     * @param EventManagerInterface $eventManager
     * @param null $index
     */
    public function __construct(
        EntityFactoryInterface $entityFactory,
        LoggerInterface $logger,
        FetchStrategyInterface $fetchStrategy,
        EventManagerInterface $eventManager,
        $index = null
    )
    {
        /** @var \Ktpl\ElasticSearch\Index\External\Wordpress\Post\Index $index */
        $this->index = $index;

        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager);

        $this->setConnection($this->index->getConnection());
        $this->setModel('\Ktpl\ElasticSearch\Index\External\Wordpress\Post\Item');
        $this->_initSelect();
    }

    /**
     * Initialize select
     *
     * @return $this|AbstractCollection|void
     */
    protected function _initSelect()
    {
        parent::_initSelect();

        $this->getSelect()
            ->where('main_table.post_status=?', 'publish');

        return $this;
    }

    /**
     * Get main table name
     *
     * @return string
     */
    public function getMainTable()
    {
        return $this->index->getModel()->getProperty('db_table_prefix') . 'posts';
    }

    /**
     * Get resource model name
     *
     * @return string
     */
    public function getResourceModelName()
    {
        return 'Ktpl\ElasticSearch\Model\ResourceModel\Index';
    }

    /**
     * Ser index
     *
     * @return AbstractCollection
     */
    protected function _afterLoad()
    {
        foreach ($this->_items as $item) {
            $item->setIndex($this->index);
        }

        return parent::_afterLoad();
    }
}
