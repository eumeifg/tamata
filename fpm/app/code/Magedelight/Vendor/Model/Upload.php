<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Vendor
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Vendor\Model;

use Magento\Framework\File\Uploader;
use Magento\MediaStorage\Model\File\UploaderFactory;

class Upload
{

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * uploader factory
     *
     * @var \Magento\Core\Model\File\UploaderFactory
     */
    protected $uploaderFactory;

    /**
     * @var \Magedelight\Vendor\Helper\Data
     */
    protected $vendorHelper;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;
    /**
     * @var \Magento\Framework\Filesystem\Io\File
     */
    private $file;

    /**
     * @param UploaderFactory $uploaderFactory
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magedelight\Vendor\Helper\Data $vendorHelper
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Framework\Filesystem\Io\File $file
     */
    public function __construct(
        UploaderFactory $uploaderFactory,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magedelight\Vendor\Helper\Data $vendorHelper,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\Filesystem\Io\File $file
    ) {
        $this->uploaderFactory = $uploaderFactory;
        $this->objectManager = $objectManager;
        $this->vendorHelper = $vendorHelper;
        $this->request = $request;
        $this->file = $file;
    }

    /**
     * upload file
     *
     * @param $input
     * @param $destinationFolder
     * @param $data
     * @param array $allowedFileTypes
     * @return string
     * @throws \Magento\Framework\Validator\Exception
     */
    public function uploadFileAndGetName($input, $destinationFolder, $data, $allowedFileTypes = ['jpg', 'jpeg', 'png'])
    {
        $files = $this->request->getFiles()->toArray();
        try {
            $uploader = $this->uploaderFactory->create(['fileId' => $input]);
            $fileExtension = $this->file->getPathInfo($files[$input]['name'])['extension'];
            $uploader->setAllowedExtensions($allowedFileTypes);
            if ($fileExtension && !$uploader->checkAllowedExtension($fileExtension)) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('Please upload file from given types %1', implode(', ', $allowedFileTypes))
                );
            }
            if ($input == 'logo' || $input == 'businesslogo') {
                $companyLogoHeight = $this->vendorHelper->getConfigValue('vendor/general/company_logo_height');
                $companyLogoWidth =  $this->vendorHelper->getConfigValue('vendor/general/company_logo_width');
                $logoSize = getimagesize($files[$input]['tmp_name']);
                $logoWidth = $logoSize[0];
                $logoHeight = $logoSize[1];

                if ($logoWidth != $companyLogoWidth || $logoHeight != $companyLogoHeight) {
                    throw new \Magento\Framework\Validator\Exception(
                        __('Logo dimensions not matched, please upload image of given dimensions.')
                    );
                }
            }
            $uploader->setAllowRenameFiles(true);
            $uploader->setFilesDispersion(true);
            $uploader->setAllowCreateFolders(true);
            $result = $uploader->save($destinationFolder);
            return $result['file'];
        } catch (\Exception $e) {
            if ($files[$input]['name'] != null &&
                ($files[$input]['size'] > Vendor::DEFAULT_IMAGE_SIZE ||
                    $files[$input]['error'] === UPLOAD_ERR_INI_SIZE)) {
                throw new \Magento\Framework\Validator\Exception(
                    __(
                        "File size must be less than %1.",
                        $this->vendorHelper->getFormattedFileSize(Vendor::DEFAULT_IMAGE_SIZE)
                    )
                );
            }
            if ($files[$input]['name'] != null && $files[$input]['error'] !== UPLOAD_ERR_OK) {
                throw new \RB\Common\Model\Validator\UploadException($files[$input]['error']);
            }
            if ($e->getCode() != Uploader::TMP_NAME_EMPTY) {
                throw new \Magento\Framework\Validator\Exception(__($e->getMessage()));
            }
            if (isset($data[$input]['value'])) {
                return $data[$input]['value'];
            }
        }
        return '';
    }
}
