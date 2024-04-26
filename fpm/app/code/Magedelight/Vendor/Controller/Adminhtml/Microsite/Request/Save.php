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
namespace Magedelight\Vendor\Controller\Adminhtml\Microsite\Request;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;

/**
 * Description of Save
 *
 * @author Rocket Bazaar Core Team
 */
class Save extends \Magento\Backend\App\Action
{

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $date;

    /**
     * @var \Magento\Backend\Helper\Js
     */
    protected $_jsHelper;

    /**
     * @var \Magento\Framework\Image\AdapterFactory
     */
    protected $adapterFactory;

    /**
     * @var \Magento\Catalog\Model\Product\Url
     */
    protected $_urlModel;

    /**
     * @var \Magento\MediaStorage\Model\File\UploaderFactory
     */
    protected $uploader;
    /**
     * @var \Magento\Framework\Filesystem
     */
    protected $filesystem;

    /**
     * @var \Magedelight\Vendor\Model\Upload
     */
    protected $uploadModel;

    /**
     * @var \Magedelight\Vendor\Model\Vendor\Image
     */
    protected $imageModel;

    /**
     * \Magento\Backend\Helper\Js $jsHelper
     * @param \Magento\Backend\Helper\Js $jsHelper
     * @param \Magento\Framework\Image\AdapterFactory $adapterFactory
     * @param \Magento\MediaStorage\Model\File\UploaderFactory $uploader
     * @param \Magento\Catalog\Model\Product\Url $url
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magedelight\Vendor\Model\Upload $uploadModel
     * @param \Magedelight\Vendor\Model\Vendor\Image $imageModel
     * @param Action\Context $context
     */
    public function __construct(
        \Magento\Backend\Helper\Js $jsHelper,
        \Magento\Framework\Image\AdapterFactory $adapterFactory,
        \Magento\MediaStorage\Model\File\UploaderFactory $uploader,
        \Magento\Catalog\Model\Product\Url $url,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Framework\Filesystem $filesystem,
        \Magedelight\Vendor\Model\Upload $uploadModel,
        \Magedelight\Vendor\Model\Vendor\Image $imageModel,
        Context $context
    ) {
        $this->_jsHelper = $jsHelper;
        $this->adapterFactory = $adapterFactory;
        $this->uploader = $uploader;
        $this->_urlModel = $url;
        $this->filesystem = $filesystem;
        $this->uploadModel = $uploadModel;
        $this->imageModel = $imageModel;
        parent::__construct($context);
        $this->date = $date;
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magedelight_Vendor::save_microsite');
    }

    /**
     * Save action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $data = $this->getRequest()->getPostValue();

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($data) {

            /** @var '\Magedelight\Vendor\Model\Microsite $model */
            $model = $this->_objectManager->create(\Magedelight\Vendor\Model\Microsite::class);

            $path = $this->filesystem->getDirectoryRead(
                \Magento\Framework\App\Filesystem\DirectoryList::MEDIA
            )
                ->getAbsolutePath('microsite/');

            if (!isset($data['microsite_id']) || $data['microsite_id']=='') {
                $data['microsite_id'] = null;
            }

            if (trim($data['page_title'])=='' || empty(trim($data['page_title']))) {
                $this->messageManager->addError('Please enter page title.');
                return $resultRedirect->setPath('*/*/');
            }

            if (trim($data['url_key'])=='' || empty(trim($data['url_key']))) {
                $this->messageManager->addError('Please enter url key.');
                return $resultRedirect->setPath('*/*/');
            }

            $data['url_key'] = $this->_urlModel->formatUrlKey($data['url_key']);

            if (!isset($data['banner']['delete'])) {
                if (isset($this->getRequest()->getFiles('banner')['name']) &&
                    $this->getRequest()->getFiles('banner')['name'] != '') {
                    try {
                        $data['banner'] = $this->uploadFile($data, 'banner');
                    } catch (\Exception $e) {
                        $this->messageManager->addError($e->getMessage());
                        if ($this->getRequest()->getParam('microsite_id')) {
                            return $resultRedirect->setPath(
                                '*/*/edit',
                                ['id' => $this->getRequest()->getParam('microsite_id')]
                            );
                        }
                        return $resultRedirect->setPath('*/*/');
                    }
                } else {
                    if (isset($data['microsite_id'])) {
                        $model->load($data['microsite_id']);
                        $data['banner'] = $model->getData()['banner'];
                    }
                }
            } else {
                $data['banner'] = '';
            }

            $id = $data['microsite_id'];
            if ($id) {
                $model->load($id);
            }

            $model->setData($data);

            try {
                $model->save();

                $this->messageManager->addSuccess(__('You saved this microsite.'));
                $this->_objectManager->get(\Magento\Backend\Model\Session::class)->setFormData(false);
                $returnToEdit = (bool)$this->getRequest()->getParam('back', false);
                if ($returnToEdit) {
                    return $resultRedirect->setPath(
                        'vendor/microsite_request/edit',
                        ['id' => $model->getMicrositeId(), '_current' => true]
                    );
                } else {
                    return $resultRedirect->setPath('*/*/');
                }
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\RuntimeException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addException($e, __('Something went wrong while saving the microsite.'));
            }

            $this->_getSession()->setFormData($data);
            return $resultRedirect->setPath(
                '*/*/edit',
                ['id' => $this->getRequest()->getParam('microsite_id')]
            );
        }
        return $resultRedirect->setPath('*/*/');
    }

    /**
     * Upload file.
     * @param array $postData
     * @param string $field
     * @return string
     * @throws \Exception
     */
    protected function uploadFile($postData = [], $field = '')
    {
        if ($this->getRequest()->getFiles($field) && !empty($this->getRequest()->getFiles($field)['tmp_name'])) {
            return $this->uploadModel->uploadFileAndGetName(
                $field,
                $this->imageModel->getBaseDir('microsite'),
                $postData
            );
        }
    }
}
