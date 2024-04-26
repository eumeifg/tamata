<?php
/**
 * Magedelight
 * Copyright (C) 2017 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Abandonedcart
 * @copyright Copyright (c) 2017 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */

namespace Magedelight\Abandonedcart\Controller\Adminhtml\BlackList;
 
use Magento\Backend\App\Action;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem;
use Magento\Framework\File\UploaderFactory;

class CsvUpload extends Action
{
    /**
     * @var Magento\Framework\Filesystem
     */
    protected $fileSystem;

    /**
     * @var Magento\Framework\File\UploaderFactory
     */
    protected $uploaderFactory;

    /**
     * @var allowed array
     */
    protected $allowedExtensions = ['csv']; // to allow file upload types

    /**
     * @var fileid
     */
    protected $fileId = 'csvfile'; // name of the input file box

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param Filesystem $fileSystem,
     * @param UploaderFactory $uploaderFactory
     */
    public function __construct(
        Action\Context $context,
        Filesystem $fileSystem,
        UploaderFactory $uploaderFactory
    ) {
        $this->fileSystem = $fileSystem;
        $this->uploaderFactory = $uploaderFactory;
        parent::__construct($context);
    }

    /**
     * CSV Upload
     *
     */
    public function execute()
    {
        $destinationPath = $this->getDestinationPath();
        try {
            $uploader = $this->uploaderFactory->create(['fileId' => $this->fileId])
                ->setAllowCreateFolders(true)
                ->setAllowedExtensions($this->allowedExtensions);
            
            $result = $uploader->save($destinationPath);
            if (!$result) {
                throw new LocalizedException(
                    __('File cannot be saved to path: $1', $destinationPath)
                );
            }
            
            return $this->resultFactory->create(ResultFactory::TYPE_JSON)->setData($result);
        } catch (\Exception $e) {
            $this->messageManager->addError(
                __($e->getMessage())
            );
        }
    }

    /**
     * @return string
     */
    public function getDestinationPath()
    {
        return $this->fileSystem
            ->getDirectoryWrite(DirectoryList::VAR_DIR)
            ->getAbsolutePath('/');
    }
}
