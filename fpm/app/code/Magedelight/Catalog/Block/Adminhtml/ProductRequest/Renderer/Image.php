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
namespace Magedelight\Catalog\Block\Adminhtml\ProductRequest\Renderer;

class Image extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    const IMAGE_WIDTH = 50;
    const IMAGE_HEIGHT = 50;
    
    /**
     * @var \Magento\Catalog\Model\Product\Media\Config
     */
    protected $_config;

    /**
     * @var \Magento\Framework\App\Filesystem\DirectoryList
     */
    protected $_directoryList;

    /**
     * @var \Magento\Catalog\Helper\Image
     */
    protected $_imageHelper;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $_productRepository;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var \Magento\Framework\Filesystem\Driver\File
     */
    protected $_file;

    /**
     * Image constructor.
     * @param \Magento\Backend\Block\Context $context
     * @param \Magento\Framework\Json\Decoder $jsonDecoder
     * @param \Magento\Catalog\Model\Product\Media\Config $config
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Magento\Catalog\Helper\Image $imageHelper
     * @param \Magento\Framework\App\Filesystem\DirectoryList $directoryList
     * @param \Magento\Framework\Filesystem\Driver\File $file
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Magento\Framework\Json\Decoder $jsonDecoder,
        \Magento\Catalog\Model\Product\Media\Config $config,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Catalog\Helper\Image $imageHelper,
        \Magento\Framework\App\Filesystem\DirectoryList $directoryList,
        \Magento\Framework\Filesystem\Driver\File $file,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_jsonDecoder = $jsonDecoder;
        $this->_productRepository = $productRepository;
        $this->_imageHelper = $imageHelper;
        $this->_directoryList = $directoryList;
        $this->_config = $config;
        $this->_file = $file;
    }

    /**
     * @param \Magento\Framework\DataObject $row
     * @return string
     * @throws \Magento\Framework\Exception\FileSystemException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        $imagewidth = self::IMAGE_WIDTH;
        $imageheight = self::IMAGE_HEIGHT;

        if (!empty($row['base_image'])) {
            if ($this->_file->isExists($this->getAbsolutePath($row['base_image']))) {
                return '<img src="' . $this->getImageUrl($row['base_image']) . '" width="' . $imagewidth . '"/>';
            } elseif (!empty($row['base_image'])) {
                if ($this->_file->isExists($this->getAbsoluteBasePath($row['base_image']))) {
                    return '<img src="' . $this->getBaseImageUrl($row['base_image']) .
                        '" width="' . $imagewidth . '"/>';
                } else {
                    return '<img src="' . $this->_imageHelper->getDefaultPlaceholderUrl('thumbnail') .
                        '" width="' . $imagewidth . '"/>';
                }
            } else {
                return '<img src="' . $this->_imageHelper->getDefaultPlaceholderUrl('thumbnail') .
                    '" width="' . $imagewidth . '"/>';
            }
        } else {
            if (!empty($row['marketplace_product_id'])) {
                $_product = $this->_productRepository->getById($row['marketplace_product_id']);
                $image_url = $this->_imageHelper
                    ->init($_product, 'category_page_list')
                    ->setImageFile($_product->getImage())
                    ->resize($imagewidth, $imageheight)
                    ->getUrl();
                return '<img src="' . $image_url . '" width="' . $imagewidth . '"/>';
            } else {
                return '<img src="' . $this->_imageHelper->getDefaultPlaceholderUrl('thumbnail') .
                    '" width="' . $imagewidth . '"/>';
            }
        }
    }

    /**
     * @param $modelImg
     * @return null|string
     */
    public function getImageUrl($modelImg)
    {
        $imagePath = null;
        if ($modelImg) {
            if ($modelImg) {
                $baseImage = $this->_jsonDecoder->decode($modelImg);
                if (is_array($baseImage)) {
                    $baseImage = $baseImage['file'];
                }
                $imagePath = $this->_urlBuilder->getBaseUrl(
                    ['_type' => \Magento\Framework\UrlInterface::URL_TYPE_MEDIA]
                ) . $this->_config->getBaseTmpMediaPath() . $this->getFilenameFromTmp($baseImage);
            }
            return $imagePath;
        }
    }

    public function getBaseImageUrl($modelImg)
    {
        $imagePath = null;
        if ($modelImg) {
            if ($modelImg) {
                $baseImage = $this->_jsonDecoder->decode($modelImg);
                if (is_array($baseImage)) {
                    $baseImage = $baseImage['file'];
                }
                $imagePath = $this->_urlBuilder->getBaseUrl(
                    ['_type' => \Magento\Framework\UrlInterface::URL_TYPE_MEDIA]
                ) . $this->_config->getBaseMediaPath() . $this->getFilenameFromTmp($baseImage);
            }
            return $imagePath;
        }
    }

    /**
     * @param $file
     * @return string
     */
    protected function getFilenameFromTmp($file)
    {
        return strrpos($file, '.tmp') == strlen($file) - 4 ? substr($file, 0, strlen($file) - 4) : $file;
    }

    /**
     * @param $modelImg
     * @return null|string
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function getAbsolutePath($modelImg)
    {
        $imagePath = null;
        if ($modelImg) {
            if ($modelImg) {
                $baseImage = $this->_jsonDecoder->decode($modelImg);
                if (is_array($baseImage)) {
                    $baseImage = $baseImage['file'];
                }
                $imagePath = $this->_directoryList->getPath('media') . DIRECTORY_SEPARATOR .
                    $this->_config->getBaseTmpMediaPath() . $this->getFilenameFromTmp($baseImage);
            }
            return $imagePath;
        }
    }

    public function getAbsoluteBasePath($modelImg)
    {
        $imagePath = null;
        if ($modelImg) {
            if ($modelImg) {
                $baseImage = $this->_jsonDecoder->decode($modelImg);
                if (is_array($baseImage)) {
                    $baseImage = $baseImage['file'];
                }
                $imagePath = $this->_directoryList->getPath('media') . DIRECTORY_SEPARATOR .
                    $this->_config->getBaseMediaPath() . $this->getFilenameFromTmp($baseImage);
            }
            return $imagePath;
        }
    }
}
