<?php

namespace CAT\Custom\Controller\Adminhtml\Automation;

use Magento\Backend\App\Action;
use CAT\Custom\Model\AutomationFactory;
use Magento\Backend\Model\Auth\Session as AdminSession;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Controller\Result\Redirect;

/**
 * Class Save
 * @package CAT\Custom\Controller\Adminhtml\Automation
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
     * @var AutomationFactory
     */
    protected $automationFactory;
    /**
     * @var AdminSession
     */
    protected $authSession;

    /**
     * @param Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory
     * @param \Magento\Framework\Filesystem $fileSystem
     * @param AutomationFactory $automationFactory
     * @param AdminSession $authSession
     */
    public function __construct(
        Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory,
        \Magento\Framework\Filesystem $fileSystem,
        AutomationFactory $automationFactory,
        AdminSession $authSession
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->fileUploaderFactory = $fileUploaderFactory;
        $this->fileSystem=$fileSystem;
        $this->automationFactory = $automationFactory;
        $this->authSession  = $authSession;
        parent::__construct($context);
    }

    /**
     * @return Redirect
     */
    public function execute(): Redirect
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $fileId = $this->_request->getParam('param_name', 'import_file');
        try {
            $uploader = $this->fileUploaderFactory->create(['fileId' => $fileId]);
            $uploader->setAllowedExtensions(['csv']);
            $uploader->setAllowRenameFiles(true);
            $uploader->setFilesDispersion(false);
            $uploader->setAllowCreateFolders(true);
            $mediaUrl = $this->fileSystem->getDirectoryWrite(DirectoryList::MEDIA)->getAbsolutePath('/cat/'.$this->_request->getParam('entity'));
            $result = $uploader->save($mediaUrl);
            if (isset($result) && !$result['error']) {
                $automationModel = $this->automationFactory->create();
                $automationModel->setFileName($result['file']);
                $automationModel->setEntityType($this->_request->getParam('entity'));
                if ($this->_request->getParam('entity') == 'product_offer') {
                    $additionalInfo = ['website_id' => $this->_request->getParam('website_id'), 'vendor_id' => $this->_request->getParam('vendor_id')];
                    $automationModel->setAdditionalInfo(json_encode($additionalInfo));
                }
                $automationModel->setUserName($this->getAdminUserName());
                $automationModel->save();
                $this->messageManager->addSuccessMessage(__('File Imported Successfully.'));
                return $resultRedirect->setPath('catcustom/automation/index');
            } else {
                $this->messageManager->addErrorMessage(__('Something went wrong.'));
                return $resultRedirect->setRefererUrl();
            }
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__($e->getMessage()));
            return $resultRedirect->setRefererUrl();
        }
    }

    /**
     * @return mixed|string|null
     */
    public function getAdminUserName() {
        return $this->authSession->getUser()->getUserName();
    }
}
