<?php

namespace Ktpl\Tookan\Controller\Adminhtml\Index;

use Exception;
use Ktpl\Tookan\Model\Tookan\CsvImportHandler;
use Magento\Backend\App\Action;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\LocalizedException;

class ImportPost extends Action
{
    /**
     * @var CsvImportHandler
     */
    private $csvImportHandler;

    /**
     * ImportPost constructor.
     * @param Action\Context $context
     * @param CsvImportHandler $csvImportHandler
     */
    public function __construct(
        Action\Context $context,
        CsvImportHandler $csvImportHandler
    ) {
        parent::__construct($context);
        $this->csvImportHandler = $csvImportHandler;
    }

    /**
     * import action from import/export tax
     *
     * @return Redirect
     */
    public function execute()
    {
        $importRatesFile = $this->getRequest()->getFiles('import_tookan_file');
        if ($this->getRequest()->isPost() && isset($importRatesFile['tmp_name'])) {
            try {
                $this->csvImportHandler->importFromCsvFile($importRatesFile);
                $this->messageManager->addSuccess(__('The file has been imported successfully.'));
            } catch (LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (Exception $e) {
                $this->messageManager->addError(__('Invalid file upload attempt'));
            }
        } else {
            $this->messageManager->addError(__('Invalid file upload attempt'));
        }
        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setUrl($this->_redirect->getRedirectUrl());
        return $resultRedirect;
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Ktpl_Tookan::import');
    }
}
