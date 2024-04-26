<?php

namespace CAT\CustomerBalance\Controller\Adminhtml\Import;

use Magento\Backend\App\Action;
use CAT\CustomerBalance\Model\BulkImportFactory;

/**
 * Class Save
 * @package CAT\CustomerBalance\Controller\Adminhtml\Import
 */
class Save extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \Magento\Framework\Filesystem
     */
    protected $fileSystem;

    /**
     * @var \Magento\MediaStorage\Model\File\UploaderFactory
     */
    protected $fileUploaderFactory;

    /**
     * @var BulkImportFactory
     */
    protected $bulkImportFactory;

    /**
     * Save constructor.
     * @param Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory
     * @param \Magento\Framework\Filesystem $fileSystem
     * @param BulkImportFactory $bulkImportFactory
     */
    public function __construct(
        Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory,
        \Magento\Framework\Filesystem $fileSystem,
        BulkImportFactory $bulkImportFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->fileUploaderFactory = $fileUploaderFactory;
        $this->fileSystem=$fileSystem;
        $this->bulkImportFactory = $bulkImportFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $fileId = $this->_request->getParam('param_name', 'import_file');
        try {
            $uploader = $this->fileUploaderFactory->create(['fileId' => $fileId]);
            $uploader->setAllowedExtensions(['csv']);
            $uploader->setAllowRenameFiles(true);
            $uploader->setFilesDispersion(false);
            $uploader->setAllowCreateFolders(true);
            $mediaUrl = $this->fileSystem->getDirectoryWrite(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA)->getAbsolutePath('/cat/store-credit/');
            $result = $uploader->save($mediaUrl);
            if (isset($result) && !$result['error']) {
                $bulkInvModel = $this->bulkImportFactory->create();
                $bulkInvModel->setFileName($result['file']);
                $bulkInvModel->save();
                $this->messageManager->addSuccessMessage(__('File Imported Successfully.'));
                return $resultRedirect->setPath('*/*/');
            } else {
                $this->messageManager->addErrorMessage(__('Something went wrong.'));
                return $resultRedirect->setRefererUrl();
            }
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__($e->getMessage()));
            return $resultRedirect->setRefererUrl();
        }
    }
}