<?php declare(strict_types=1);


namespace Ktpl\Pushnotification\Model;

use Ktpl\Pushnotification\Api\Data\KtplPushnotificationsInterfaceFactory;
use Ktpl\Pushnotification\Api\Data\KtplPushnotificationsSearchResultsInterfaceFactory;
use Ktpl\Pushnotification\Api\KtplPushnotificationsRepositoryInterface;
use Ktpl\Pushnotification\Model\ResourceModel\KtplPushnotifications as ResourceKtplPushnotifications;
use Ktpl\Pushnotification\Model\ResourceModel\KtplPushnotifications\CollectionFactory as KtplPushnotificationsCollectionFactory;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\ExtensibleDataObjectConverter;
use Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Store\Model\StoreManagerInterface;


class KtplPushnotificationsRepository implements KtplPushnotificationsRepositoryInterface
{

    protected $extensionAttributesJoinProcessor;

    protected $searchResultsFactory;

    private $storeManager;

    protected $dataObjectProcessor;

    protected $dataObjectHelper;

    protected $extensibleDataObjectConverter;
    protected $resource;

    protected $ktplPushnotificationsCollectionFactory;

    protected $ktplPushnotificationsFactory;

    private $collectionProcessor;

    protected $dataKtplPushnotificationsFactory;


    /**
     * @param ResourceKtplPushnotifications $resource
     * @param KtplPushnotificationsFactory $ktplPushnotificationsFactory
     * @param KtplPushnotificationsInterfaceFactory $dataKtplPushnotificationsFactory
     * @param KtplPushnotificationsCollectionFactory $ktplPushnotificationsCollectionFactory
     * @param KtplPushnotificationsSearchResultsInterfaceFactory $searchResultsFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param DataObjectProcessor $dataObjectProcessor
     * @param StoreManagerInterface $storeManager
     * @param CollectionProcessorInterface $collectionProcessor
     * @param JoinProcessorInterface $extensionAttributesJoinProcessor
     * @param ExtensibleDataObjectConverter $extensibleDataObjectConverter
     */
    public function __construct(
        ResourceKtplPushnotifications $resource,
        KtplPushnotificationsFactory $ktplPushnotificationsFactory,
        KtplPushnotificationsInterfaceFactory $dataKtplPushnotificationsFactory,
        KtplPushnotificationsCollectionFactory $ktplPushnotificationsCollectionFactory,
        KtplPushnotificationsSearchResultsInterfaceFactory $searchResultsFactory,
        DataObjectHelper $dataObjectHelper,
        DataObjectProcessor $dataObjectProcessor,
        StoreManagerInterface $storeManager,
        CollectionProcessorInterface $collectionProcessor,
        JoinProcessorInterface $extensionAttributesJoinProcessor,
        ExtensibleDataObjectConverter $extensibleDataObjectConverter
    ) {
        $this->resource = $resource;
        $this->ktplPushnotificationsFactory = $ktplPushnotificationsFactory;
        $this->ktplPushnotificationsCollectionFactory = $ktplPushnotificationsCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->dataKtplPushnotificationsFactory = $dataKtplPushnotificationsFactory;
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
        \Ktpl\Pushnotification\Api\Data\KtplPushnotificationsInterface $ktplPushnotifications
    ) {
        /* if (empty($ktplPushnotifications->getStoreId())) {
            $storeId = $this->storeManager->getStore()->getId();
            $ktplPushnotifications->setStoreId($storeId);
        } */

        $ktplPushnotificationsData = $this->extensibleDataObjectConverter->toNestedArray(
            $ktplPushnotifications,
            [],
            \Ktpl\Pushnotification\Api\Data\KtplPushnotificationsInterface::class
        );

        $ktplPushnotificationsModel = $this->ktplPushnotificationsFactory->create()->setData($ktplPushnotificationsData);

        try {
            $this->resource->save($ktplPushnotificationsModel);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__(
                'Could not save the Pushnotifications: %1',
                $exception->getMessage()
            ));
        }
        return $ktplPushnotificationsModel->getDataModel();
    }

    /**
     * {@inheritdoc}
     */
    public function get($ktplPushnotificationsId)
    {
        $ktplPushnotifications = $this->ktplPushnotificationsFactory->create();
        $this->resource->load($ktplPushnotifications, $ktplPushnotificationsId);
        if (!$ktplPushnotifications->getId()) {
            throw new NoSuchEntityException(__('Pushnotifications with id "%1" does not exist.', $ktplPushnotificationsId));
        }
        return $ktplPushnotifications->getDataModel();
    }

    /**
     * {@inheritdoc}
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $criteria
    ) {
        $collection = $this->ktplPushnotificationsCollectionFactory->create();

        $this->extensionAttributesJoinProcessor->process(
            $collection,
            \Ktpl\Pushnotification\Api\Data\KtplPushnotificationsInterface::class
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
        \Ktpl\Pushnotification\Api\Data\KtplPushnotificationsInterface $ktplPushnotifications
    ) {
        try {
            $ktplPushnotificationsModel = $this->ktplPushnotificationsFactory->create();
            $this->resource->load($ktplPushnotificationsModel, $ktplPushnotifications->getKtplPushnotificationsId());
            $this->resource->delete($ktplPushnotificationsModel);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__(
                'Could not delete the ktpl_pushnotifications: %1',
                $exception->getMessage()
            ));
        }
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteById($ktplPushnotificationsId)
    {
        return $this->delete($this->get($ktplPushnotificationsId));
    }
}

