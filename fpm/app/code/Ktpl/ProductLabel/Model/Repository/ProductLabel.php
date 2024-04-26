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

use Magento\Framework\Api\SearchCriteriaInterface;
use Ktpl\ProductLabel\Api\ProductLabelRepositoryInterface;
use Ktpl\ProductLabel\Api\Data\ProductLabelInterface;
use Ktpl\ProductLabel\Api\Data\ProductLabelInterfaceFactory;
use Ktpl\ProductLabel\Api\Data\ProductLabelSearchResultsInterfaceFactory;
use Ktpl\ProductLabel\Model\Repository\Manager as RepositoryManager;
use Ktpl\ProductLabel\Model\Repository\ManagerFactory as RepositoryManagerFactory;
use Ktpl\ProductLabel\Model\ResourceModel\ProductLabel as ProductLabelResourceModel;
use Ktpl\ProductLabel\Model\ResourceModel\ProductLabel\CollectionFactory as ProductLabelCollectionFactory;

class ProductLabel implements ProductLabelRepositoryInterface
{
    protected $productLabelRepositoryManager;

    public function __construct(
        RepositoryManagerFactory $repositoryManagerFactory,
        ProductLabelInterfaceFactory $objectFactory,
        ProductLabelResourceModel $objectResource,
        ProductLabelCollectionFactory $objectCollectionFactory,
        ProductLabelSearchResultsInterfaceFactory $objectSearchResultsFactory
    ) {
        $this->productLabelRepositoryManager = $repositoryManagerFactory->create(
            [
                'objectFactory'              => $objectFactory,
                'objectResource'             => $objectResource,
                'objectCollectionFactory'    => $objectCollectionFactory,
                'objectSearchResultsFactory' => $objectSearchResultsFactory,
                'identifierFieldName'        => ProductLabelInterface::PRODUCTLABEL_ID,
            ]
        );
    }

    public function getById($objectId)
    {
        return $this->productLabelRepositoryManager->getEntityById($objectId);
    }

    public function getByIdentifier($objectIdentifier)
    {
        return $this->productLabelRepositoryManager->getEntityByIdentifier($objectIdentifier);
    }

    public function getList(SearchCriteriaInterface $searchCriteria = null)
    {
        return $this->productLabelRepositoryManager->getEntities($searchCriteria);
    }

    public function save(ProductLabelInterface $object)
    {
        return $this->productLabelRepositoryManager->saveEntity($object);
    }

    public function deleteById($objectId)
    {
        return $this->productLabelRepositoryManager->deleteEntityById($objectId);
    }

    public function deleteByIdentifier($object)
    {
        return $this->productLabelRepositoryManager->deleteEntityByIdentifier($object);
    }
}
