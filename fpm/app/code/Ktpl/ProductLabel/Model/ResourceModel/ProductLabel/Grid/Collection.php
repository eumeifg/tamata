<?php
 /**
  * KrishTechnolabs
  *
  * PHP version 7
  *
  * @category  KrishTechnolabs
  * @package   Ktpl_ProductLabel
  * @author    Kirti Nariya <kirti.nariya@krishtechnolabs.com>
  * @copyright 2019 (c) KrishTechnolabs (https://www.KrishTechnolabs.com/)
  * @license   https://www.krishtechnolabs.com/LICENSE.txt Krish License
  * @link      https://www.krishtechnolabs.com/
  */

namespace Ktpl\ProductLabel\Model\ResourceModel\ProductLabel\Grid;

use \Magento\Framework\Api\Search\SearchResultInterface;

class Collection extends \Ktpl\ProductLabel\Model\ResourceModel\ProductLabel\Collection implements SearchResultInterface
{
    private $aggregations;

    public function setItems(array $items = null)
    {
        return $this;
    }

    public function getAggregations()
    {
        return $this->aggregations;
    }

    public function setAggregations($aggregations)
    {
        $this->aggregations = $aggregations;

        return $this;
    }

    public function getSearchCriteria()
    {
        return null;
    }

    public function setSearchCriteria(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria)
    {
        return $this;
    }


    public function getTotalCount()
    {
        return $this->getSize();
    }

    public function setTotalCount($totalCount)
    {
        return $this;
    }

    protected function _construct()
    {
        parent::_construct();
        $this->setModel('Magento\Framework\View\Element\UiComponent\DataProvider\Document');
    }

    protected function _renderFiltersBefore()
    {
        parent::_renderFiltersBefore();

        $this->getSelect()->joinInner(
            ['ea' => $this->getTable('eav_attribute')],
            'ea.attribute_id = main_table.attribute_id',
            ['frontend_label']
        );

        $this->getSelect()->joinLeft(
            ['eaov' => $this->getTable('eav_attribute_option_value')],
            'eaov.option_id = main_table.option_id',
            ['option_label' => 'value']
        );

        $storeCondition = \Magento\Store\Model\Store::DEFAULT_STORE_ID;

        if ($this->getFilter('store')) {
            $storeId = current($this->getStoreIds());

            $this->getSelect()->joinLeft(
                ['eaov_s' => $this->getTable('eav_attribute_option_value')],
                sprintf('eaov_s.option_id = main_table.option_id AND eaov_s.store_id = %s', $storeId),
                ['option_label' => 'value']
            );

            $storeCondition = $this->getConnection()->getIfNullSql(
                "eaov_s.store_id",
                \Magento\Store\Model\Store::DEFAULT_STORE_ID
            );
        }

        $this->getSelect()->where('eaov.store_id = ?', $storeCondition);

        return $this;
    }
}
