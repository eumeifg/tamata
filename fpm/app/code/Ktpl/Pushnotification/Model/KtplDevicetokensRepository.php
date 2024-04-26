<?php declare(strict_types=1);


namespace Ktpl\Pushnotification\Model;

use Ktpl\Pushnotification\Api\Data\KtplDevicetokensInterfaceFactory;
use Ktpl\Pushnotification\Api\Data\KtplDevicetokensSearchResultsInterfaceFactory;
use Ktpl\Pushnotification\Api\KtplDevicetokensRepositoryInterface;
use Ktpl\Pushnotification\Model\ResourceModel\KtplDevicetokens as ResourceKtplDevicetokens;
use Ktpl\Pushnotification\Model\ResourceModel\KtplDevicetokens\CollectionFactory as KtplDevicetokensCollectionFactory;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\ExtensibleDataObjectConverter;
use Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Store\Model\StoreManagerInterface;


class KtplDevicetokensRepository implements KtplDevicetokensRepositoryInterface
{

    protected $extensionAttributesJoinProcessor;

    protected $searchResultsFactory;

    protected $ktplDevicetokensFactory;

    protected $dataObjectProcessor;

    private $storeManager;

    protected $dataObjectHelper;

    protected $dataKtplDevicetokensFactory;

    protected $extensibleDataObjectConverter;
    protected $resource;

    protected $ktplDevicetokensCollectionFactory;

    private $collectionProcessor;


    /**
     * @param ResourceKtplDevicetokens $resource
     * @param KtplDevicetokensFactory $ktplDevicetokensFactory
     * @param KtplDevicetokensInterfaceFactory $dataKtplDevicetokensFactory
     * @param KtplDevicetokensCollectionFactory $ktplDevicetokensCollectionFactory
     * @param KtplDevicetokensSearchResultsInterfaceFactory $searchResultsFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param DataObjectProcessor $dataObjectProcessor
     * @param StoreManagerInterface $storeManager
     * @param CollectionProcessorInterface $collectionProcessor
     * @param JoinProcessorInterface $extensionAttributesJoinProcessor
     * @param ExtensibleDataObjectConverter $extensibleDataObjectConverter
     */
    public function __construct(
        ResourceKtplDevicetokens $resource,
        KtplDevicetokensFactory $ktplDevicetokensFactory,
        KtplDevicetokensInterfaceFactory $dataKtplDevicetokensFactory,
        KtplDevicetokensCollectionFactory $ktplDevicetokensCollectionFactory,
        KtplDevicetokensSearchResultsInterfaceFactory $searchResultsFactory,
        DataObjectHelper $dataObjectHelper,
        DataObjectProcessor $dataObjectProcessor,
        StoreManagerInterface $storeManager,
        CollectionProcessorInterface $collectionProcessor,
        JoinProcessorInterface $extensionAttributesJoinProcessor,
        ExtensibleDataObjectConverter $extensibleDataObjectConverter
    ) {
        $this->resource = $resource;
        $this->ktplDevicetokensFactory = $ktplDevicetokensFactory;
        $this->ktplDevicetokensCollectionFactory = $ktplDevicetokensCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->dataKtplDevicetokensFactory = $dataKtplDevicetokensFactory;
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
        \Ktpl\Pushnotification\Api\Data\KtplDevicetokensInterface $ktplDevicetokens
    ) {
        /* if (empty($ktplDevicetokens->getStoreId())) {
            $storeId = $this->storeManager->getStore()->getId();
            $ktplDevicetokens->setStoreId($storeId);
        } */
        
        $ktplDevicetokensData = $this->extensibleDataObjectConverter->toNestedArray(
            $ktplDevicetokens,
            [],
            \Ktpl\Pushnotification\Api\Data\KtplDevicetokensInterface::class
        );
        
        $ktplDevicetokensModel = $this->ktplDevicetokensFactory->create()->setData($ktplDevicetokensData);
        
        try {
            $this->resource->save($ktplDevicetokensModel);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__(
                'Could not save the ktplDevicetokens: %1',
                $exception->getMessage()
            ));
        }
        return $ktplDevicetokensModel->getDataModel();
    }

    /**
     * {@inheritdoc}
     */
    public function get($ktplDevicetokensId)
    {
        $ktplDevicetokens = $this->ktplDevicetokensFactory->create();
        $this->resource->load($ktplDevicetokens, $ktplDevicetokensId);
        if (!$ktplDevicetokens->getId()) {
            throw new NoSuchEntityException(__('ktpl_devicetokens with id "%1" does not exist.', $ktplDevicetokensId));
        }
        return $ktplDevicetokens->getDataModel();
    }

    /**
     * {@inheritdoc}
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $criteria
    ) {
        $collection = $this->ktplDevicetokensCollectionFactory->create();
        
        $this->extensionAttributesJoinProcessor->process(
            $collection,
            \Ktpl\Pushnotification\Api\Data\KtplDevicetokensInterface::class
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
        \Ktpl\Pushnotification\Api\Data\KtplDevicetokensInterface $ktplDevicetokens
    ) {
        try {
            $ktplDevicetokensModel = $this->ktplDevicetokensFactory->create();
            $this->resource->load($ktplDevicetokensModel, $ktplDevicetokens->getKtplDevicetokensId());
            $this->resource->delete($ktplDevicetokensModel);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__(
                'Could not delete the ktpl_devicetokens: %1',
                $exception->getMessage()
            ));
        }
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteById($ktplDevicetokensId)
    {
        return $this->delete($this->get($ktplDevicetokensId));
    }
}

