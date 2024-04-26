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
namespace Magedelight\Catalog\Helper;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\UrlInterface;

class Image extends AbstractHelper
{

    /**
     * Default quality value (for JPEG images only).
     *
     * @var int
     */
    protected $_quality = 100;
    protected $_keepAspectRatio = false;
    protected $_keepFrame = false;
    protected $_keepTransparency = true;
    protected $_constrainOnly = true;
    protected $_backgroundColor = [255, 255, 255];
    protected $_baseFile;
    protected $_isBaseFilePlaceholder;
    protected $_newFile;
    protected $_processor;
    protected $_destinationSubdir;
    protected $_angle;
    protected $_watermarkFile;
    protected $_watermarkPosition;
    protected $_watermarkWidth;
    protected $_watermarkHeight;
    protected $_watermarkImageOpacity = 0;
    protected $_folder = null;

    /**
     * @var \Magento\Framework\App\Helper\Context
     */
    protected $context;

    /**
     * @var \Magento\Framework\Image\Factory
     */
    protected $imageFactory;

    /**
     * @var \Magento\Framework\Filesystem
     */
    protected $filesystem;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\Image\AdapterFactory
     */
    protected $imageFileFactory;

    /**
     * @var \Magento\Framework\Image\Factory
     */
    protected $_imageFactory;

    /**
     * @var \Magento\Framework\Filesystem\Directory\WriteInterface
     */
    protected $_mediaDirectory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Framework\Filesystem
     */
    protected $_filesystem;

    /**
     * @var \Magento\Framework\Filesystem\Driver\File
     */
    protected $file;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\Image\Factory $imageFactory
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Image\AdapterFactory $imageFileFactory
     * @param \Magento\Framework\Filesystem\Driver\File $file
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\Image\Factory $imageFactory,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Image\AdapterFactory $imageFileFactory,
        \Magento\Framework\Filesystem\Driver\File $file
    ) {
        $this->_imageFactory = $imageFactory;
        $this->_mediaDirectory = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $this->_storeManager = $storeManager;
        $this->_filesystem = $filesystem;
        $this->imageFileFactory = $imageFileFactory;
        $this->file = $file;
        parent::__construct($context);
    }

    /**
     * @param $image
     * @param $width
     * @param $height
     * @return \Magento\Framework\UrlInterface | Boolean
     * @throws \Magento\Framework\Exception\FileSystemException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function resize($image, $width = null, $height = null)
    {
        $folder = $this->getFolderPath();
        if (!$this->_isImageExist($image)) {
            return false;
        }

        $dimension = '';
        if (!empty($width)) {
            $dimension = "/" . $width;
        } elseif (!empty($height)) {
            $dimension = "/" . $height;
        }
        if ($this->_isImageExist($dimension . $image)) {
            return $this->_storeManager->getStore()->getBaseUrl(
                UrlInterface::URL_TYPE_MEDIA
            ) . $folder . $dimension . $image;
        }

        $resizedURL = $this->_getResizedImageUrl($folder, $dimension, $image, $width, $height);
        return $resizedURL;
    }

    /**
     * @param $folder
     * @param $dimension
     * @param $image
     * @param $width
     * @param $height
     * @return Magento\Framework\UrlInterface|string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */

    protected function _getResizedImageUrl($folder, $dimension, $image, $width, $height)
    {
        $absolutePath = $this->_filesystem->getDirectoryRead(
            DirectoryList::MEDIA
        )->getAbsolutePath($folder) . $image;
        $imageResized = $this->_filesystem->getDirectoryRead(
            DirectoryList::MEDIA
        )->getAbsolutePath($folder . $dimension) . $image;
        $imageResize = $this->imageFileFactory->create();
        $imageResize->open($absolutePath);
        $imageResize->constrainOnly(true);
        $imageResize->keepTransparency(true);
        $imageResize->keepFrame(false);
        $imageResize->keepAspectRatio(true);
        $imageResize->resize($width, $height);
        $destination = $imageResized;

        $imageResize->save($destination);

        return $resizedURL = $this->_storeManager->getStore()
                ->getBaseUrl(UrlInterface::URL_TYPE_MEDIA) . $folder . $dimension . $image;
    }

    /**
     * @param $image
     * @return Boolean
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    protected function _isImageExist($image)
    {
        if ($image == '' || $image == null) {
            return false;
        }
        $folder = $this->getFolderPath();
        $reader = $this->_filesystem->getDirectoryRead(DirectoryList::MEDIA);
        if ($this->file->isExists($reader->getAbsolutePath($reader->getAbsolutePath($folder . $image)))) {
            return true;
        }
        return false;
    }

    /**
     * @return \Magento\Framework\Image
     */
    public function getImageProcessor()
    {
        $folder = $this->getFolderPath();
        $filename = $this->_baseFile ? $this->_mediaDirectory->getAbsolutePath($folder . $this->_baseFile) : null;
        $this->_processor = $this->_imageFactory->create($filename);
        $this->_processor->keepAspectRatio($this->_keepAspectRatio);
        $this->_processor->keepFrame($this->_keepFrame);
        $this->_processor->keepTransparency($this->_keepTransparency);
        $this->_processor->constrainOnly($this->_constrainOnly);
        $this->_processor->backgroundColor($this->_backgroundColor);
        $this->_processor->quality($this->_quality);
        $this->_processor->resize($this->_width, $this->_height);
        return $this->_processor;
    }

    /**
     * @param $baseFile
     * @return $this
     */
    public function init($baseFile)
    {
        $this->_newFile = '';
        $this->_baseFile = $baseFile;
        return $this;
    }

    /**
     * @return $this
     */
    public function saveFile()
    {
        $filename = $this->_mediaDirectory->getAbsolutePath($this->_newFile);
        $this->getImageProcessor()->save($filename);
        return $this;
    }

    /**
     * @param $filename
     * @return Boolean
     */
    protected function _fileExists($filename)
    {
        if ($this->_mediaDirectory->isFile($filename)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @return Boolean
     */
    public function isCached()
    {
        if (is_string($this->_newFile)) {
            return $this->_fileExists($this->_newFile);
        }
    }

    /**
     * @return \Magento\Framework\UrlInterface|string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function __toString()
    {
        $url = "";
        if ($this->_baseFile) {
            $url = $this->_storeManager->getStore()->getBaseUrl(
                UrlInterface::URL_TYPE_MEDIA
            ) . $this->_newFile;
        }
        return $url;
    }

    /**
     * @param $folder
     */
    public function setFolderPath($folder)
    {
        $this->_folder = $folder;
    }

    /**
     * @return String
     */
    public function getFolderPath()
    {
        return $this->_folder;
    }
}
