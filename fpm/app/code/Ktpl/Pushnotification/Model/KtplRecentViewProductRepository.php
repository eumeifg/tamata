<?php declare(strict_types=1);

namespace Ktpl\Pushnotification\Model;

use Ktpl\Pushnotification\Api\Data\KtplRecentViewProductInterfaceFactory;
use Ktpl\Pushnotification\Api\KtplRecentViewProductRepositoryInterface;
use Ktpl\Pushnotification\Model\ResourceModel\KtplRecentViewProduct as ResourceKtplRecentViewProduct;
use Ktpl\Pushnotification\Model\ResourceModel\KtplRecentViewProduct\CollectionFactory as KtplRecentViewProductCollectionFactory;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\ExtensibleDataObjectConverter;
use Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Store\Model\StoreManagerInterface;


class KtplRecentViewProductRepository implements KtplRecentViewProductRepositoryInterface
{

    protected $extensionAttributesJoinProcessor;

    protected $searchResultsFactory;

    private $storeManager;

    protected $dataObjectProcessor;

    protected $dataObjectHelper;

    protected $extensibleDataObjectConverter;
    protected $resource;

    protected $ktplRecentViewProductCollectionFactory;

    protected $ktplRecentViewProductFactory;

    private $collectionProcessor;

    protected $dataKtplRecentViewProductFactory;


    /**
     * @param ResourceKtplRecentViewProduct $resource
     * @param KtplRecentViewProductFactory $ktplRecentViewProductFactory
     * @param KtplRecentViewProductInterfaceFactory $dataKtplRecentViewProductFactory
     * @param KtplRecentViewProductCollectionFactory $ktplRecentViewProductCollectionFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param DataObjectProcessor $dataObjectProcessor
     * @param StoreManagerInterface $storeManager
     * @param CollectionProcessorInterface $collectionProcessor
     * @param JoinProcessorInterface $extensionAttributesJoinProcessor
     * @param ExtensibleDataObjectConverter $extensibleDataObjectConverter
     */
    public function __construct(
        ResourceKtplRecentViewProduct $resource,
        KtplRecentViewProductFactory $ktplRecentViewProductFactory,
        KtplRecentViewProductInterfaceFactory $dataKtplRecentViewProductFactory,
        KtplRecentViewProductCollectionFactory $ktplRecentViewProductCollectionFactory,
        DataObjectHelper $dataObjectHelper,
        DataObjectProcessor $dataObjectProcessor,
        StoreManagerInterface $storeManager,
        CollectionProcessorInterface $collectionProcessor,
        JoinProcessorInterface $extensionAttributesJoinProcessor,
        ExtensibleDataObjectConverter $extensibleDataObjectConverter
    ) {
        $this->resource = $resource;
        $this->ktplRecentViewProductFactory = $ktplRecentViewProductFactory;
        $this->ktplRecentViewProductCollectionFactory = $ktplRecentViewProductCollectionFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->dataKtplRecentViewProductFactory = $dataKtplRecentViewProductFactory;
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
        \Ktpl\Pushnotification\Api\Data\KtplRecentViewProductInterface $ktplRecentViewProduct
    ) {

        $ktplRecentViewProductData = $this->extensibleDataObjectConverter->toNestedArray(
            $ktplRecentViewProduct,
            [],
            \Ktpl\Pushnotification\Api\Data\KtplRecentViewProductInterface::class
        );

        $ktplRecentViewProductModel = $this->ktplRecentViewProductFactory->create()->setData($ktplRecentViewProductData);

        try {
            $this->resource->save($ktplRecentViewProductModel);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__(
                'Could not save the Pushnotifications: %1',
                $exception->getMessage()
            ));
        }
        return $ktplRecentViewProductModel->getDataModel();
    }

    /**
     * {@inheritdoc}
     */
    public function get($ktplRecentViewProductId)
    {
        $ktplRecentViewProduct = $this->ktplRecentViewProductFactory->create();
        $this->resource->load($ktplRecentViewProduct, $ktplRecentViewProductId);
        if (!$ktplRecentViewProduct->getId()) {
            throw new NoSuchEntityException(__('Recently View with id "%1" does not exist.', $ktplRecentViewProductId));
        }
        return $ktplRecentViewProduct->getDataModel();
    }

    /**
     * {@inheritdoc}
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $criteria
    ) {
        $collection = $this->ktplRecentViewProductCollectionFactory->create();

        $this->extensionAttributesJoinProcessor->process(
            $collection,
            \Ktpl\Pushnotification\Api\Data\KtplRecentViewProductInterface::class
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
    // public function delete(
    //     \Ktpl\Pushnotification\Api\Data\KtplRecentViewProductInterface $ktplRecentViewProduct
    // ) {
    //     try {
    //         $ktplRecentViewProductModel = $this->ktplRecentViewProductFactory->create();
    //         $this->resource->load($ktplRecentViewProductModel, $ktplRecentViewProduct->getKtplRecentViewProductId());
    //         $this->resource->delete($ktplRecentViewProductModel);
    //     } catch (\Exception $exception) {
    //         throw new CouldNotDeleteException(__(
    //             'Could not delete the ktpl_recentViewProduct: %1',
    //             $exception->getMessage()
    //         ));
    //     }
    //     return true;
    // }


    public function delete($productId) {
        try {
           
            $ktplRecentViewProductModel = $this->ktplRecentViewProductFactory->create()->load((int)$productId, 'product_id');            
            
            $this->resource->delete($ktplRecentViewProductModel);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__(
                'Could not delete the ktpl_recentViewProduct: %1',
                $exception->getMessage()
            ));
        }
        return true;
    }
    /**
     * {@inheritdoc}
     */
    public function deleteById($ktplRecentViewProductId)
    {
        // return $this->delete($this->get($ktplRecentViewProductId));
        return $this->delete($ktplRecentViewProductId);
    }
}

