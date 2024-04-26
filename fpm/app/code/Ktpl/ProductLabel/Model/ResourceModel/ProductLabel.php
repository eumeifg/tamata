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

namespace Ktpl\ProductLabel\Model\ResourceModel;

use Magento\Framework\DB\Select;
use Magento\Framework\EntityManager\EntityManager;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\Model\AbstractModel;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Ktpl\ProductLabel\Api\Data\ProductLabelInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\App\ObjectManager;

class ProductLabel extends AbstractDb
{
    protected $entityManager;

    protected $metadataPool;

    private $storeManager;

    protected $serializer;

    public function __construct(
        Context $context,
        EntityManager $entityManager,
        MetadataPool $metadataPool,
        StoreManagerInterface $storeManager,
        SerializerInterface $serializer = null,
        $connectionName = null
    ) {
        parent::__construct($context, $connectionName);

        $this->entityManager = $entityManager;
        $this->metadataPool  = $metadataPool;
        $this->storeManager  = $storeManager;
        $this->serializer = $serializer ? : ObjectManager::getInstance()->get(SerializerInterface::class);
    }

    public function getConnection()
    {
        $connectionName = $this->metadataPool->getMetadata(ProductLabelInterface::class)->getEntityConnectionName();

        return $this->_resources->getConnectionByName($connectionName);
    }

    public function save(AbstractModel $object)
    {
        $this->entityManager->save($object);

        return $this;
    }

    public function delete(AbstractModel $object)
    {
        $this->entityManager->delete($object);

        return $this;
    }

    public function saveStoreRelation(\Magento\Framework\Model\AbstractModel $object)
    {
        $oldStores = $this->getStoreIds($object);
        if (strpos($this->serializer->serialize($object->getStores()), ',') !== false) {
            $newStores = explode(',', (string) $object->getStores());
        } else {
            $newStores = $object->getStores();
        }

        $this->checkUnicity($object, $newStores);

        $table = $this->getTable(ProductLabelInterface::STORE_TABLE_NAME);

        $delete = array_diff($oldStores, $newStores);
        if ($delete) {
            $where = [
                $this->getIdFieldName() . ' = ?' => (int) $object->getData($this->getIdFieldName()),
                'store_id IN (?)'                => $delete,
            ];
            $this->getConnection()->delete($table, $where);
        }

        $insert = array_diff($newStores, $oldStores);
        if ($insert) {
            $data = [];
            foreach ($insert as $storeId) {
                $data[] = [
                    $this->getIdFieldName() => (int) $object->getData($this->getIdFieldName()),
                    'store_id'              => (int) $storeId,
                ];
            }

            $this->getConnection()->insertMultiple($table, $data);
        }

        return $object;
    }

    public function getStoreIds(\Magento\Framework\Model\AbstractModel $object)
    {
        $connection = $this->getConnection();

        $select = $connection->select()
            ->from(['pls' => $this->getTable(ProductLabelInterface::STORE_TABLE_NAME)], 'store_id')
            ->join(
                ['pl' => $this->getMainTable()],
                'pls.' . $this->getIdFieldName() . ' = pl.' . $this->getIdFieldName(),
                []
            )
            ->where("pl." . $this->getIdFieldName() . " = :{$this->getIdFieldName()}");

        return $connection->fetchCol($select, [$this->getIdFieldName() => (int) $object->getId()]);
    }

    protected function _construct()
    {
        $this->_init(
            ProductLabelInterface::TABLE_NAME,
            ProductLabelInterface::PRODUCTLABEL_ID
        );
    }

    private function checkUnicity(\Magento\Framework\Model\AbstractModel $object, array $stores)
    {
        $isDefaultStore = $this->storeManager->isSingleStoreMode()
            || array_search(Store::DEFAULT_STORE_ID, $stores) !== false;

        if (!$isDefaultStore) {
            $stores[] = Store::DEFAULT_STORE_ID;
        }

        $select = $this->getConnection()->select()
            ->from(['pl' => $this->getMainTable()])
            ->join(
                ['pls' => $this->getTable(ProductLabelInterface::STORE_TABLE_NAME)],
                'pl.' . $this->getIdFieldName() . ' = pls.' . $this->getIdFieldName(),
                [ProductLabelInterface::STORE_ID]
            )
            ->where('pl.' . ProductLabelInterface::ATTRIBUTE_ID . ' = ?  ', $object->getData(ProductLabelInterface::ATTRIBUTE_ID))
            ->where('pl.' . ProductLabelInterface::OPTION_ID . ' = ?  ', $object->getData(ProductLabelInterface::OPTION_ID));

        if (!$isDefaultStore) {
            $select->where('pls.store_id IN (?)', $stores);
        }

        if ($object->getId()) {
            $select->where('pl.' . $this->getIdFieldName() . ' <> ?', $object->getId());
        }

        if ($row = $this->getConnection()->fetchRow($select)) {
            $error = new \Magento\Framework\Phrase(
                'Label for attribute %1, option %2, and store %3  already exist.',
                [
                    $object->getData(ProductLabelInterface::ATTRIBUTE_ID),
                    $object->getData(ProductLabelInterface::OPTION_ID),
                    $row[ProductLabelInterface::STORE_ID],
                ]
            );

            throw new AlreadyExistsException($error);
        }

        return true;
    }
}
