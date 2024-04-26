<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_AdvancedReview
 */


namespace Amasty\AdvancedReview\Model;

use Amasty\AdvancedReview\Helper\ImageHelper;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\MediaStorage\Model\File\Uploader;

class ImageUploader
{
    /**
     * @var \Magento\Framework\Filesystem
     */
    private $filesystem;

    /**
     * @var \Magento\MediaStorage\Model\File\UploaderFactory
     */
    private $fileUploaderFactory;

    /**
     * @var \Magento\Framework\Image\AdapterFactory
     */
    private $adapterFactory;

    /**
     * @var \Magento\Framework\Filesystem\Io\File
     */
    private $ioFile;

    public function __construct(
        \Magento\Framework\Filesystem $filesystem,
        \Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory,
        \Magento\Framework\Image\AdapterFactory $adapterFactory,
        \Magento\Framework\Filesystem\Io\File $ioFile
    ) {
        $this->filesystem = $filesystem;
        $this->fileUploaderFactory = $fileUploaderFactory;
        $this->adapterFactory = $adapterFactory;
        $this->ioFile = $ioFile;
    }

    public function execute(array $file, $isTmp = false)
    {
        $path = $this->filesystem->getDirectoryRead(
            DirectoryList::MEDIA
        )->getAbsolutePath(
            $isTmp ? ImageHelper::IMAGE_TMP_PATH : ImageHelper::IMAGE_PATH
        );
        $this->ioFile->checkAndCreateFolder($path);

        /** @var $uploader Uploader */
        $uploader = $this->fileUploaderFactory->create(
            ['fileId' => $file]
        );
        $imageAdapter = $this->adapterFactory->create();
        $uploader->addValidateCallback('catalog_product_image', $imageAdapter, 'validateUploadFile');
        $uploader->setAllowedExtensions(['jpg', 'jpeg', 'gif', 'png']);
        $uploader->setAllowRenameFiles(true);
        $uploader->setFilesDispersion(true);
        $result = $uploader->save($path);
        $this->trim($result);

        return $result;
    }

    public function copy(string $imagePath)
    {
        $path = $this->filesystem->getDirectoryRead(
            DirectoryList::MEDIA
        )->getAbsolutePath(
            ImageHelper::IMAGE_TMP_PATH
        );

        $from = $path . $imagePath;
        if ($this->ioFile->fileExists($from)) {
            $realPath = $this->filesystem->getDirectoryRead(
                DirectoryList::MEDIA
            )->getAbsolutePath(
                ImageHelper::IMAGE_PATH
            );

            $counter = 0;
            while ($this->ioFile->fileExists($realPath . $imagePath)) {
                $imagePathArray = explode('.', $imagePath);
                $imagePathArray[0] .= $counter++;
                $imagePath = implode('.', $imagePathArray);
            }

            $this->ioFile->checkAndCreateFolder($this->ioFile->dirname($realPath . $imagePath));
            if ($this->ioFile->mv($from, $realPath . $imagePath)) {
                return $imagePath;
            }
        }

        return false;
    }

    /**
     * Fix for magento 2114 - setup upgrade
     * @param $result
     */
    private function trim($result)
    {
        if (isset($result['path']) && $result['file']) {
            $path = rtrim($result['path'], '/') . $result['file'];
            $this->ioFile->chmod($path, 0777);
        }
    }
}
