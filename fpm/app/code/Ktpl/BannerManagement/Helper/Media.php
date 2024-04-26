<?php
/*
 * Copyright © 2019 Krish Technolabs. All rights reserved.
 * See COPYING.txt for license details
 */

namespace Ktpl\BannerManagement\Helper;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Filesystem;
use Magento\Framework\Image\AdapterFactory;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\UrlInterface;
use Magento\MediaStorage\Model\File\UploaderFactory;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;
use Ktpl\BannerManagement\Helper\Image;

/**
 * Class Media
 *
 * @package Ktpl\BannerManagement\Helper
 */
class Media extends AbstractData
{
    const TEMPLATE_MEDIA_PATH = 'ktpl';

    /**
     * @var \Magento\Framework\Filesystem\Directory\ReadInterface
     */
    protected $mediaDirectory;

    /**
     * @var \Magento\MediaStorage\Model\File\UploaderFactory
     */
    protected $uploaderFactory;

    /**
     * @var \Magento\Framework\Image\AdapterFactory
     */
    protected $imageFactory;

    /**
     * Media constructor.
     *
     * @param Context                $context
     * @param ObjectManagerInterface $objectManager
     * @param StoreManagerInterface  $storeManager
     * @param Filesystem             $filesystem
     * @param UploaderFactory        $uploaderFactory
     * @param AdapterFactory         $imageFactory
     *
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function __construct(
        Context $context,
        ObjectManagerInterface $objectManager,
        StoreManagerInterface $storeManager,
        Filesystem $filesystem,
        UploaderFactory $uploaderFactory,
        AdapterFactory $imageFactory
    ) {
        parent::__construct($context, $objectManager, $storeManager);

        $this->mediaDirectory = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $this->uploaderFactory = $uploaderFactory;
        $this->imageFactory = $imageFactory;
        $this->_filesystem = $filesystem;
    }

    /**
     * @param $data
     * @param string $fileName
     * @param string $type
     * @param null   $oldImage
     *
     * @return $this
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function uploadImage(&$data, $fileName = 'image', $type = '', $oldImage = null)
    {
        if (isset($data[$fileName]) && isset($data[$fileName]['delete']) && $data[$fileName]['delete']) {
            if ($oldImage) {
                $this->removeImage($oldImage, $type);
            }
            $data['image'] = '';
        } else {
            try {
                $uploader = $this->uploaderFactory->create(['fileId' => $fileName]);

                if ($data['type'] == \Ktpl\BannerManagement\Model\Config\Source\Type::VIDEO) {
                    $uploader->setAllowedExtensions(['mp4', 'avi', 'swf']);
                } else {
                    $uploader->setAllowedExtensions(['jpg', 'jpeg', 'gif', 'png']);
                }
                $uploader->setAllowRenameFiles(true);
                $uploader->setFilesDispersion(true);
                $uploader->setAllowCreateFolders(true);

                $path = $this->getBaseMediaPath($type);

                $image = $uploader->save(
                    $this->mediaDirectory->getAbsolutePath($path)
                );

                if ($oldImage) {
                    $this->removeImage($oldImage, $type);
                }

                $data['image'] = $this->_prepareFile($image['file']);
            } catch (\Exception $e) {
                $data['image'] = isset($data['image']['value']) ? $data['image']['value'] : '';
                return false;
            }
        }

        return $this;
    }

    /**
     * @param $file
     * @param $type
     *
     * @return $this
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function removeImage($file, $type)
    {
        $image = $this->getMediaPath($file, $type);
        if ($this->mediaDirectory->isFile($image)) {
            $this->mediaDirectory->delete($image);
        }

        return $this;
    }

    /**
     * @param $file
     * @param string $type
     *
     * @return string
     */
    public function getMediaPath($file, $type = '')
    {
        return $this->getBaseMediaPath($type) . '/' . $this->_prepareFile($file);
    }

    /**
     * @param string $type
     *
     * @return string
     */
    public function getBaseMediaPath($type = '')
    {
        return trim($type, '/');
    }

    /**
     * @param string $file
     *
     * @return string
     */
    protected function _prepareFile($file)
    {
        return ltrim(str_replace('\\', '/', $file), '/');
    }

    /**
     * @param $file
     * @param $size
     * @param string $type
     * @param bool   $keepRatio
     *
     * @return string
     */
    public function resizeImage($file, $size, $type = '', $keepRatio = true)
    {
        $image = $this->getMediaPath($file, $type);
        if (!($imageSize = $this->correctImageSize($size))) {
            return $this->getMediaUrl($image);
        }
        list($width, $height) = $imageSize;

        $resizeImage = $this->getMediaPath($file, ($type ? $type . '/' : '') . 'resize/' . $width . 'x' . $height);

        /**
 * @var \Magento\Framework\Filesystem\Directory\WriteInterface $mediaDirectory
*/
        $mediaDirectory = $this->getMediaDirectory();
        if (!$mediaDirectory->isFile($resizeImage)) {
            try {
                $imageResize = $this->imageFactory->create();
                $imageResize->open($mediaDirectory->getAbsolutePath($image));
                $imageResize->constrainOnly(true);
                $imageResize->keepTransparency(true);
                $imageResize->keepFrame(false);
                $imageResize->keepAspectRatio($keepRatio);
                $imageResize->resize($width, $height);
                $imageResize->save($mediaDirectory->getAbsolutePath($resizeImage));

                $image = $resizeImage;
            } catch (\Exception $e) {
                $this->objectManager->get(LoggerInterface::class)->critical($e->getMessage());
            }
        } else {
            $image = $resizeImage;
        }

        return $this->getMediaUrl($image);
    }

    /**
     * @param $size
     *
     * @return array|bool
     */
    protected function correctImageSize($size)
    {
        if (!$size) {
            return false;
        }

        if (strpos($size, 'x') === false) {
            $width = $height = (int)$size;
        } else {
            list($width, $height) = explode('x', $size);
        }

        if (!$width && !$height) {
            return false;
        }

        return [(int)$width ?: null, (int)$height ?: null];
    }

    /**
     * @param string $file
     *
     * @return string
     */
    public function getMediaUrl($file)
    {
        return $this->getBaseMediaUrl() . '/' . $this->_prepareFile($file);
    }

    /**
     * @return string
     */
    public function getBaseMediaUrl()
    {
        return rtrim($this->storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA), '/');
    }

    /**
     * @return \Magento\Framework\Filesystem\Directory\WriteInterface
     */
    public function getMediaDirectory()
    {
        return $this->mediaDirectory;
    }

    /**
     * @param $path
     *
     * @return $this
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function removePath($path)
    {
        $pathMedia = $this->mediaDirectory->getRelativePath($path);
        if ($this->mediaDirectory->isDirectory($pathMedia)) {
            $this->mediaDirectory->delete($path);
        }

        return $this;
    }

    public function getAbsolutePath()
    {
        return $this->_filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath();
    }
}
