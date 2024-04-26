<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_ProductAlert
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\ProductAlert\Model;

use Magento\Catalog\Api\CategoryRepositoryInterface;

use Magento\Framework\Api\AttributeValueFactory;

class Product extends \Magento\Catalog\Model\Product
{
    protected $productCollection;
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        AttributeValueFactory $customAttributeFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Api\ProductAttributeRepositoryInterface $metadataService,
        \Magento\Catalog\Model\Product\Url $url,
        \Magento\Catalog\Model\Product\Link $productLink,
        \Magento\Catalog\Model\Product\Configuration\Item\OptionFactory $itemOptionFactory,
        \Magento\CatalogInventory\Api\Data\StockItemInterfaceFactory $stockItemFactory,
        \Magento\Catalog\Model\Product\OptionFactory $catalogProductOptionFactory,
        \Magento\Catalog\Model\Product\Visibility $catalogProductVisibility,
        \Magento\Catalog\Model\Product\Attribute\Source\Status $catalogProductStatus,
        \Magento\Catalog\Model\Product\Media\Config $catalogProductMediaConfig,
        \Magento\Catalog\Model\Product\Type $catalogProductType,
        \Magento\Framework\Module\Manager $moduleManager,
        \Magento\Catalog\Helper\Product $catalogProduct,
        \Magento\Catalog\Model\ResourceModel\Product $resource,
        \Magento\Catalog\Model\ResourceModel\Product\Collection $resourceCollection,
        \Magento\Framework\Data\CollectionFactory $collectionFactory,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\Indexer\IndexerRegistry $indexerRegistry,
        \Magento\Catalog\Model\Indexer\Product\Flat\Processor $productFlatIndexerProcessor,
        \Magento\Catalog\Model\Indexer\Product\Price\Processor $productPriceIndexerProcessor,
        \Magento\Catalog\Model\Indexer\Product\Eav\Processor $productEavIndexerProcessor,
        CategoryRepositoryInterface $categoryRepository,
        \Magento\Catalog\Model\Product\Image\CacheFactory $imageCacheFactory,
        \Magento\Catalog\Model\ProductLink\CollectionProvider $entityCollectionProvider,
        \Magento\Catalog\Model\Product\LinkTypeProvider $linkTypeProvider,
        \Magento\Catalog\Api\Data\ProductLinkInterfaceFactory $productLinkFactory,
        \Magento\Catalog\Api\Data\ProductLinkExtensionFactory $productLinkExtensionFactory,
        \Magento\Catalog\Model\Product\Attribute\Backend\Media\EntryConverterPool $mediaGalleryEntryConverterPool,
        \Magento\Framework\Api\DataObjectHelper $dataObjectHelper,
        \Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface $joinProcessor,
        \Magedelight\ProductAlert\Model\ResourceModel\Product\Collection $productCollection,
        array $data = []
    ) {
            parent::__construct($context, $registry, $extensionFactory, $customAttributeFactory, $storeManager, $metadataService, $url, $productLink, $itemOptionFactory, $stockItemFactory, $catalogProductOptionFactory, $catalogProductVisibility, $catalogProductStatus, $catalogProductMediaConfig, $catalogProductType, $moduleManager, $catalogProduct, $resource, $resourceCollection, $collectionFactory, $filesystem, $indexerRegistry, $productFlatIndexerProcessor, $productPriceIndexerProcessor, $productEavIndexerProcessor, $categoryRepository, $imageCacheFactory, $entityCollectionProvider, $linkTypeProvider, $productLinkFactory, $productLinkExtensionFactory, $mediaGalleryEntryConverterPool, $dataObjectHelper, $joinProcessor, $data);
            $this->productCollection = $productCollection;
    }
    public function getCollection()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        return $this->productCollection;
    }

    protected function _construct()
    {
        $this->_init('Magento\Catalog\Model\ResourceModel\Product');
    }
}
