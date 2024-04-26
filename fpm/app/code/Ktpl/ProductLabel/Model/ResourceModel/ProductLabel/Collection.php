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

namespace Ktpl\ProductLabel\Model\ResourceModel\ProductLabel;

use Ktpl\ProductLabel\Api\Data\ProductLabelInterface;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected $_idFieldName = ProductLabelInterface::PRODUCTLABEL_ID;

    private $storeIds = [];

    public function getStoreIds()
    {
        return $this->storeIds;
    }

    public function getAllAttributeIds()
    {
        $optionIdsSelect = clone $this->getSelect();
        $optionIdsSelect->reset(\Magento\Framework\DB\Select::ORDER);
        $optionIdsSelect->reset(\Magento\Framework\DB\Select::LIMIT_COUNT);
        $optionIdsSelect->reset(\Magento\Framework\DB\Select::LIMIT_OFFSET);
        $optionIdsSelect->reset(\Magento\Framework\DB\Select::COLUMNS);
        $optionIdsSelect->distinct(true)->columns(ProductLabelInterface::ATTRIBUTE_ID, 'main_table');

        return $this->getConnection()->fetchCol($optionIdsSelect, $this->_bindParams);
    }

    public function getAllOptionIds()
    {
        $optionIdsSelect = clone $this->getSelect();
        $optionIdsSelect->reset(\Magento\Framework\DB\Select::ORDER);
        $optionIdsSelect->reset(\Magento\Framework\DB\Select::LIMIT_COUNT);
        $optionIdsSelect->reset(\Magento\Framework\DB\Select::LIMIT_OFFSET);
        $optionIdsSelect->reset(\Magento\Framework\DB\Select::COLUMNS);
        $optionIdsSelect->distinct(true)->columns(ProductLabelInterface::OPTION_ID, 'main_table');

        return $this->getConnection()->fetchCol($optionIdsSelect, $this->_bindParams);
    }

    public function addAttributeFilter(\Magento\Catalog\Api\Data\ProductAttributeInterface $attribute)
    {
        if ($attribute->getAttributeId()) {
            $this->addFieldToFilter(ProductLabelInterface::ATTRIBUTE_ID, (int) $attribute->getAttributeId());
        }

        return $this;
    }

    public function addAttributeSetIdFilter($attributeSetIds)
    {
        if (!is_array($attributeSetIds)) {
            $attributeSetIds = [$attributeSetIds];
        }

        $this->getSelect()->joinInner(
            ['ea' => $this->getTable('eav_attribute')],
            'ea.attribute_id = main_table.attribute_id'
        )->where('ea.attribute_set_id IN (?)', $attributeSetIds);

        $this->getSelect()->group(ProductLabelInterface::PRODUCTLABEL_ID);

        return $this;
    }

    public function addFieldToFilter($field, $condition = null)
    {
        if ($field === 'store_id') {
            return $this->addStoreFilter($condition);
        }

        return parent::addFieldToFilter($field, $condition);
    }

    public function addStoreFilter($store)
    {
        $defaultStoreId = \Magento\Store\Model\Store::DEFAULT_STORE_ID;

        if ($store instanceof \Magento\Store\Model\Store) {
            $store = [$store->getId()];
        }

        if (!is_array($store)) {
            $store = [$store];
        }

        $store = array_map('intval', $store);

        if (!in_array($defaultStoreId, $store)) {
            $store[] = $defaultStoreId;
        }

        $this->storeIds = $store;
        $this->addFilter('store', ['in' => $store], 'public');

        return $this;
    }

    protected function _construct()
    {
        $this->_init(
            'Ktpl\ProductLabel\Model\ProductLabel',
            'Ktpl\ProductLabel\Model\ResourceModel\ProductLabel'
        );

        /* @see self::_renderFiltersBefore() */
        $this->_map['fields']['store']        = ProductLabelInterface::STORE_TABLE_NAME . '.store_id';
        $this->_map['fields']['attribute_id'] = 'main_table.attribute_id';
    }

    protected function _afterLoad()
    {
        $linkedIds = $this->getColumnValues(ProductLabelInterface::PRODUCTLABEL_ID);

        if (count($linkedIds)) {
            $connection = $this->getConnection();
            $select     = $connection->select()
                ->from($this->getTable(ProductLabelInterface::STORE_TABLE_NAME))
                ->where(ProductLabelInterface::PRODUCTLABEL_ID . ' IN (?)', array_map('intval', $linkedIds));

            $result = $connection->fetchAll($select);

            if ($result) {
                $storesData = [];
                foreach ($result as $storeData) {
                    $storesData[$storeData[ProductLabelInterface::PRODUCTLABEL_ID]][] = $storeData['store_id'];
                }

                foreach ($this as $item) {
                    $linkedId = $item->getData(ProductLabelInterface::PRODUCTLABEL_ID);
                    if (!isset($storesData[$linkedId])) {
                        continue;
                    }

                    $item->setData('store_id', $storesData[$linkedId]);
                }
            }
        }

        return parent::_afterLoad();
    }

    protected function _renderFiltersBefore()
    {
        if ($this->getFilter('store')) {
            $this->getSelect()->join(
                [ProductLabelInterface::STORE_TABLE_NAME => $this->getTable(ProductLabelInterface::STORE_TABLE_NAME)],
                sprintf(
                    'main_table.%s = %s.%s',
                    ProductLabelInterface::PRODUCTLABEL_ID,
                    ProductLabelInterface::STORE_TABLE_NAME,
                    ProductLabelInterface::PRODUCTLABEL_ID
                ),
                []
            )->group(
                'main_table.' . ProductLabelInterface::PRODUCTLABEL_ID
            );
        }

        parent::_renderFiltersBefore();
    }
}
