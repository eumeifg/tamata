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
namespace Magedelight\Vendor\Controller\Sellerhtml\Microsite;

use Magedelight\Backend\App\Action\Context;

/**
 * Description of Save
 *
 * @author Rocket Bazaar Core Team
 */
class Save extends \Magedelight\Backend\App\Action
{

    /**
     * @var \Magedelight\Vendor\Helper\Data
     */
    protected $vendorHelper;

    /**
     * @var \Magedelight\Vendor\Helper\Microsite\Data
     */
    protected $helper;

    /**
     * @var \Magento\Framework\Image\AdapterFactory
     */
    protected $adapterFactory;

    /**
     * @var \Magento\MediaStorage\Model\File\UploaderFactory
     */
    protected $uploader;

    /**
     * @var \Magento\Framework\Filesystem
     */
    protected $filesystem;

    /**
     * @var \Magedelight\Vendor\Model\MicrositeFactory
     */
    protected $micrositeFactory;

    /**
     * @param Context $context
     * @param \Magedelight\Vendor\Helper\Data $vendorHelper
     * @param \Magedelight\Vendor\Helper\Microsite\Data $helper
     * @param \Magento\Framework\Image\AdapterFactory $adapterFactory
     * @param \Magento\MediaStorage\Model\File\UploaderFactory $uploader
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magedelight\Vendor\Model\MicrositeFactory $micrositeFactory
     */
    public function __construct(
        Context $context,
        \Magedelight\Vendor\Helper\Data $vendorHelper,
        \Magedelight\Vendor\Helper\Microsite\Data $helper,
        \Magento\Framework\Image\AdapterFactory $adapterFactory,
        \Magento\MediaStorage\Model\File\UploaderFactory $uploader,
        \Magento\Framework\Filesystem $filesystem,
        \Magedelight\Vendor\Model\MicrositeFactory $micrositeFactory
    ) {
        $this->vendorHelper = $vendorHelper;
        $this->helper = $helper;
        $this->adapterFactory = $adapterFactory;
        $this->uploader = $uploader;
        $this->filesystem = $filesystem;
        $this->micrositeFactory = $micrositeFactory;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Redirect|\Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     * @throws \Zend_Validate_Exception
     */
    public function execute()
    {
        $data = $this->getRequest()->getPostValue();
        $data['vendor_id'] = $this->_auth->getUser()->getVendorId();

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();

        /** @var \Magedelight\Vendor\Model\Microsite $model */
        $model = $this->micrositeFactory->create();

        $path = $this->filesystem->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA)
                ->getAbsolutePath('microsite/');

        $errors = $this->validateMicrosite($data);
        if (count($errors) > 0) {
            $this->messageManager->addErrorMessage(implode(', ', $errors));
            return $resultRedirect->setPath('*/*/');
        }

        if (!isset($data['microsite_id']) || $data['microsite_id'] == '') {
            $data['microsite_id'] = null;
        }
        $micrositeData = $model->load($data['vendor_id'], 'vendor_id');

        $data = $this->filterMicrositePostData($data, $model);

        if (!$micrositeData->getVendorId()) {
            $data['url_key'] = $this->helper->generateUrlKey(trim($data['page_title']));
        }
        if ($micrositeData->getVendorId()) {
            $storeId = $this->vendorHelper->getCurrentStoreId();
            $data['store_id'] = $storeId;
            $model->setData($data);

            try {
                $model->save();
                $this->messageManager->addSuccessMessage(__('Information has been saved successfully.'));
                $eventParams = ['microsite' => $model, 'post_data' => $data];
                $this->_eventManager->dispatch('microsite_save_after', $eventParams);
                $this->_objectManager->get(\Magento\Backend\Model\Session::class)->setFormData(false);
                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath('*/*/');
                }
                return $resultRedirect->setPath('*/*/');
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\RuntimeException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Zend_Db_Statement_Exception $e) {
                $this->messageManager->addException($e, __(
                    'Duplicate (Vendor ID "%1"")',
                    $data['vendor_id']
                ));
            } catch (\Exception $e) {
                $this->messageManager->addException(
                    $e,
                    __('Something went wrong while saving the microsite information.')
                );
            }
        } else {
            $storeIds = $this->vendorHelper->getAllStoreIds();
            try {
                foreach ($storeIds as $store) {
                    $model = $this->micrositeFactory->create();
                    $data['store_id'] = $store;
                    $model->setData($data);
                    $model->save();
                    $eventParams = ['microsite' => $model, 'post_data' => $data];
                    $this->_eventManager->dispatch('microsite_save_after', $eventParams);
                }
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\RuntimeException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Zend_Db_Statement_Exception $e) {
                $this->messageManager->addException($e, __(
                    'Duplicate (Vendor ID "%1"")',
                    $data['vendor_id']
                ));
            } catch (\Exception $e) {
                $this->messageManager->addException(
                    $e,
                    __('Something went wrong while saving the microsite information.')
                );
            }
            $this->messageManager->addSuccess(__('Information has been saved successfully.'));
            $this->_objectManager->get(\Magento\Backend\Model\Session::class)->setFormData(false);
            if ($this->getRequest()->getParam('back')) {
                return $resultRedirect->setPath('*/*/');
            }
            return $resultRedirect->setPath('*/*/');
        }
        return $resultRedirect->setPath('*/*/');
    }

    /**
     * Filter microsite data
     *
     * @param \Magedelight\Vendor\Model\Microsite $model
     * @param array $data
     * @return array
     * @throws \Exception
     */
    protected function filterMicrositePostData(array $data, $model)
    {
        try {
            if (isset($this->getRequest()->getFiles('banner')['name']) &&
                $this->getRequest()->getFiles('banner')['name'] != '') {
                $data['banner'] = $this->uploadFile('banner');
            } elseif (isset($data['microsite_id']) && !empty($data['banner']['delete'])) {
                $data['banner'] = null;
            } elseif (isset($data['microsite_id']) && empty($data['banner']['delete'])) {
                $data['banner'] = $model->getData('banner');
            } else {
                $data['banner']  = null;
            }
        } catch (\Exception $e) {
            throw new \Exception(__('Banner Not Save.'));
        }

        return $data;
    }

    /**
     * @param string $fileId
     * @param string $subPath
     * @return string
     * @throws \Exception
     */
    protected function uploadFile($fileId, $subPath = 'microsite')
    {
        $path = $this->filesystem->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA)
                ->getAbsolutePath($subPath);
        $uploader = $this->uploader->create(['fileId' => $fileId]);
        $uploader->setAllowedExtensions(['jpg', 'jpeg', 'gif', 'png']);
        $imageAdapter = $this->adapterFactory->create();
        $uploader->addValidateCallback($fileId, $imageAdapter, 'validateUploadFile');
        $uploader->setAllowRenameFiles(true);
        $uploader->setFilesDispersion(true);
        $result = $uploader->save($path);
        return $result['file'];
    }

    /**
     *
     * @param array|object $postData
     * @param boolean $returnFirstError
     * @return array
     * @throws \Zend_Validate_Exception
     */
    public function validateMicrosite($postData, $returnFirstError = false)
    {
        $errors = [];

        if (is_array($postData)) {
            $microsite = new \Magento\Framework\DataObject();
            $microsite->setData($postData);
        } else {
            $microsite = $postData;
        }

        if (!\Zend_Validate::is($microsite->getPageTitle(), 'NotEmpty')) {
            $errors[] = __('"%fieldName" is required. Enter and try again.', ['fieldName' => 'Page Title']);
        } else {
            if (!\Zend_Validate::is($microsite->getPageTitle(), 'Regex', ['pattern' => '/^.{0,255}$/'])) {
                $errors[] = __(
                    'Please enter less than 255 characters for "%fieldName".',
                    ['fieldName' => 'Page Title']
                );
            }
        }

        if (\Zend_Validate::is($microsite->getMetaKeyword(), 'NotEmpty')) {
            if (!\Zend_Validate::is($microsite->getMetaKeyword(), 'Regex', ['pattern' => '/^.{0,255}$/'])) {
                $errors[] = __(
                    'Please enter less than 255 characters for "%fieldName".',
                    ['fieldName' => 'Meta Keywords']
                );
            }
        }

        if (\Zend_Validate::is($microsite->getTwitterPage(), 'NotEmpty')) {
            if (!\Zend_Validate::is($microsite->getTwitterPage(), 'Regex', ['pattern' => '/^.{0,255}$/'])) {
                $errors[] = __(
                    'Please enter less than 255 characters for "%fieldName".',
                    ['fieldName' => 'Twitter Page']
                );
            }
        }

        if (\Zend_Validate::is($microsite->getGooglePage(), 'NotEmpty')) {
            if (!\Zend_Validate::is($microsite->getGooglePage(), 'Regex', ['pattern' => '/^.{0,255}$/'])) {
                $errors[] = __(
                    'Please enter less than 255 characters for "%fieldName".',
                    ['fieldName' => 'Google Page']
                );
            }
        }

        if (\Zend_Validate::is($microsite->getFacebookPage(), 'NotEmpty')) {
            if (!\Zend_Validate::is($microsite->getFacebookPage(), 'Regex', ['pattern' => '/^.{0,255}$/'])) {
                $errors[] = __(
                    'Please enter less than 255 characters for "%fieldName".',
                    ['fieldName' => 'Facebook Page']
                );
            }
        }

        if (\Zend_Validate::is($microsite->getTumblerPage(), 'NotEmpty')) {
            if (!\Zend_Validate::is($microsite->getTumblerPage(), 'Regex', ['pattern' => '/^.{0,255}$/'])) {
                $errors[] = __(
                    'Please enter less than 255 characters for "%fieldName".',
                    ['fieldName' => 'Tumbler Page']
                );
            }
        }

        if (\Zend_Validate::is($microsite->getInstagramPage(), 'NotEmpty')) {
            if (!\Zend_Validate::is($microsite->getInstagramPage(), 'Regex', ['pattern' => '/^.{0,255}$/'])) {
                $errors[] = __(
                    'Please enter less than 255 characters for "%fieldName".',
                    ['fieldName' => 'Instagram Page']
                );
            }
        }

        return ($returnFirstError && count($errors) > 0) ? [$errors[0]] : $errors;
    }
}
