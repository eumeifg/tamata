<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Catalog
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Catalog\Model\ResourceModel;

class Product extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    protected $setSPNullFlag = 0;

    protected function _construct()
    {
        $this->_init('md_vendor_product', 'vendor_product_id');
    }

    protected function _beforeSave(\Magento\Framework\Model\AbstractModel $object)
    {
        if (!$object->getData('special_price')) {
            $this->setSPNullFlag = 1;
        } else {
            $this->setSPNullFlag = 0;
        }
        parent::_beforeSave($object);
    }

    protected function _afterSave(\Magento\Framework\Model\AbstractModel $object)
    {
        return parent::_afterSave($object);
    }

    public function getByOriginProductId($mainProdId, $vendorId, $storeId = 0)
    {
        $connection = $this->getConnection();

        $select = $connection->select()
                ->from($this->getMainTable())
                ->where('marketplace_product_id = :marketplace_product_id')
                ->where('vendor_id = :vendor_id');
        $select->joinLeft(
            ['rbvps' => $this->getTable('md_vendor_product_store')],
            "md_vendor_product.vendor_product_id = rbvps.vendor_product_id"
        )->where('rbvps.store_id = :store_id');

        $bind = [
            ':marketplace_product_id' => (int)$mainProdId,
            ':vendor_id' => (int)$vendorId,
            ':store_id' => (int)$storeId];

        return $connection->fetchRow($select, $bind);
    }

    /**
     * Load an object
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @param mixed $value
     * @param string $field field to load by (defaults to model id)
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function load(\Magento\Framework\Model\AbstractModel $object, $value, $field = null)
    {
        $object->beforeLoad($value, $field);
        if ($field === null) {
            $field = $this->getIdFieldName();
        }
        $websiteId = null;
        $storeId = null;
        if ($object->getWebsiteId()) {
            $websiteId = $object->getWebsiteId();
        }
        if ($object->getStoreId()) {
            $storeId = $object->getStoreId();
        }
        $connection = $this->getConnection();
        if ($connection && $value !== null) {
            $select = $this->_getLoadSelect($field, $value, $object);
            $data = $connection->fetchRow($select);

            if ($data) {
                $object->setData($data);
            }
        }
        $object->setData('website_id', $websiteId);
        $object->setData('store_id', $storeId);
        $this->unserializeFields($object);
        $this->_afterLoad($object);
        $object->afterLoad();
        $object->setOrigData();
        $object->setHasDataChanges(false);

        return $this;
    }

    /**
     * Get product identifier by sku
     *
     * @param string $sku
     * @param $storeId
     * @return int|false
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getIdBySku($sku, $storeId)
    {
        $connection = $this->getConnection();

        $select = $connection->select()->from($this->getMainTable(), 'vendor_product_id')
            ->where('vendor_sku = :vendor_sku');
        $select->joinLeft(
            ['rbvps' => $this->getTable('md_vendor_product_store')],
            "md_vendor_product.vendor_product_id = rbvps.vendor_product_id"
        )->where('rbvps.store_id = :store_id');

        $bind = [':vendor_sku' => (string)$sku, ':store_id' => (string)$storeId];

        return $connection->fetchOne($select, $bind);
    }

    /**
     * @param $productId
     * @param $vendorId
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getVendorProductSku($productId, $vendorId)
    {
        $connection = $this->getConnection();

        $select = $connection->select()->from($this->getMainTable(), 'vendor_sku')
            ->where('marketplace_product_id = :marketplace_product_id');
        $bind = [':marketplace_product_id' => (int)$productId];
        if ($vendorId) {
            $select->where('vendor_id = :vendor_id');
            $bind[':vendor_id'] = (int)$vendorId;
        }

        /*$select = $connection->select()->from($this->getMainTable(), 'vendor_sku')
            ->where('marketplace_product_id = :marketplace_product_id AND vendor_id = :vendor_id');

        $bind = [':marketplace_product_id' => (int)$productId, ':vendor_id' => (int)$vendorId];*/

        return $connection->fetchOne($select, $bind);
    }

    public function deleteVendorOffer($mpid, $vendorId)
    {
        $connection = $this->getConnection();
        $where = ['marketplace_product_id = ?' => (int)$mpid, 'vendor_id = ?' => (int)$vendorId];
        $connection->delete($this->getMainTable(), $where);
    }

    /**
     * @param $ids
     * @param int $status
     */
    public function updateStatusForProducts($ids, $status = 1)
    {
        if (is_array($ids)) {
            $ids = implode(',', $ids);
        }
        $connection = $this->getConnection();
        $data = [
            'status' => $status,
        ];
        $connection->update('md_vendor_product_website', $data, 'vendor_product_id IN (' . $ids . ')');
    }

    /**
     * @param $productId
     * @param int $websiteId
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getSellersDataByProductId($productId, $websiteId = 0)
    {
        $connection = $this->getConnection();

        $select = $connection->select()
            ->from($this->getMainTable(), ['md_vendor_product.vendor_id', 'marketplace_product_id'])
            ->where('marketplace_product_id = :marketplace_product_id');
        $select->joinLeft(
            ['mvpw' => $this->getTable('md_vendor_product_website')],
            "md_vendor_product.vendor_product_id = mvpw.vendor_product_id",
            ['mvpw.price', 'mvpw.special_price', 'mvpw.special_from_date', 'mvpw.special_to_date']
        )->where('mvpw.website_id = :website_id');

        $bind = [
            ':marketplace_product_id' => (int)$productId,
            ':website_id' => (int)$websiteId];

        return $connection->fetchAll($select, $bind);
    }

    /**
     * Used in following areas : [Catalog Price Rules]
     *
     * @param int $productId
     * @param int $websiteId
     * @return int|false
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getDefaultVendorIdUsingIndexes($productId, $websiteId)
    {
        $connection = $this->getConnection();

        $select = $connection->select()->from('md_vendor_product_listing_idx', 'vendor_id')
            ->where('marketplace_product_id = :marketplace_product_id AND website_id = :website_id');

        $bind = [':marketplace_product_id' => (string)$productId,
            ':website_id' => (int)$websiteId
        ];

        $select->limit(1);

        return $connection->fetchOne($select, $bind);
    }

    /**
     * Used to get minimum data to boost the speed.
     * @param $productId
     * @param $vendorId
     * @param $websiteId
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getVendorProductPrices($productId, $vendorId, $websiteId)
    {
        $connection = $this->getConnection();

        $select = $connection->select()->from(['main_table' =>$this->getMainTable()], 'vendor_sku')
            ->where('marketplace_product_id = :marketplace_product_id AND main_table.vendor_id = :vendor_id');

        $select->joinLeft(
            ['mvpw' => $this->getTable('md_vendor_product_website')],
            "main_table.vendor_product_id = mvpw.vendor_product_id",
            ['mvpw.price', 'mvpw.special_price', 'mvpw.special_from_date', 'mvpw.special_to_date']
        )->where('mvpw.website_id = :website_id');

        $bind = [
            ':marketplace_product_id' => (int)$productId,
            ':vendor_id' => (int)$vendorId,
            ':website_id' => (int)$websiteId
        ];

        return $connection->fetchRow($select, $bind);
    }
}
