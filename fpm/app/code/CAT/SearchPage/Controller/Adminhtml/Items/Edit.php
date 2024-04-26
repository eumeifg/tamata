<?php


namespace CAT\SearchPage\Controller\Adminhtml\Items;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Image\AdapterFactory;
use Magento\MediaStorage\Model\File\UploaderFactory;

class Edit extends \CAT\SearchPage\Controller\Adminhtml\Items
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
        
        //$model = $this->_objectManager->create('CAT\OfferPage\Model\OfferPage');

        if ($id) {
          $this->searchPage->load($id);
            if (!$this->searchPage->getId()) {
                $this->messageManager->addError(__('This item no longer exists.'));
                $this->_redirect('search_page/*');
                return;
            }
        }
        // set entered data if was error when we do save
        $data = $this->_objectManager->get('Magento\Backend\Model\Session')->getPageData(true);
        if (!empty($data)) {
          $this->searchPage->addData($data);
        }
        $this->_coreRegistry->register('current_cat_search_page_items', $this->searchPage);
        $this->_initAction();
        $this->_view->getLayout()->getBlock('items_items_edit');
        $this->_view->renderLayout();
    }
}
