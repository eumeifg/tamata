<?php

namespace MDC\Sales\Controller\Adminhtml\BulkInvoiceShipment;

use Magento\Backend\App\Action;
use MDC\Sales\Model\BulkInvoiceShipFactory;

/**
 * Class Save
 * @package MDC\Sales\Controller\Adminhtml\BulkInvoiceShipment
 */
class Save extends Action
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

    protected $bulkInvoiceShipFactory;


    /**
     * Save constructor.
     * @param Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory
     * @param \Magento\Framework\Filesystem $fileSystem
     */
    public function __construct(
        Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory,
        \Magento\Framework\Filesystem $fileSystem,
        BulkInvoiceShipFactory $bulkInvoiceShipFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->fileUploaderFactory = $fileUploaderFactory;
        $this->fileSystem=$fileSystem;
        $this->bulkInvoiceShipFactory = $bulkInvoiceShipFactory;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     */
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
            $mediaUrl = $this->fileSystem->getDirectoryWrite(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA)->getAbsolutePath('/cat/');
            $result = $uploader->save($mediaUrl);
            if (isset($result) && !$result['error']) {
                $bulkInvModel = $this->bulkInvoiceShipFactory->create();
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