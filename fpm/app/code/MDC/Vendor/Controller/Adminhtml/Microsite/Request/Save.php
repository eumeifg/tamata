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
namespace MDC\Vendor\Controller\Adminhtml\Microsite\Request;

class Save extends \Magedelight\Vendor\Controller\Adminhtml\Microsite\Request\Save
{

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

            $path = $this->filesystem->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA)
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

            if (isset($data['microsite_id'])) {
                $model->load($data['microsite_id']);
            }
            try {
                if (!isset($data['banner']['delete'])) {
                    if (!empty($this->getRequest()->getFiles('banner')['name'])) {
                            $data['banner'] = $this->uploadFile($data, 'banner');
                    } else {
                        if (isset($data['microsite_id'])) {
                            $data['banner'] = $model->getData('banner');
                        }
                    }
                } else {
                    $data['banner'] = '';
                }
                
                if (!isset($data['promo_banner_1']['delete'])) {
                    if (!empty($this->getRequest()->getFiles('promo_banner_1')['name'])) {
                            $data['promo_banner_1'] = $this->uploadFile($data, 'promo_banner_1');
                    } else {
                        if (isset($data['microsite_id'])) {
                            $data['promo_banner_1'] = $model->getData('promo_banner_1');
                        }
                    }
                } else {
                    $data['promo_banner_1'] = '';
                }
                
                if (!isset($data['promo_banner_2']['delete'])) {
                    if (!empty($this->getRequest()->getFiles('promo_banner_2')['name'])) {
                            $data['promo_banner_2'] = $this->uploadFile($data, 'promo_banner_2');
                    } else {
                        if (isset($data['microsite_id'])) {
                            $data['promo_banner_2'] = $model->getData('promo_banner_2');
                        }
                    }
                } else {
                    $data['promo_banner_2'] = '';
                }
                
                if (!isset($data['promo_banner_3']['delete'])) {
                    if (!empty($this->getRequest()->getFiles('promo_banner_3')['name'])) {
                            $data['promo_banner_3'] = $this->uploadFile($data, 'promo_banner_3');
                    } else {
                        if (isset($data['microsite_id'])) {
                            $data['promo_banner_3'] = $model->getData('promo_banner_3');
                        }
                    }
                } else {
                    $data['promo_banner_3'] = '';
                }

                if (!isset($data['mobile_promo_banner_1']['delete'])) {
                    if (!empty($this->getRequest()->getFiles('mobile_promo_banner_1')['name'])) {
                            $data['mobile_promo_banner_1'] = $this->uploadFile($data, 'mobile_promo_banner_1');
                    } else {
                        if (isset($data['microsite_id'])) {
                            $data['mobile_promo_banner_1'] = $model->getData('mobile_promo_banner_1');
                        }
                    }
                } else {
                    $data['mobile_promo_banner_1'] = '';
                }

                if (!isset($data['mobile_promo_banner_2']['delete'])) {
                    if (!empty($this->getRequest()->getFiles('mobile_promo_banner_2')['name'])) {
                            $data['mobile_promo_banner_2'] = $this->uploadFile($data, 'mobile_promo_banner_2');
                    } else {
                        if (isset($data['microsite_id'])) {
                            $data['mobile_promo_banner_2'] = $model->getData('mobile_promo_banner_2');
                        }
                    }
                } else {
                    $data['mobile_promo_banner_2'] = '';
                }

                if (!isset($data['mobile_promo_banner_3']['delete'])) {
                    if (!empty($this->getRequest()->getFiles('mobile_promo_banner_3')['name'])) {
                            $data['mobile_promo_banner_3'] = $this->uploadFile($data, 'mobile_promo_banner_3');
                    } else {
                        if (isset($data['microsite_id'])) {
                            $data['mobile_promo_banner_3'] = $model->getData('mobile_promo_banner_3');
                        }
                    }
                } else {
                    $data['mobile_promo_banner_3'] = '';
                }
                
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
                if ($this->getRequest()->getParam('microsite_id')) {
                    return $resultRedirect->setPath('*/*/edit', [
                        'id' => $this->getRequest()->getParam('microsite_id')
                    ]);
                }
                return $resultRedirect->setPath('*/*/');
            }
            
            if (array_key_exists('stores', $data)) {
                unset($data['stores']);
            }

            $model->setData($data);

            try {
                $model->save();
                $this->messageManager->addSuccess(__('You saved this microsite.'));
                $this->_getSession()->setFormData(false);
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
                $this->messageManager->addException($e, __('Something went wrong while saving the microsite.'.$e->getMessage()));
            }

            $this->_getSession()->setFormData($data);
            return $resultRedirect->setPath('*/*/edit', ['id' => $this->getRequest()->getParam('microsite_id')]);
        }
        return $resultRedirect->setPath('*/*/');
    }
    
    /**
     * Upload file.
     * @param array $postData
     */
    protected function uploadFile($postData = [], $field = '')
    {
        $subPath = ($field == 'banner')?'microsite':'microsite/promo_banners';
        if ($this->getRequest()->getFiles($field) && !empty($this->getRequest()->getFiles($field)['tmp_name'])) {
            return $this->uploadModel->uploadFileAndGetName($field, $this->imageModel->getBaseDir($subPath), $postData);
        }
    }
}
