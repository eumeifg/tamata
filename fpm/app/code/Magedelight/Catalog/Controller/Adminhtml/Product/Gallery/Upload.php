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
namespace Magedelight\Catalog\Controller\Adminhtml\Product\Gallery;

use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\Framework\App\Filesystem\DirectoryList;

class Upload extends \Magento\Backend\App\Action implements HttpPostActionInterface
{

    /**
     * @var \Magento\Framework\Controller\Result\RawFactory
     */
    protected $resultRawFactory;

    /**
     * @var \Magedelight\Catalog\Helper\Data
     */
    protected $helper;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Controller\Result\RawFactory $resultRawFactory
     * @param \Magedelight\Catalog\Helper\Data $helper
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Controller\Result\RawFactory $resultRawFactory,
        \Magedelight\Catalog\Helper\Data $helper
    ) {
        $this->helper = $helper;
        $this->resultRawFactory = $resultRawFactory;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\Controller\Result\Raw
     */
    public function execute()
    {
        $width = $this->helper->getImageWidth();
        $height = $this->helper->getImageHeight();

        try {
            $_filesParam = $this->getRequest()->getFiles()->toArray();
            if ($this->validateImageDimensions($_filesParam, $width, $height)) {
                if ($this->validateImageSize($_filesParam)) {
                    $uploader = $this->_objectManager->create(
                        \Magento\MediaStorage\Model\File\Uploader::class,
                        ['fileId' => 'image']
                    );
                    $uploader->setAllowedExtensions(['jpg', 'jpeg', 'gif', 'png']);
                    /** @var \Magento\Framework\Image\Adapter\AdapterInterface $imageAdapter */
                    $imageAdapter = $this->_objectManager->get(
                        \Magento\Framework\Image\AdapterFactory::class
                    )->create();
                    $uploader->addValidateCallback('catalog_product_image', $imageAdapter, 'validateUploadFile');
                    $uploader->setAllowRenameFiles(true);
                    $uploader->setFilesDispersion(true);
                    /** @var \Magento\Framework\Filesystem\Directory\Read $mediaDirectory */
                    $mediaDirectory = $this->_objectManager->get(\Magento\Framework\Filesystem::class)
                        ->getDirectoryRead(DirectoryList::MEDIA);
                    $config = $this->_objectManager->get(\Magento\Catalog\Model\Product\Media\Config::class);
                    $result = $uploader->save($mediaDirectory->getAbsolutePath($config->getBaseTmpMediaPath()));

                    unset($result['tmp_name']);
                    unset($result['path']);

                    $result['url'] = $this->_objectManager->get(\Magento\Catalog\Model\Product\Media\Config::class)
                        ->getTmpMediaUrl($result['file']);
                    // $result['file'] = $result['file'] . '.tmp';
                    $result['file'] = $result['file'];
                } else {
                    $result = ['error' => 'File size is too big.', 'errorcode' => 2];
                }
            } else {
                $result = [
                    'error' => __('Please upload image as per mentioned dimensions. i.e(%1px x %2px)', $width, $height),
                    'errorcode' => 1
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
            if ($_filesParam['image']['size'] > $this->helper->getImageSize('bytes')
                    || $_filesParam['image']['error'] === UPLOAD_ERR_INI_SIZE) {
                return false;
            } else {
                return true;
            }
        }
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magedelight_Catalog::vendor_products');
    }
}
