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
namespace Magedelight\Catalog\Model\Gallery;

use Magento\Framework\App\Filesystem\DirectoryList;

class Upload implements \Magedelight\Catalog\Api\CatalogGalleryInterface
{

    /**
     * @var \Magedelight\Catalog\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var \Magento\Catalog\Model\Product\Media\Config
     */
    protected $mediaConfig;

    /**
     * @var \Magento\Framework\Filesystem
     */
    protected $fileSystem;

    /**
     * @var \Magedelight\Vendor\Model\VendorRegistry
     */
    protected $vendorRegistry;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $jsonHelper;

    /**
     * @var \Magento\Framework\Filesystem\Driver\File
     */
    protected $fileDriver;

    /**
     * @var \Magento\MediaStorage\Model\File\UploaderFactory
     */
    protected $uploaderFactory;

    /**
     * @var \Magento\Framework\Image\AdapterFactory
     */
    protected $adapterFactory;

    /**
     * @var \Magento\Framework\Filesystem\Directory\Read
     */
    protected $mediaDirectory;

    /**
     * @param \Magedelight\Backend\App\Action\Context $context
     * @param \Magento\Framework\Filesystem $fileSystem
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param \Magedelight\Vendor\Model\VendorRegistry $vendorRegistry
     * @param \Magento\Catalog\Model\Product\Media\Config $mediaConfig
     * @param \Magedelight\Catalog\Helper\Data $helper
     * @param \Magento\Framework\Filesystem\Driver\File $fileDriver
     * @param \Magento\MediaStorage\Model\File\UploaderFactory $uploaderFactory
     * @param \Magento\Framework\Image\AdapterFactory $adapterFactory
     * @param \Magento\Framework\Filesystem $filesystem
     */
    public function __construct(
        \Magedelight\Backend\App\Action\Context $context,
        \Magento\Framework\Filesystem $fileSystem,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magedelight\Vendor\Model\VendorRegistry $vendorRegistry,
        \Magento\Catalog\Model\Product\Media\Config $mediaConfig,
        \Magedelight\Catalog\Helper\Data $helper,
        \Magento\Framework\Filesystem\Driver\File $fileDriver,
        \Magento\MediaStorage\Model\File\UploaderFactory $uploaderFactory,
        \Magento\Framework\Image\AdapterFactory $adapterFactory,
        \Magento\Framework\Filesystem $filesystem
    ) {
        $this->request = $context->getRequest();
        $this->_objectManager = $context->getObjectManager();
        $this->fileSystem = $fileSystem;
        $this->mediaConfig = $mediaConfig;
        $this->jsonHelper = $jsonHelper;
        $this->vendorRegistry = $vendorRegistry;
        $this->helper = $helper;
        $this->fileDriver = $fileDriver;
        $this->uploaderFactory = $uploaderFactory;
        $this->adapterFactory = $adapterFactory;
        $this->mediaDirectory = $filesystem->getDirectoryRead(DirectoryList::MEDIA);
    }

    /**
     * {@inheritdoc}
     */
    public function upload($sellerId)
    {
        $_filesParam = $_FILES;
        /* This essential for check vendor with given id exist or not */
        $result = [];
        if (count($_filesParam) < 1 || !isset($_filesParam['bulk-image'])) {
            $result[]['file_upload_error'] = true;
            return $this->jsonHelper->jsonEncode($result);
        }
        $this->vendorRegistry->retrieve($sellerId);
        $filetypeImage = ['image/x-png', 'image/png', 'image/gif', 'image/jpeg', 'image/jpg'];
        $tmpFiles = is_array($_filesParam['bulk-image']['tmp_name']) ?
            $_filesParam['bulk-image']['tmp_name'][0] : $_filesParam['bulk-image']['tmp_name'];
        $filesType = is_array($_filesParam['bulk-image']['type']) ?
            $_filesParam['bulk-image']['type'][0] : $_filesParam['bulk-image']['type'];

        if (in_array($filesType, $filetypeImage) && $sellerId) {
            foreach ($_filesParam["bulk-image"] as $key => $val) {
                if (is_array($val)) {
                    $_filesParam["bulk-image"][$key] = $val[0];
                }
            }
            $width = $this->helper->getImageHeight();
            $height = $this->helper->getImageWidth();
            $width = empty($width) ? 250 : $width;
            $height = empty($height) ? 250 : $height;
            try {
                $_FILES = $_filesParam;
                $uploader = $this->uploaderFactory->create(['fileId' => 'bulk-image']);
                $uploader->setAllowedExtensions($this->helper->getAllowedExtensions());
                if ($this->validateSpecialCharacters($_filesParam['bulk-image']['tmp_name'])) {
                    if ($this->validateImageDimensions($tmpFiles, $width, $height)) {
                        if ($this->validateImageSize($tmpFiles)) {
                            /** @var \Magento\Framework\Image\Adapter\AdapterInterface $imageAdapter */
                            $imageAdapter = $this->adapterFactory->create();
                            $uploader->addValidateCallback(
                                'catalog_product_image',
                                $imageAdapter,
                                'validateUploadFile'
                            );
                            $uploader->setAllowRenameFiles(true);
                            $uploader->setFilesDispersion(true);
                            if (!$this->checkIfFileExists($_filesParam, $uploader)) {
                                $result = $uploader->save(
                                    $this->mediaDirectory->getAbsolutePath($this->mediaConfig->getBaseTmpMediaPath())
                                );
                                $result['url'] = $this->mediaConfig->getTmpMediaUrl($result['file']);
                                $result['file'] = $result['file'];
                                $result['file_upload_error'] = false;
                            } else {
                                $imageName = $_filesParam['bulk-image']['name'];
                                $firstName = substr($imageName, 0, 1);
                                $secondName = substr($imageName, 1, 1);
                                $mediaRootDir = $this->mediaDirectory->getAbsolutePath() . 'tmp/catalog/product/' . $firstName . '/' . $secondName . '/';
                                $newImageName = $this->updateImageName($mediaRootDir,$_filesParam['bulk-image']['name']);
                                $_filesParam['bulk-image']['name'] = $newImageName; 
                                $result = $uploader->save(
                                    $this->mediaDirectory->getAbsolutePath($this->mediaConfig->getBaseTmpMediaPath())
                                );
                                $result['url'] = $this->mediaConfig->getTmpMediaUrl($result['file']);
                                $result['file'] = $result['file'];
                                $result['file_upload_error'] = false;
                                // $result['file_upload_error'] = false;
                                // $result['file_upload_error'] = true;
                                // $result['error'] = __('The image ' . strtolower($_filesParam['bulk-image']['name']) . ' is already exist or added by other seller. Kindly rename it and try.');
                            }
                        } else {
                            $result = ['error' => 'File size is too big.', 'errorcode' => 2];
                        }
                    } else {
                        $result['file_name'] = $_filesParam['bulk-image']['name'];
                        $result['file_upload_error'] = true;
                        $result['error'] = __('Please upload images as per mentioned dimensions %1.',
                        $_filesParam['bulk-image']['name']);
                    }
                } else {
                    $result['file_name'] = $_filesParam['bulk-image']['name'];
                    $result['file_upload_error'] = true;
                    $result['error'] = __(
                        'Please remove special characters from file %1.',
                        $_filesParam['bulk-image']['name']
                    );
                }
            } catch (\Exception $e) {
                $result = ['error' => $e->getMessage(), 'errorcode' => $e->getCode()];
            }
        } else {
            $result['file_upload_error'] = true;
            $result['error'] = __(
                'Please upload images of following types %1',
                implode(', ', ['jpg', 'jpeg', 'gif', 'png'])
            );
        }
        return $this->jsonHelper->jsonEncode($result);
    }

    /**
     * @param array $files
     * @param string $width
     * @param string $height
     * @return boolean
     * @todo validate image dimensions
     */
    protected function validateImageDimensions($files = [], $width = '', $height = '')
    {
        if (!empty($files)) {
            $image_dimensions_info = getimagesize($files);
            $image_width = $image_dimensions_info[0];
            $image_height = $image_dimensions_info[1];

            if ($width && $image_width < $width) {
                return false;
            }

            if ($height && $image_height < $height) {
                return false;
            }

            return true;
        }
    }

    /**
     * @param $tmpFile
     * @return bool
     */
    protected function validateImageSize($tmpFile)
    {
        if (filesize($tmpFile)) {
            if (filesize($tmpFile) > $this->helper->getImageSize('bytes')) {
                return false;
            } else {
                return true;
            }
        }
    }

    /**
     * @todo validate special characters in file names.
     * @return boolean
     */
    protected function validateSpecialCharacters()
    {
        $_filesParam = $_FILES;
        if (preg_match('/[\'^£$%&*()}{@#~?><>,|=+]/', $_filesParam['bulk-image']['name'])) {
            return false;
        }
        return true;
    }

    /**
     * @param $_filesParam array
     * @param $uploader \Magento\MediaStorage\Model\File\Uploader
     * @return bool
     * @throws \Magento\Framework\Exception\FileSystemException
     * @throws \Magento\Framework\Exception\ValidatorException
     */
    protected function checkIfFileExists($_filesParam, $uploader)
    {
        $dispersion = $uploader->getDispersionPath(strtolower($_filesParam['bulk-image']['name']));
        $fullpathToCheck = $this->mediaDirectory->getAbsolutePath($this->mediaConfig->getBaseTmpMediaPath()) . $dispersion . DIRECTORY_SEPARATOR . strtolower($_filesParam['bulk-image']['name']);
        return $this->fileDriver->isExists($fullpathToCheck);
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
