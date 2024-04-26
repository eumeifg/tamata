<?php

namespace MDC\Catalog\Cron;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\ScopeInterface;
use MDC\Catalog\Block\Product\View\OpenInApp;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;

class CreateProductAppLink
{
    const PRODUCT_BATCH_COUNT = 'open_in_app/general/number';
    /**
     * @var Status
     */
    protected $productStatus;

    /**
     * @var Visibility
     */
    protected $productVisibility;

    /**
     * @var CollectionFactory
     */
    protected $_productCollectionFactory;

    /**
     * @var OpenInApp
     */
    protected $openInAppBlock;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @param Status $productStatus
     * @param Visibility $productVisibility
     * @param OpenInApp $openInAppBlock
     * @param ScopeConfigInterface $scopeConfig
     * @param ProductRepositoryInterface $productRepository
     * @param CollectionFactory $productCollectionFactory
     */
    public function __construct(
        Status $productStatus,
        Visibility $productVisibility,
        OpenInApp $openInAppBlock,
        ScopeConfigInterface $scopeConfig,
        ProductRepositoryInterface $productRepository,
        CollectionFactory $productCollectionFactory
    )
    {
        $this->productStatus = $productStatus;
        $this->productVisibility = $productVisibility;
        $this->openInAppBlock = $openInAppBlock;
        $this->scopeConfig = $scopeConfig;
        $this->productRepository = $productRepository;
        $this->_productCollectionFactory = $productCollectionFactory;
    }

    /**
     * @return Collection
     */
    public function getProductCollection(): Collection
    {
        $batchNo = $this->scopeConfig->getValue(self::PRODUCT_BATCH_COUNT, ScopeInterface::SCOPE_STORE);
        $batchNo = !empty($batchNo) ? $batchNo : 10;
        $collection = $this->_productCollectionFactory->create();
        $collection->addAttributeToSelect(['id', 'sku', 'app_link', 'status', 'product_url']);
        $collection->addAttributeToFilter('app_link', ['null' => true]);
        $collection->addAttributeToFilter('status', ['in' => $this->productStatus->getVisibleStatusIds()]);
        $collection->setVisibility($this->productVisibility->getVisibleInSiteIds());
        $collection->getSelect()->limit($batchNo);
        return $collection;
    }

    /**
     * @return void
     * @throws NoSuchEntityException
     */
    public function createProductAppLink()
    {
        $collection = $this->getProductCollection();

        if ($collection->getSize()) {
            /** @var Product $product */
            foreach ($collection as $product) {
                $productUrl = $product->getProductUrl();
                /*$productUrl = str_replace('http://tamata.local/', 'https://www.tamata.com/', $product->getProductUrl());*/
                $productId = $product->getId();

                $productUrlWithId = $productUrl."?productId=".$productId;
                $appLinkValue = $product->getAppLink();
                if (empty($appLinkValue)) {
                    $generatedDeepLink = $this->openInAppBlock->createDeepLinkForCurrentPorduct($productUrlWithId);
                    if ($generatedDeepLink) {
                        $repositoryProduct = $this->productRepository->getById($productId);
                        $repositoryProduct->setAppLink($generatedDeepLink);
                        $repositoryProduct->getResource()->saveAttribute($repositoryProduct, 'app_link');
                    }
                }
            }
        }
    }
}
