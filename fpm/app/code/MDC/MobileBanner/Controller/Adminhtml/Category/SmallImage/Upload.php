<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package MDC_MobileBanner
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace MDC\MobileBanner\Controller\Adminhtml\Category\SmallImage;

use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\App\Filesystem\DirectoryList;

class Upload extends \Magento\Backend\App\Action
{
    /**
     * Image uploader
     *
     * @var \Magento\Catalog\Model\ImageUploader
     */
    protected $imageUploader;

    /**
     * Uploader factory
     *
     * @var \Magento\MediaStorage\Model\File\UploaderFactory
     */
    private $uploaderFactory;

    /**
     * Media directory object (writable).
     *
     * @var \Magento\Framework\Filesystem\Directory\WriteInterface
     */
    protected $mediaDirectory;

    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * Core file storage database
     *
     * @var \Magento\MediaStorage\Helper\File\Storage\Database
     */
    protected $coreFileStorageDatabase;

    /**
     * Upload constructor.
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Catalog\Model\ImageUploader $imageUploader
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Catalog\Model\ImageUploader $imageUploader,
        \Magento\MediaStorage\Model\File\UploaderFactory $uploaderFactory,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Filesystem\Io\File $fileIo,
        \Magento\MediaStorage\Helper\File\Storage\Database $coreFileStorageDatabase
    )
    {
        parent::__construct($context);
        $this->imageUploader = $imageUploader;
        $this->uploaderFactory = $uploaderFactory;
        $this->filesystem = $filesystem;
        $this->mediaDirectory = $filesystem->getDirectoryWrite(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA);
        $this->storeManager = $storeManager;
        $this->coreFileStorageDatabase = $coreFileStorageDatabase;
        $this->fileIo = $fileIo;
    }

    /**
     * Upload file controller action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $dynamicrows = $this->_request->getFiles()['dynamic_rows_container'];
        $files = array();
        foreach ($dynamicrows as $key => $rows) {
            $files = $dynamicrows[$key];
        }
        try {
            $imageResult = $this->imageUploader->saveFileToTmpDir($files['mobile_banner_image']);
            // Upload image folder wise
            $imageName = $imageResult['name'];
            $firstName = substr($imageName, 0, 1);
            $secondName = substr($imageName, 1, 1);
            $basePath = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath() . 'mobile_banner/image/';
            $mediaRootDir = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath() . 'mobile_banner/image/' . $firstName . '/' . $secondName . '/';
            //echo $mediaRootDir;exit;
            if (!is_dir($mediaRootDir)) {
                $this->fileIo->mkdir($mediaRootDir, 0775);
            }
            // Set image name with new name, If image already exist
            $newImageName = $this->updateImageName($mediaRootDir, $imageName);
            $this->fileIo->mv($basePath . $imageName, $mediaRootDir . $newImageName);


            $imageResult['cookie'] = [
                'name' => $this->_getSession()->getName(),
                'value' => $this->_getSession()->getSessionId(),
                'lifetime' => $this->_getSession()->getCookieLifetime(),
                'path' => $this->_getSession()->getCookiePath(),
                'domain' => $this->_getSession()->getCookieDomain(),
            ];
            $mediaUrl = $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
            $imageResult['name'] = $newImageName;
            $imageResult['file'] =  $firstName . '/' . $secondName . '/' .$newImageName;
            $imageResult['url'] = $mediaUrl . 'mobile_banner/image/' . $firstName . '/' . $secondName . '/' . $newImageName;

            $newimagepath = 'mobile_banner/image/' . $firstName . '/' . $secondName . '/' . $newImageName;
            list($width, $height) = getimagesize($this->mediaDirectory->getAbsolutePath($newimagepath));
            $imageResult['width'] = $width;
            $imageResult['height'] = $height;
        } catch (\Exception $e) {
            $imageResult = ['error' => $e->getMessage(), 'errorcode' => $e->getCode()];
        }
        return $this->resultFactory->create(ResultFactory::TYPE_JSON)->setData($imageResult);
    }

    public function updateImageName($path, $file_name)
    {
        if ($position = strrpos($file_name, '.')) {
            $name = substr($file_name, 0, $position);
            $extension = substr($file_name, $position);
        } else {
            $name = $file_name;
        }
        $new_file_path = $path . '/' . $file_name;
        $new_file_name = $file_name;
        $count = 0;
        while (file_exists($new_file_path)) {
            $new_file_name = $name . '_' . $count . $extension;
            $new_file_path = $path . '/' . $new_file_name;
            $count++;
        }
        return $new_file_name;
    }
}
