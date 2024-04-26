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

namespace Ktpl\ProductLabel\Model\Repository;

use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface as CollectionProcessor;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Data\Collection\AbstractDb as AbstractCollection;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb as AbstractResourceModel;
use Magento\Framework\Phrase;

class Manager
{
    protected $collectionProcessor;

    protected $objectFactory;

    protected $objectResource;

    protected $objectCollectionFactory;

    protected $objectSearchResultsFactory;

    protected $identifierFieldName;

    protected $cacheById = [];

    protected $cacheByIdentifier = [];

    public function __construct(
        CollectionProcessor $collectionProcessor,
        $objectFactory,
        AbstractResourceModel $objectResource,
        $objectCollectionFactory,
        $objectSearchResultsFactory,
        $identifierFieldName = null
    ) {
        $this->collectionProcessor = $collectionProcessor;

        $this->objectFactory              = $objectFactory;
        $this->objectResource             = $objectResource;
        $this->objectCollectionFactory    = $objectCollectionFactory;
        $this->objectSearchResultsFactory = $objectSearchResultsFactory;
        $this->identifierFieldName        = $identifierFieldName;
    }

    public function getEntityById($objectId)
    {
        if (!isset($this->cacheById[$objectId])) {
            $object = $this->objectFactory->create();
            $this->objectResource->load($object, $objectId);

            if (!$object->getId()) {
                throw NoSuchEntityException::singleField('objectId', $objectId);
            }

            $this->cacheById[$object->getId()] = $object;

            if ($this->identifierFieldName !== null) {
                $objectIdentifier                           = $object->getData($this->identifierFieldName);
                $this->cacheByIdentifier[$objectIdentifier] = $object;
            }
        }

        return $this->cacheById[$objectId];
    }

    public function getEntityByIdentifier($objectIdentifier)
    {
        if ($this->identifierFieldName === null) {
            throw new NoSuchEntityException('The identifier field name is not set');
        }

        if (!isset($this->cacheByIdentifier[$objectIdentifier])) {
            $object = $this->objectFactory->create();
            $this->objectResource->load($object, $objectIdentifier, $this->identifierFieldName);

            if (!$object->getId()) {
                throw NoSuchEntityException::singleField('objectIdentifier', $objectIdentifier);
            }

            $this->cacheById[$object->getId()]          = $object;
            $this->cacheByIdentifier[$objectIdentifier] = $object;
        }

        return $this->cacheByIdentifier[$objectIdentifier];
    }

    public function saveEntity(AbstractModel $object)
    {
        try {
            $this->objectResource->save($object);

            unset($this->cacheById[$object->getId()]);
            if ($this->identifierFieldName !== null) {
                $objectIdentifier = $object->getData($this->identifierFieldName);
                unset($this->cacheByIdentifier[$objectIdentifier]);
            }
        } catch (\Exception $e) {
            $msg = new Phrase($e->getMessage());
            throw new CouldNotSaveException($msg);
        }

        return $object;
    }

    public function deleteEntity(AbstractModel $object)
    {
        try {
            $this->objectResource->delete($object);

            unset($this->cacheById[$object->getId()]);
            if ($this->identifierFieldName !== null) {
                $objectIdentifier = $object->getData($this->identifierFieldName);
                unset($this->cacheByIdentifier[$objectIdentifier]);
            }
        } catch (\Exception $e) {
            $msg = new Phrase($e->getMessage());
            throw new CouldNotDeleteException($msg);
        }

        return true;
    }

    public function deleteEntityById($objectId)
    {
        return $this->deleteEntity($this->getEntityById($objectId));
    }

    public function deleteEntityByIdentifier($objectIdentifier)
    {
        return $this->deleteEntity($this->getEntityByIdentifier($objectIdentifier));
    }

    public function getEntities(SearchCriteriaInterface $searchCriteria = null)
    {
        $collection = $this->objectCollectionFactory->create();
        $searchResults = $this->objectSearchResultsFactory->create();
        if ($searchCriteria) {
            $searchResults->setSearchCriteria($searchCriteria);
            $this->collectionProcessor->process($searchCriteria, $collection);
        }
        $collection->load();
        $searchResults->setTotalCount($collection->getSize());
        $searchResults->setItems($collection->getItems());

        return $searchResults;
    }
}
