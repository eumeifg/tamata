<?php
/**
 * KrishTechnolabs
 *
 * PHP version 7
 *
 * @category  KrishTechnolabs
 * @package   MDC_Vendor
 * @copyright 2019 (c) KrishTechnolabs (https://www.KrishTechnolabs.com/)
 * @license   https://www.krishtechnolabs.com/LICENSE.txt Krish License
 * @link      https://www.krishtechnolabs.com/
 */
namespace MDC\Vendor\Controller\Sellerhtml\Microsite;

class Save extends \Magedelight\Vendor\Controller\Sellerhtml\Microsite\Save
{
    
    public function execute()
    {
        $data = $this->getRequest()->getPostValue();
        $data['vendor_id'] = $this->_auth->getUser()->getVendorId();

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();

        /** @var \Magedelight\Vendor\Model\Microsite $model */
        $model = $this->micrositeFactory->create();

        if (!isset($data['microsite_id']) || $data['microsite_id'] == '') {
            $data['microsite_id'] = null;
        }
        
        $errors = $this->validateMicrosite($data);
        if (count($errors) > 0) {
            $this->messageManager->addErrorMessage(implode(', ', $errors));
            return $resultRedirect->setPath('*/*/');
        }
        
        $data['url_key'] = $this->helper->generateUrlKey(trim($data['page_title']));
        
        if (isset($data['microsite_id'])) {
            $model->load($data['microsite_id']);
        }
        
        $data = $this->filterMicrositePostData($data, $model);

        $micrositeData = $model->load($data['vendor_id'], 'vendor_id');
        if ($micrositeData->getVendorId()) {
            $storeId = $this->vendorHelper->getCurrentStoreId();
            $data['store_id'] = $storeId;
            $model->setData($data);

            try {
                $model->save();
                $this->messageManager->addSuccessMessage(__('Information has been saved successfully.'));
                $eventParams = ['microsite' => $model, 'post_data' => $data];
                $this->_eventManager->dispatch('microsite_save_after', $eventParams);
                $this->_objectManager->get('Magento\Backend\Model\Session')->setFormData(false);
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
                $this->messageManager->addException($e, __('Something went wrong while saving the microsite information.'));
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
                $this->messageManager->addException($e, __('Something went wrong while saving the microsite information.'));
            }
            $this->messageManager->addSuccess(__('Information has been saved successfully.'));
            $this->_objectManager->get('Magento\Backend\Model\Session')->setFormData(false);
            if ($this->getRequest()->getParam('back')) {
                return $resultRedirect->setPath('*/*/');
            }
            return $resultRedirect->setPath('*/*/');
        }
        return $resultRedirect->setPath('*/*/');
    }
    
    /**
     * @param string $fileId
     * @param string $subPath
     * @return string
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
                $errors[] = __('Please enter less than 255 characters for "%fieldName".', ['fieldName' => 'Page Title']);
            }
        }
        
        if (\Zend_Validate::is($microsite->getMetaKeyword(), 'NotEmpty')) {
            if (!\Zend_Validate::is($microsite->getMetaKeyword(), 'Regex', ['pattern' => '/^.{0,255}$/'])) {
                $errors[] = __('Please enter less than 255 characters for "%fieldName".', ['fieldName' => 'Meta Keywords']);
            }
        }
        
        if (\Zend_Validate::is($microsite->getTwitterPage(), 'NotEmpty')) {
            if (!\Zend_Validate::is($microsite->getTwitterPage(), 'Regex', ['pattern' => '/^.{0,255}$/'])) {
                $errors[] = __('Please enter less than 255 characters for "%fieldName".', ['fieldName' => 'Twitter Page']);
            }
        }
        
        if (\Zend_Validate::is($microsite->getGooglePage(), 'NotEmpty')) {
            if (!\Zend_Validate::is($microsite->getGooglePage(), 'Regex', ['pattern' => '/^.{0,255}$/'])) {
                $errors[] = __('Please enter less than 255 characters for "%fieldName".', ['fieldName' => 'Google Page']);
            }
        }
        
        if (\Zend_Validate::is($microsite->getFacebookPage(), 'NotEmpty')) {
            if (!\Zend_Validate::is($microsite->getFacebookPage(), 'Regex', ['pattern' => '/^.{0,255}$/'])) {
                $errors[] = __('Please enter less than 255 characters for "%fieldName".', ['fieldName' => 'Facebook Page']);
            }
        }
        
        if (\Zend_Validate::is($microsite->getTumblerPage(), 'NotEmpty')) {
            if (!\Zend_Validate::is($microsite->getTumblerPage(), 'Regex', ['pattern' => '/^.{0,255}$/'])) {
                $errors[] = __('Please enter less than 255 characters for "%fieldName".', ['fieldName' => 'Tumbler Page']);
            }
        }
        
        if (\Zend_Validate::is($microsite->getInstagramPage(), 'NotEmpty')) {
            if (!\Zend_Validate::is($microsite->getInstagramPage(), 'Regex', ['pattern' => '/^.{0,255}$/'])) {
                $errors[] = __('Please enter less than 255 characters for "%fieldName".', ['fieldName' => 'Instagram Page']);
            }
        }

        return ($returnFirstError && count($errors) > 0) ? [$errors[0]] : $errors;
    }
    
    /**
     * Filter microsite data
     *
     * @param \Magedelight\Vendor\Model\Microsite $model
     * @param array $data
     * @return array
     */
    protected function filterMicrositePostData(array $data, $model)
    {
        if (isset($this->getRequest()->getFiles('banner')['name']) && $this->getRequest()->getFiles('banner')['name'] != '') {
            try {
                $data['banner'] = $this->uploadFile('banner');
            } catch (\Exception $e) {
                $data['banner'] = '';
            }
        } else {
            if (!empty($data['banner']['delete'])) {
                $data['banner'] = null;
            } else {
                if (isset($data['microsite_id'])) {
                    $data['banner'] = $model->getData('banner');
                }
            }
        }
        
        if (!empty($this->getRequest()->getFiles('promo_banner_1')['name'])) {
            try {
                $data['promo_banner_1'] = $this->uploadFile('promo_banner_1', 'microsite/promo_banners');
            } catch (\Exception $e) {
                $data['promo_banner_1'] = '';
            }
        } else {
            if (!empty($data['promo_banner_1']['delete'])) {
                $data['promo_banner_1'] = null;
            } else {
                if (isset($data['microsite_id'])) {
                    $data['promo_banner_1'] = $model->getData('promo_banner_1');
                }
            }
        }
        
        if (!empty($this->getRequest()->getFiles('promo_banner_2')['name'])) {
            try {
                $data['promo_banner_2'] = $this->uploadFile('promo_banner_2', 'microsite/promo_banners');
            } catch (\Exception $e) {
                $data['promo_banner_2'] = '';
            }
        } else {
            if (!empty($data['promo_banner_2']['delete'])) {
                $data['promo_banner_2'] = null;
            } else {
                if (isset($data['microsite_id'])) {
                    $data['promo_banner_2'] = $model->getData('promo_banner_2');
                }
            }
        }
        
        if (!empty($this->getRequest()->getFiles('promo_banner_3')['name'])) {
            try {
                $data['promo_banner_3'] = $this->uploadFile('promo_banner_3', 'microsite/promo_banners');
            } catch (\Exception $e) {
                $data['promo_banner_3'] = '';
            }
        } else {
            if (!empty($data['promo_banner_3']['delete'])) {
                $data['promo_banner_3'] = null;
            } else {
                if (isset($data['microsite_id'])) {
                    $data['promo_banner_3'] = $model->getData('promo_banner_3');
                }
            }
        }

        if (!empty($this->getRequest()->getFiles('mobile_promo_banner_1')['name'])) {
            try {
                $data['mobile_promo_banner_1'] = $this->uploadFile('mobile_promo_banner_1', 'microsite/promo_banners');
            } catch (\Exception $e) {
                $data['mobile_promo_banner_1'] = '';
            }
        } else {
            if (!empty($data['mobile_promo_banner_1']['delete'])) {
                $data['mobile_promo_banner_1'] = null;
            } else {
                if (isset($data['microsite_id'])) {
                    $data['mobile_promo_banner_1'] = $model->getData('mobile_promo_banner_1');
                }
            }
        }

        if (!empty($this->getRequest()->getFiles('mobile_promo_banner_2')['name'])) {
            try {
                $data['mobile_promo_banner_2'] = $this->uploadFile('mobile_promo_banner_2', 'microsite/promo_banners');
            } catch (\Exception $e) {
                $data['mobile_promo_banner_2'] = '';
            }
        } else {
            if (!empty($data['mobile_promo_banner_2']['delete'])) {
                $data['mobile_promo_banner_2'] = null;
            } else {
                if (isset($data['microsite_id'])) {
                    $data['mobile_promo_banner_2'] = $model->getData('mobile_promo_banner_2');
                }
            }
        }

        if (!empty($this->getRequest()->getFiles('mobile_promo_banner_3')['name'])) {
            try {
                $data['mobile_promo_banner_3'] = $this->uploadFile('mobile_promo_banner_3', 'microsite/promo_banners');
            } catch (\Exception $e) {
                $data['mobile_promo_banner_3'] = '';
            }
        } else {
            if (!empty($data['mobile_promo_banner_3']['delete'])) {
                $data['mobile_promo_banner_3'] = null;
            } else {
                if (isset($data['microsite_id'])) {
                    $data['mobile_promo_banner_3'] = $model->getData('mobile_promo_banner_3');
                }
            }
        }
        return $data;
    }
}
