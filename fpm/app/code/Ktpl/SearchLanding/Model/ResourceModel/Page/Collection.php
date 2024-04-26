<?php
/**
 * KrishTechnolabs
 *
 * PHP version 7
 *
 * @category  KrishTechnolabs
 * @package   Ktpl_SearchLanding
 * @author    Dhara Bhatti <dhara.bhatti@krishtechnolabs.com>
 * @copyright 2019 (c) KrishTechnolabs (https://www.KrishTechnolabs.com/)
 * @license   https://www.krishtechnolabs.com/LICENSE.txt Krish License
 * @link      https://www.krishtechnolabs.com/
 */

namespace Ktpl\SearchLanding\Model\ResourceModel\Page;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Class Collection
 *
 * @package Ktpl\SearchLanding\Model\ResourceModel\Page
 */
class Collection extends AbstractCollection
{
    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init('Ktpl\SearchLanding\Model\Page', 'Ktpl\SearchLanding\Model\ResourceModel\Page');
    }

    /**
     * Add store filter
     *
     * @param $storeId
     * @return $this
     */
    public function addStoreFilter($storeId)
    {
        $id = intval($storeId);

        $this->getSelect()->where('(FIND_IN_SET (' . $id . ', main_table.store_ids)
            OR FIND_IN_SET (0, main_table.store_ids))');

        return $this;
    }
}
