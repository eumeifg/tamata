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
namespace Magedelight\Catalog\Controller\Sellerhtml\Product\Gallery;

use Magento\Framework\App\Filesystem\DirectoryList;

class Upload extends \Magedelight\Backend\App\Action
{

    /**
     * @var \Magento\Framework\Controller\Result\RawFactory
     */
    protected $resultRawFactory;

    /**
     * @var \Magento\MediaStorage\Model\File\UploaderFactory
     */
    protected $uploaderFactory;

    /**
     * @var \Magedelight\Catalog\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magento\Catalog\Model\Product\Media\Config
     */
    protected $config;

    /**
     * @var \Magento\Framework\Image\AdapterFactory
     */
    protected $adapterFactory;

    /**
     * @var \Magento\Framework\Filesystem
     */
    protected $filesystem;

    /**
     * @var \Magento\Framework\Filesystem\Io\File
     */
    protected $file;

    /**
     * @param \Magedelight\Backend\App\Action\Context $context
     * @param \Magento\Framework\Controller\Result\RawFactory $resultRawFactory
     * @param \Magento\MediaStorage\Model\File\UploaderFactory $uploaderFactory
     * @param \Magedelight\Catalog\Helper\Data $helper
     * @param \Magento\Catalog\Model\Product\Media\Config $config
     * @param \Magento\Framework\Image\AdapterFactory $adapterFactory
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Framework\Filesystem\Io\File $file
     */
    public function __construct(
        \Magedelight\Backend\App\Action\Context $context,
        \Magento\Framework\Controller\Result\RawFactory $resultRawFactory,
        \Magento\MediaStorage\Model\File\UploaderFactory $uploaderFactory,
        \Magedelight\Catalog\Helper\Data $helper,
        \Magento\Catalog\Model\Product\Media\Config $config,
        \Magento\Framework\Image\AdapterFactory $adapterFactory,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\Filesystem\Io\File $file
    ) {
        $this->resultRawFactory = $resultRawFactory;
        $this->uploaderFactory = $uploaderFactory;
        $this->helper = $helper;
        $this->config = $config;
        $this->adapterFactory = $adapterFactory;
        $this->filesystem = $filesystem;
        $this->file = $file;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\Controller\Result\Raw
     */
    public function execute()
    {
        $width = $this->helper->getImageWidth();
        $height = $this->helper->getImageHeight();
        $allowedFileTypes = ['jpg', 'jpeg', 'png'];
        try {
            $_filesParam = $this->getRequest()->getFiles();

            $uploader = $this->uploaderFactory->create(['fileId' => 'image']);
            $fileExtension = $this->file->getPathInfo($_filesParam['image']['name'])['extension'];
            $uploader->setAllowedExtensions($allowedFileTypes);
            if ($fileExtension && $uploader->checkAllowedExtension($fileExtension)) {
                if ($this->validateImageDimensions($_filesParam, $width, $height)) {
                    if ($this->validateImageSize()) {
                        /** @var \Magento\Framework\Image\Adapter\AdapterInterface $imageAdapter */
                        $imageAdapter = $this->adapterFactory->create();
                        $uploader->addValidateCallback(
                            'catalog_product_image',
                            $imageAdapter,
                            'validateUploadFile'
                        );
                        $uploader->setAllowRenameFiles(true);
                        $uploader->setFilesDispersion(true);
                        /** @var \Magento\Framework\Filesystem\Directory\Read $mediaDirectory */
                        $mediaDirectory = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA);
                        $result = $uploader->save(
                            $mediaDirectory->getAbsolutePath($this->config->getBaseTmpMediaPath())
                        );

                        $this->_eventManager->dispatch(
                            'catalog_product_gallery_upload_image_after',
                            ['result' => $result, 'action' => $this]
                        );

                        unset($result['tmp_name']);
                        unset($result['path']);

                        $result['url'] = $this->config->getTmpMediaUrl($result['file']);
                        $result['file'] = $result['file'] . '.tmp';
                    } else {
                        $result = ['error' => 'File size is too big.', 'errorcode' => 2];
                    }
                } else {
                    $result = [
                        'error' => __('Please upload image as per mentioned dimensions.
                         i.e(%1px x %2px)', $width, $height),
                        'errorcode' => 1
                    ];
                }
            } else {
                $result = [
                    'error' => __('Please upload file from given types %1', implode(', ', $allowedFileTypes)),
                    'errorcode' => 2
                ];
            }
        } catch (\Exception $e) {
            $result = ['error' => $e->getMessage(), 'errorcode' => $e->getCode()];
        }

        /** @var \Magento\Framework\Controller\Result\Raw $response */
        $response = $this->resultRawFactory->create();
        $response->setHeader('Content-type', 'text/plain');
        $response->setContents(json_encode($result));
        return $response;
    }

    /**
     * validate image dimensions
     * @param array $files
     * @param string $width
     * @param string $height
     * @return bool
     */
    protected function validateImageDimensions($files = [], $width = '', $height = '')
    {
        if (!empty($files)) {
            $image_dimensions_info = getimagesize($files["image"]["tmp_name"]);
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
     * @return bool
     */
    protected function validateImageSize()
    {
        $_filesParam = $this->getRequest()->getFiles()->toArray();
        if (isset($_filesParam['image'])) {
            if ($_filesParam['image']['size'] > $this->helper->getImageSize('bytes') ||
                $_filesParam['image']['error'] === UPLOAD_ERR_INI_SIZE) {
                return false;
            } else {
                return true;
            }
        }
    }

    /**
     * Catalog access rights checking
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magedelight_Catalog::manage_products');
    }
}
