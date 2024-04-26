<?php declare(strict_types=1);

namespace Ktpl\Pushnotification\Model;

use Ktpl\Pushnotification\Api\Data\KtplAbandonCartDataInterfaceFactory;
use Ktpl\Pushnotification\Api\KtplAbandonCartRepositoryInterface;
use Ktpl\Pushnotification\Model\ResourceModel\KtplAbandonCart as ResourceKtplAbandonCart;
use Ktpl\Pushnotification\Model\ResourceModel\KtplAbandonCart\CollectionFactory as KtplAbandonCartCollectionFactory;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\ExtensibleDataObjectConverter;
use Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Store\Model\StoreManagerInterface;
use Ktpl\Pushnotification\Api\KtplAbandonCartFactory;

class KtplAbandonCartRepository implements KtplAbandonCartRepositoryInterface
{

    protected $extensionAttributesJoinProcessor;

    protected $searchResultsFactory;

    private $storeManager;

    protected $dataObjectProcessor;

    protected $dataObjectHelper;

    protected $extensibleDataObjectConverter;
    protected $resource;

    protected $ktplAbandonCartCollectionFactory;

    private $collectionProcessor;

    protected $dataAbandonCartFactory;

    /**
     * @param ResourceKtplAbandonCart $resource
     * @param KtplAbandonCartDataInterfaceFactory $dataAbandonCartFactory
     * @param KtplAbandonCartCollectionFactory $ktplAbandonCartCollectionFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param DataObjectProcessor $dataObjectProcessor
     * @param StoreManagerInterface $storeManager
     * @param CollectionProcessorInterface $collectionProcessor
     * @param JoinProcessorInterface $extensionAttributesJoinProcessor
     * @param ExtensibleDataObjectConverter $extensibleDataObjectConverter
     */
    public function __construct(
        ResourceKtplAbandonCart $resource,
        KtplAbandonCartDataInterfaceFactory $dataAbandonCartFactory,
        KtplAbandonCartCollectionFactory $ktplAbandonCartCollectionFactory,
        DataObjectHelper $dataObjectHelper,
        DataObjectProcessor $dataObjectProcessor,
        StoreManagerInterface $storeManager,
        CollectionProcessorInterface $collectionProcessor,
        JoinProcessorInterface $extensionAttributesJoinProcessor,
        ExtensibleDataObjectConverter $extensibleDataObjectConverter
    ) {
        $this->resource = $resource;
        $this->ktplAbandonCartCollectionFactory = $ktplAbandonCartCollectionFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->dataAbandonCartFactory = $dataAbandonCartFactory;
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->storeManager = $storeManager;
        $this->collectionProcessor = $collectionProcessor;
        $this->extensionAttributesJoinProcessor = $extensionAttributesJoinProcessor;
        $this->extensibleDataObjectConverter = $extensibleDataObjectConverter;
    }

     /**
     * {@inheritdoc}
     */
    public function save(
        \Ktpl\Pushnotification\Api\Data\KtplAbandonCartDataInterface $ktplAbandonCart
    ) {
        try {
            $this->resource->save($ktplAbandonCart);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__(
                'Could not save the AbandonCart: %1',
                $exception->getMessage()
            ));
        }
        return $ktplAbandonCart;
    }


    /**
     * {@inheritdoc}
     */
    public function get($ktplAbandonCartId)
    {
        $ktplAbandonCart = $this->dataAbandonCartFactory->create();
        $this->resource->load($ktplAbandonCart, $ktplAbandonCartId);
        if (!$ktplAbandonCart->getId()) {
            throw new NoSuchEntityException(__('cart id "%1" does not exist.', $ktplAbandonCartId));
        }
        return $ktplAbandonCart->getDataModel();
    }

    /**
     * {@inheritdoc}
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $criteria
    ) {
        $collection = $this->ktplAbandonCartCollectionFactory->create();

        $this->extensionAttributesJoinProcessor->process(
            $collection,
            \Ktpl\Pushnotification\Api\Data\KtplAbandonCartDataInterface::class
        );

        $this->collectionProcessor->process($criteria, $collection);

        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($criteria);

        $items = [];
        foreach ($collection as $model) {
            $items[] = $model->getDataModel();
        }

        $searchResults->setItems($items);
        $searchResults->setTotalCount($collection->getSize());
        return $searchResults;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(
        \Ktpl\Pushnotification\Api\Data\KtplAbandonCartDataInterface $ktplAbandonCart
    ) {
         $id = $ktplAbandonCart->getId();
        try {
             unset($this->instances[$id]);
             $this->resource->delete($ktplAbandonCart);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__(
                'Could not delete the ktpl_abandonCart: %1',
                $exception->getMessage()
            ));
        }
        return true;
    }


    /**
     * {@inheritdoc}
     */
    public function deleteById($ktplAbandonCartId)
    {
        return $this->delete($this->get($ktplAbandonCartId));
    }
}

