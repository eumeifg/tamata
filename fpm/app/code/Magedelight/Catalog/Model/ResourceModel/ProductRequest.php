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

use Magedelight\Catalog\Model\ProductRequest as VendorProductRequest;
use Magento\Framework\EntityManager\EntityManager;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Store\Model\StoreManagerInterface;

class ProductRequest extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{

    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * ProductRequest constructor.
     * @param Context $context
     * @param EntityManager $entityManager
     * @param StoreManagerInterface $storeManager
     * @param null $connectionName
     */
    public function __construct(
        Context $context,
        EntityManager $entityManager,
        StoreManagerInterface $storeManager,
        $connectionName = null
    ) {
        $this->entityManager = $entityManager;
        $this->storeManager = $storeManager;
        parent::__construct($context, $connectionName);
    }

    protected $setSPNullFlag = 0;

    protected function _construct()
    {
        $this->_init('md_vendor_product_request', 'product_request_id');
    }

    /**
     * return product request for a particular vendor and product which not approved.
     * @param type $productId
     * @param type $vendorId
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function existButNotApproved($productId, $vendorId)
    {
        $connection = $this->getConnection();

        $select = $connection->select()
            ->from($this->getMainTable(), 'product_request_id')
            ->where('marketplace_product_id = :marketplace_product_id')
            ->where('vendor_id = :vendor_id')
            ->where('status != :status');

        $bind = [
            ':marketplace_product_id' => $productId,
            ':vendor_id' => $vendorId,
            ':status' => VendorProductRequest::STATUS_APPROVED
        ];

        return $connection->fetchOne($select, $bind);
    }

    /**
     * @param \Magento\Framework\Model\AbstractModel $object
     * @param mixed $value
     * @param null $field
     * @return $this|\Magento\Framework\Model\ResourceModel\Db\AbstractDb
     */
    public function load(\Magento\Framework\Model\AbstractModel $object, $value, $field = null)
    {
        parent::load($object, $value, $field);
        $this->entityManager->load($object, $value);
        return $this;
    }

    /**
     * returns product request data for product of particular vendor.
     * @param type $productId
     * @param type $vendorId
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function loadByProductVendorId($productId, $vendorId)
    {
        $connection = $this->getConnection();

        $select = $connection->select()
            ->from($this->getMainTable(), 'product_request_id')
            ->where('marketplace_product_id = :marketplace_product_id')
            ->where('vendor_id = :vendor_id');

        $bind = [
            ':marketplace_product_id' => $productId,
            ':vendor_id' => $vendorId
        ];

        return $connection->fetchOne($select, $bind);
    }

    /**
     * Retrieves Product Request Product Name from DB by passed id.
     *
     * @param string $id
     * @return string|bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getRequestNameById($id)
    {
        $adapter = $this->getConnection();
        $select = $adapter->select()
            ->from($this->getMainTable(), 'name')
            ->where('product_request_id = :product_request_id');
        $binds = ['product_request_id' => (int)$id];
        return $adapter->fetchOne($select, $binds);
    }

    /**
     * @param \Magento\Framework\Model\AbstractModel $object
     * @param array $attribute
     * @return $this
     */
    protected function beforeSaveAttribute(\Magento\Framework\Model\AbstractModel $object, $attribute)
    {
        if ($object->getEventObject() && $object->getEventPrefix()) {
            $this->eventManager->dispatch(
                $object->getEventPrefix() . '_save_attribute_before',
                [
                    $object->getEventObject() => $this,
                    'object' => $object,
                    'attribute' => $attribute
                ]
            );
        }
        return $this;
    }

    /**
     * After save object attribute
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @param string $attribute
     * @return $this
     */
    protected function afterSaveAttribute(\Magento\Framework\Model\AbstractModel $object, $attribute)
    {
        if ($object->getEventObject() && $object->getEventPrefix()) {
            $this->eventManager->dispatch(
                $object->getEventPrefix() . '_save_attribute_after',
                [
                    $object->getEventObject() => $this,
                    'object' => $object,
                    'attribute' => $attribute
                ]
            );
        }
        return $this;
    }

    /**
     * @param \Magento\Framework\Model\AbstractModel $object
     * @param array $attribute
     * @return $this
     * @throws \Exception
     */
    public function saveAttribute(\Magento\Framework\Model\AbstractModel $object, $attribute)
    {
        if (is_string($attribute)) {
            $attributes = [$attribute];
        } else {
            $attributes = $attribute;
        }
        if (is_array($attributes) && !empty($attributes)) {
            $this->getConnection()->beginTransaction();
            $data = array_intersect_key($object->getData(), array_flip($attributes));
            try {
                $this->beforeSaveAttribute($object, $attributes);
                if ($object->getId() && !empty($data)) {
                    $this->getConnection()->update(
                        $object->getResource()->getMainTable(),
                        $data,
                        [$object->getResource()->getIdFieldName() . '= ?' => (int)$object->getId()]
                    );
                    $object->addData($data);
                }
                $this->afterSaveAttribute($object, $attributes);
                $this->getConnection()->commit();
            } catch (\Exception $e) {
                $this->getConnection()->rollBack();
                throw $e;
            }
        }
        return $this;
    }

    /**
     * Retrieve Required children ids
     * Return grouped array, ex array(
     *   group => array(ids)
     * )
     *
     * @param int|array $parentId
     * @return array
     */
    public function getChildrenIds($parentId)
    {
        $select = $this->getConnection()->select()->from(
            ['l' => 'md_vendor_product_request_super_link'],
            ['product_request_id', 'parent_id']
        )->join(
            ['p' => $this->getTable('md_vendor_product_request')],
            'p.product_request_id  = l.parent_id',
            []
        )->where(
            'p.product_request_id IN (?)',
            $parentId
        );

        $childrenIds = [
            0 => array_column(
                $this->getConnection()->fetchAll($select),
                'product_request_id',
                'product_request_id'
            )
        ];

        return $childrenIds;
    }

    /**
     * @param $parentId
     * @param null $websiteId
     * @param null $storeId
     * @param array $websiteColumns
     * @param array $storeColumns
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getChildItems(
        $parentId,
        $websiteId = null,
        $storeId = null,
        $websiteColumns = ['*'],
        $storeColumns = ['*']
    ) {
        $websiteId = $websiteId ?? $this->storeManager->getStore()->getWebsiteId();
        $storeId = $storeId ?? $this->storeManager->getStore()->getId();
        $select = $this->getConnection()->select()->from(
            ['l' => 'md_vendor_product_request_super_link'],
            ['product_request_id', 'parent_id']
        )->join(
            ['p' => $this->getTable('md_vendor_product_request')],
            'p.product_request_id  = l.product_request_id',
            []
        )->join(
            ['p_website' => $this->getTable('md_vendor_product_request_website')],
            'p_website.product_request_id  = p.product_request_id AND p_website.website_id = ' . $websiteId,
            $websiteColumns
        )->join(
            ['p_store' => $this->getTable('md_vendor_product_request_store')],
            'p_store.product_request_id  = p.product_request_id AND p_store.store_id = ' . $storeId,
            $storeColumns
        )->where(
            'l.parent_id = ? ',
            $parentId
        );

        return $this->getConnection()->fetchAll($select);
    }
}
