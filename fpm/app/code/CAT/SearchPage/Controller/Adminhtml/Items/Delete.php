<?php

namespace CAT\SearchPage\Controller\Adminhtml\Items;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Image\AdapterFactory;
use Magento\MediaStorage\Model\File\UploaderFactory;

class Delete extends \CAT\SearchPage\Controller\Adminhtml\Items
{
    private $searchPage;

    public function __construct(
      \Magento\Backend\App\Action\Context $context,
      \Magento\Framework\Registry $coreRegistry,
      \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory,
      \Magento\Framework\View\Result\PageFactory $resultPageFactory,
      DirectoryList $directoryList,
      UploaderFactory $uploaderFactory,
      AdapterFactory $adapterFactory,
      Filesystem $filesystem,
      Filesystem\Driver\File $file,
      \CAT\SearchPage\Model\SearchPage $searchPage
    )
    {
      $this->searchPage = $searchPage;
      parent::__construct(
        $context,
        $coreRegistry,
        $resultForwardFactory,
        $resultPageFactory,
        $directoryList,
        $uploaderFactory,
        $adapterFactory,
        $filesystem,
        $file
      );
    }

  public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        if ($id) {
            try {
                //$model = $this->_objectManager->create('CAT\OfferPage\Model\OfferPage');
                $this->searchPage->load($id);
                $this->searchPage->delete();
                $this->messageManager->addSuccess(__('You deleted the item.'));
                $this->_redirect('search_page/*/');
                return;
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addError(
                    __('We can\'t delete item right now. Please review the log and try again.')
                );
                $this->_objectManager->get('Psr\Log\LoggerInterface')->critical($e);
                $this->_redirect('search_page/*/edit', ['id' => $id]);
                return;
            }
        }
        $this->messageManager->addError(__('We can\'t find a item to delete.'));
        $this->_redirect('search_page/*/');
    }
}
