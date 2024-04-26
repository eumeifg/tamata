<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Catalog
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Catalog\Model\Product\Type;

use Magento\Catalog\Api\CategoryLinkManagementInterface;
use Magento\Catalog\Controller\Adminhtml\Product;
use Magento\Catalog\Model\ProductFactory as MagentoProductFactory;
use Magento\ConfigurableProduct\Helper\Product\Options\Factory;
use Magedelight\Catalog\Model\ResourceModel\ProductRequestStore\CollectionFactory as ProductRequestStoreCollectionFactory;
use Magedelight\Catalog\Model\ResourceModel\ProductRequestWebsite\CollectionFactory as ProductRequestWebsiteCollectionFactory;

/**
 * @api
 * Abstract model for product type implementation
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 100.0.2
 */
abstract class AbstractType
{

    /**
     * @var \Magento\Framework\Json\Decoder
     */
    protected $jsonDecoder;

    /**
     * @var \Magento\Framework\Json\Encoder
     */
    protected $jsonEncoder;

    /**
     * @var Initialization\Helper
     */
    protected $initializationHelper;

    /**
     * @var \Magento\Catalog\Model\Product\TypeTransitionManager
     */
    protected $productTypeManager;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $_productRepository;

    /**
     * @var \Magento\Framework\Math\Random
     */
    protected $_mathRandom;

    /**
     * @var Factory
     */
    protected $optionFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Catalog\Api\CategoryLinkManagementInterface
     */
    protected $categoryLinkManagement;

    /**
     * @var MagentoProductFactory
     */
    protected $productFactory;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product
     */
    protected $productResourceModel;

    /**
     * @var \Magedelight\Catalog\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magedelight\Catalog\Model\ResourceModel\ProductRequest\CollectionFactory
     */
    protected $_productRequestCollectionFactory;

    /**
     * @var ProductRequestStoreCollectionFactory
     */
    protected $_productRequestStoreCollectionFactory;

    /**
     * @var ProductRequestWebsiteCollectionFactory
     */
    protected $_productRequestWebsiteCollectionFactory;

    /**
     * @var \Magedelight\Catalog\Model\VendorProduct\Type\Simple
     */
    protected $_simpleVendorProduct;

    /**
     * @var \Magedelight\Backend\Log\LoggerInterface
     */
    protected $_logger;

    /**
     * @var \Magento\Framework\Filter\FilterManager
     */
    protected $filterManager;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * @param \Magento\Framework\Json\Decoder $jsonDecoder
     * @param \Magento\Framework\Json\Encoder $jsonEncoder
     * @param \Magento\Catalog\Controller\Adminhtml\Product\Initialization\Helper $initializationHelper
     * @param \Magento\Catalog\Model\Product\TypeTransitionManager $productTypeManager
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Magento\Framework\Math\Random $mathRandom
     * @param Factory $optionFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param CategoryLinkManagementInterface $categoryLinkManagement
     * @param MagentoProductFactory $productFactory
     * @param \Magento\Catalog\Model\ResourceModel\Product $productResourceModel
     * @param \Magedelight\Catalog\Helper\Data $helper
     * @param \Magedelight\Catalog\Model\ResourceModel\ProductRequest\CollectionFactory $productRequestCollectionFactory
     * @param ProductRequestStoreCollectionFactory $productRequestStoreCollectionFactory
     * @param ProductRequestWebsiteCollectionFactory $productRequestWebsiteCollectionFactory
     * @param \Magedelight\Catalog\Model\VendorProduct\Type\Simple $simpleVendorProduct
     * @param \Magento\Framework\Filter\FilterManager $filterManager
     * @param \Magedelight\Backend\Log\LoggerInterface $logger
     * @param \Magento\Framework\App\RequestInterface $request
     */
    public function __construct(
        \Magento\Framework\Json\Decoder $jsonDecoder,
        \Magento\Framework\Json\Encoder $jsonEncoder,
        Product\Initialization\Helper $initializationHelper,
        \Magento\Catalog\Model\Product\TypeTransitionManager $productTypeManager,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Framework\Math\Random $mathRandom,
        Factory $optionFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        CategoryLinkManagementInterface $categoryLinkManagement,
        MagentoProductFactory $productFactory,
        \Magento\Catalog\Model\ResourceModel\Product $productResourceModel,
        \Magedelight\Catalog\Helper\Data $helper,
        \Magedelight\Catalog\Model\ResourceModel\ProductRequest\CollectionFactory $productRequestCollectionFactory,
        ProductRequestStoreCollectionFactory $productRequestStoreCollectionFactory,
        ProductRequestWebsiteCollectionFactory $productRequestWebsiteCollectionFactory,
        \Magedelight\Catalog\Model\VendorProduct\Type\Simple $simpleVendorProduct,
        \Magento\Framework\Filter\FilterManager $filterManager,
        \Magedelight\Backend\Log\LoggerInterface $logger,
        \Magento\Framework\App\RequestInterface $request
    ) {
        $this->jsonDecoder = $jsonDecoder;
        $this->jsonEncoder = $jsonEncoder;
        $this->initializationHelper = $initializationHelper;
        $this->productTypeManager = $productTypeManager;
        $this->_productRepository = $productRepository;
        $this->_mathRandom = $mathRandom;
        $this->optionFactory = $optionFactory;
        $this->_storeManager = $storeManager;
        $this->getCategoryLinkManagement = $categoryLinkManagement;
        $this->productFactory = $productFactory;
        $this->productResourceModel = $productResourceModel;
        $this->helper = $helper;
        $this->_productRequestCollectionFactory = $productRequestCollectionFactory;
        $this->_productRequestStoreCollectionFactory = $productRequestStoreCollectionFactory;
        $this->_productRequestWebsiteCollectionFactory = $productRequestWebsiteCollectionFactory;
        $this->_simpleVendorProduct = $simpleVendorProduct;
        $this->_logger = $logger;
        $this->filterManager = $filterManager;
        $this->request = $request;
    }

    /**
     * Build product based on user request
     *
     * @param RequestInterface $request
     * @return \Magento\Catalog\Model\Product
     */
    public function build($request)
    {
        $productId = (array_key_exists('id', $request)) ? (int)$request['id'] : null;
        /** @var $product \Magento\Catalog\Model\Product */
        $product = $this->productFactory->create();

        $product->setStoreId($request['store_id']);
        $product->setSku($request['product']['sku']);

        $typeId = $request['type'];
        if (!$productId && $typeId) {
            $product->setTypeId($typeId);
        }

        $product->setData('_edit_mode', true);

        if ($productId) {
            try {
                $product->load($productId);
            } catch (\Exception $e) {
                $product->setTypeId(\Magento\Catalog\Model\Product\Type::DEFAULT_TYPE);
            }
        }
        $product->setVisibility(
            \Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH
        );

        $setId = (int)$request['set'];
        if ($setId) {
            $product->setAttributeSetId($setId);
        }
        return $product;
    }

    /**
     * Notify customer when image was not deleted in specific case.
     * TODO: temporary workaround must be eliminated in MAGETWO-45306
     *
     * @param array $postData
     * @param int $productId
     * @return void
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function handleImageRemoveErrorProduct($postData, $productId)
    {
        if (isset($postData['product']['media_gallery']['images'])) {
            $removedImagesAmount = 0;
            foreach ($postData['product']['media_gallery']['images'] as $image) {
                if (!empty($image['removed'])) {
                    $removedImagesAmount++;
                }
            }
            if ($removedImagesAmount) {
                $expectedImagesAmount = count($postData['product']['media_gallery']['images']) - $removedImagesAmount;
                $product = $this->_productRepository->getById($productId);
                /* if ($expectedImagesAmount != count($product->getMediaGallery('images'))) {
                    $this->messageManager->addNotice(
                        __('The image cannot be removed as it has been assigned to the other image role')
                    );
                } */
            }
        }
    }

    protected function checkUrlKeyDuplicates($sku, $urlKey, $storeId = '')
    {
        $urlKey .= '.html';

        $connection = $this->productResourceModel->getConnection(
            \Magento\Framework\App\ResourceConnection::DEFAULT_CONNECTION
        );

        $tablename = $connection->getTableName('url_rewrite');
        $sql = $connection->select()->from(
            ['url_rewrite' => $connection->getTableName('url_rewrite')],
            ['request_path', 'store_id']
        )->joinLeft(
            ['cpe' => $connection->getTableName('catalog_product_entity')],
            "cpe.entity_id = url_rewrite.entity_id"
        )->where('request_path IN (?)', $urlKey)
                /* ->where('store_id IN (?)', $storeId) */
                ->where('cpe.sku not in (?)', $sku);

        $urlKeyDuplicates = $connection->fetchAssoc($sql);

        if (!empty($urlKeyDuplicates)) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Generate url key based on url_key entered by merchant or page title
     *
     * @param string $identifier
     * @return string
     * @api
     */
    public function generateUrlKey($urlKey = '')
    {
        $urlKey = trim($urlKey);
        return $this->filterManager->translitUrl($urlKey === '' || $urlKey === null ? '' : $urlKey);
    }
}
