<?php

namespace MDC\Catalog\Block\Sellerhtml\Sellexisting;
use Magedelight\Catalog\Block\Sellerhtml\Sellexisting\Result;
use Magento\Catalog\Model\Indexer\Product\Flat\State as FlatState;

/**
 * Class ResultRewrite
 * @package MDC\Catalog\Block\Sellerhtml\Sellexisting
 */
class ResultRewrite extends Result
{
    /**
     * ResultRewrite constructor.
     * @param \Magedelight\Backend\Block\Template\Context $context
     * @param \Magedelight\Backend\Model\Auth\Session $authSession
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
     * @param \Magento\Catalog\Model\CategoryRepository $categoryRepository
     * @param \Magento\Catalog\Model\Product\Attribute\Source\Status $productStatus
     * @param \Magento\Catalog\Model\Product\Visibility $productVisibility
     * @param \Magento\Catalog\Model\CategoryFactory $categoryFactory
     * @param \Magedelight\Catalog\Model\ProductFactory $vendorProductFactory
     * @param \Magedelight\Catalog\Model\ProductRequestFactory $productRequestFactory
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepositoryInterface
     * @param FlatState $flatState
     * @param array $data
     */
    public function __construct(
        \Magedelight\Backend\Block\Template\Context $context,
        \Magedelight\Backend\Model\Auth\Session $authSession,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Catalog\Model\CategoryRepository $categoryRepository,
        \Magento\Catalog\Model\Product\Attribute\Source\Status $productStatus,
        \Magento\Catalog\Model\Product\Visibility $productVisibility,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        \Magedelight\Catalog\Model\ProductFactory $vendorProductFactory,
        \Magedelight\Catalog\Model\ProductRequestFactory $productRequestFactory,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepositoryInterface,
        FlatState $flatState,
        array $data = []
    ) {
        parent::__construct($context, $authSession, $productCollectionFactory, $categoryRepository, $productStatus, $productVisibility, $categoryFactory, $vendorProductFactory, $productRequestFactory, $productRepositoryInterface, $flatState, $data);
    }

    protected function _construct()
    {
        parent::_construct();
        $stockFlag = 'has_stock_status_filter';
        $search = $this->getRequest()->getParam('search');
        $categoryId = $this->getRequest()->getParam('category');
        $vendorId = ($this->getVendor()) ? $this->getVendor()->getVendorId() : '';
        $vendorCollection = $this->_vendorProductFactory->create()->getCollection()
            ->addFieldToFilter('main_table.vendor_id', ['eq' => $vendorId]);
        $excludeIds[] = $vendorCollection->getColumnValues('marketplace_product_id');

        $collection = $this->_productCollectionFactory->create();
        /* Flat table Compatibility Changes */
        if ($this->flatState->isAvailable()) {
            $collection->addFieldToFilter('type_id', ['neq' => 'configurable']);
            $collection->addFieldToSelect('name');
            $collection->addFieldToSelect('model_number');
            $collection->addFieldToSelect('small_image');
        } else {
            $collection->addAttributeToFilter('type_id', ['neq' => 'configurable']);
            $collection->addAttributeToSelect('name');
            $collection->addAttributeToSelect('model_number');
            $collection->addAttributeToSelect('small_image');
        }

        if ($categoryId) {
            $collection->addCategoriesFilter(['eq'=>$categoryId]);
        }

        $requestedProducts = $this->getRequestedProducts($vendorId);

        if ($excludeIds[0] != null) {
            $excludeIds = array_merge($excludeIds[0], $requestedProducts);
            $collection->addIdFilter($excludeIds, true);
        }

        /* Flat table Compatibility Changes */
        if ($search) {
            if ($this->flatState->isAvailable()) {
                $collection->addFieldToFilter([
                    ['attribute' => 'name', 'like' => '%' . $search . '%'],
                    ['attribute' => 'sku', 'like' => trim($search)],
                    // ['attribute' => 'bar_code', 'like' => trim($search)]
                ]);
            } else {
                $collection->addAttributeToFilter([
                    ['attribute' => 'name', 'like' => '%' . $search . '%'],
                    ['attribute' => 'sku', 'like' => trim($search)],
                    // ['attribute' => 'bar_code', 'like' => trim($search)]
                ]);
            }
        } else {
            if ($this->flatState->isAvailable()) {
                $collection->addFieldToFilter([
                    ['attribute' => 'name', 'like' => '%' . $search . '%'],
                    ['attribute' => 'sku', 'like' => trim($search)]
                ]);
            } else {
                $collection->addAttributeToFilter([
                    ['attribute' => 'name', 'like' => '%' . $search . '%'],
                    ['attribute' => 'sku', 'like' => trim($search)]
                ]);
            }
        }
        $collection->setFlag($stockFlag, false);
        $this->setCollection($collection);
    }
}