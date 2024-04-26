<?php

namespace CAT\SearchPage\Controller\Adminhtml\Items;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Image\AdapterFactory;
use Magento\MediaStorage\Model\File\UploaderFactory;

class Save extends \CAT\SearchPage\Controller\Adminhtml\Items
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
        $this->searchPage = $searchPage;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     */
    public function execute()
    {
        if ($this->getRequest()->getPostValue()) {
            try {
                $data = $this->getRequest()->getPostValue();
                $dynamicRows = $this->getRequest()->getParam('dynamic_rows');
                $bannerImage = array_key_exists('file', $dynamicRows[0]['banner']) ? $dynamicRows[0]['banner'][0]['file'] : $dynamicRows[0]['banner'][0]['name'];
                if($dynamicRows && count($dynamicRows) > 0) {
                    $data['additional_info'] = json_encode($dynamicRows);
                    $data['banner'] = (!empty($dynamicRows[0]['banner']) && (count($dynamicRows[0]['banner']) > 0)) ? $bannerImage : '';
                }
                unset($data['dynamic_rows']);
                $inputFilter = new \Zend_Filter_Input(
                    [],
                    [],
                    $data
                );

                $data = $inputFilter->getUnescaped();
                $id = $this->getRequest()->getParam('id');
                if ($id) {
                    $this->searchPage->load($id);
                    if ($id != $this->searchPage->getId()) {
                        throw new \Magento\Framework\Exception\LocalizedException(__('The wrong item is specified.'));
                    }
                }

                if (empty($data['search_page_id'])) {
                    unset($data['search_page_id']);
                }
                $this->searchPage->setData($data);
                $session = $this->_objectManager->get('Magento\Backend\Model\Session');
                $session->setPageData($this->searchPage->getData());
                $this->searchPage->save();
                $this->messageManager->addSuccess(__('You saved the item.'));
                $session->setPageData(false);
                /*if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('search_page/edit', ['id' => $this->searchPage->getId()]);
                    return;
                }*/
                return $this->_redirect('search_page/*/');

            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                $id = (int)$this->getRequest()->getParam('search_page_id');
                if (!empty($id)) {
                    $this->_redirect('search_page/*/edit', ['id' => $id]);
                } else {
                    $this->_redirect('search_page/*/new');
                }
                return;
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(
                    __('Something went wrong while saving the item data. Please review the error log.')
                );
                $this->_objectManager->get('Psr\Log\LoggerInterface')->critical($e);
                $this->_objectManager->get('Magento\Backend\Model\Session')->setPageData($data);
                $this->_redirect('search_page/*/edit', ['id' => $this->getRequest()->getParam('offerpage_id')]);
                return;
            }
        }
        $this->_redirect('search_page/*/');
    }
}
