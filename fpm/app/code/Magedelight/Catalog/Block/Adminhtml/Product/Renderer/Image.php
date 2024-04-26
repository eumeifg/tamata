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
namespace Magedelight\Catalog\Block\Adminhtml\Product\Renderer;

class Image extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    protected $_storeManager;

    protected $_productRepository;

    protected $_scopeConfig;

    /**
     * @var \Magento\Framework\Filesystem\Driver\File
     */
    protected $_file;

    /**
     * Image constructor.
     * @param \Magento\Backend\Block\Context $context
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Magento\Catalog\Helper\Image $imageHelper
     * @param \Magento\Catalog\Model\Product\Media\Config $mediaConfig
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Filesystem\DirectoryList $directorylist
     * @param \Magento\Framework\Filesystem\Driver\File $file
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Catalog\Helper\Image $imageHelper,
        \Magento\Catalog\Model\Product\Media\Config $mediaConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Filesystem\DirectoryList $directorylist,
        \Magento\Framework\Filesystem\Driver\File $file,
        array $data = []
    ) {
        $this->_productRepository = $productRepository;
        $this->imageHelper = $imageHelper;
        $this->mediaConfig = $mediaConfig;
        $this->directorylist = $directorylist;
        $this->_storeManager = $storeManager;
        $this->_file = $file;
        $this->_scopeConfig = $context->getScopeConfig();
        parent::__construct($context, $data);
    }

    /**
     * @param \Magento\Framework\DataObject $row
     * @return string
     * @throws \Magento\Framework\Exception\FileSystemException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        $mediaDirectory = $this->_storeManager->getStore()->getBaseUrl(
            \Magento\Framework\UrlInterface::URL_TYPE_MEDIA
        );

        $placeHolderImage = $mediaDirectory . 'catalog/product/placeholder/'
            . $this->getPlaceholderImage('small_image');

        $productId = $row['marketplace_product_id'];

        // if ($productId = $row['marketplace_product_id']) {
        $coreProduct = $this->_productRepository->getById($productId);
        $image = $this->imageHelper->init(
            $coreProduct,
            'category_page_grid'
        )->setImageFile($coreProduct->getSmallImage())->resize(50);

        $path = $this->directorylist->getPath('media') . '/'
                . $this->mediaConfig->getMediaPath($coreProduct->getSmallImage());

        if ($coreProduct->getImage()) {
            if ($this->_file->isExists($path)) {
                return '<img src="' . $image->getUrl() . '" />';
            } else {
                return '<img src="' . $placeHolderImage . '" width="50"/>';
            }
        } else {
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $product = $objectManager->create(
                \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable::class
            )->getParentIdsByChild($productId);
            if (isset($product[0])) {
                $parentProductId = $product[0];
                $coreParentProduct = $this->_productRepository->getById($parentProductId);
                $parentImage = $this->imageHelper->init(
                    $coreParentProduct,
                    'category_page_grid'
                )->setImageFile($coreParentProduct->getSmallImage())->resize(50);
                $parentPath = $this->directorylist->getPath('media') . '/'
                    . $this->mediaConfig->getMediaPath($coreParentProduct->getSmallImage());
                if ($coreParentProduct->getImage()) {
                    if ($this->_file->isExists($parentPath)) {
                        return '<img src="' . $parentImage->getUrl() . '" />';
                    } else {
                        return '<img src="' . $placeHolderImage . '" width="50"/>';
                    }
                }
            }
            return '<img src="' . $placeHolderImage . '" width="50"/>';
        }
        // }

        return '<img src="' . $placeHolderImage . '" width="50"/>';
    }

    /**
     * @param string $placeHolderImageType
     * @return mixed
     */
    public function getPlaceholderImage($placeHolderImageType = 'small_image')
    {
        $configPath = 'catalog/placeholder/' . $placeHolderImageType . '_placeholder';
        $value = $this->_scopeConfig->getValue($configPath, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        return $value;
    }
}
