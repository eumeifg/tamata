<?php
/**
 * Ktpl
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the KrishTechnolabs.com license that is
 * available through the world-wide-web at this URL:
 * https://https://www.KrishTechnolabs.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Ktpl
 * @package     Ktpl_Productslider
 * @copyright   Copyright (c) Ktpl (https://https://www.KrishTechnolabs.com//)
 * @license     https://https://www.KrishTechnolabs.com/LICENSE.txt
 */

namespace Ktpl\Productslider\Controller\Adminhtml\Slider;

use Exception;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\DateTime\Filter\Date;
use Ktpl\Productslider\Controller\Adminhtml\Slider;
use Ktpl\Productslider\Model\SliderFactory;
use Zend_Filter_Input;
use Magento\Framework\Filesystem;
use Magento\MediaStorage\Model\File\UploaderFactory;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class Save
 * @package Ktpl\Productslider\Controller\Adminhtml\Slider
 */
class Save extends Slider
{
    /**
     * Date filter
     *
     * @var Date
     */
    protected $_dateFilter;

    /**
     * @var DataPersistorInterface
     */
    protected $dataPersistor;

    /**
     * @var UploaderFactory
     */
    protected $uploaderFactory;

    /**
     * @var Filesystem\Directory\WriteInterface
     */
    protected $mediaDirectory;

    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @param Context $context
     * @param SliderFactory $sliderFactory
     * @param Registry $coreRegistry
     * @param Date $dateFilter
     * @param DataPersistorInterface $dataPersistor
     * @param Filesystem $filesystem
     * @param UploaderFactory $uploaderFactory
     * @param StoreManagerInterface $storeManager
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function __construct(
        Context $context,
        SliderFactory $sliderFactory,
        Registry $coreRegistry,
        Date $dateFilter,
        DataPersistorInterface $dataPersistor,
        Filesystem $filesystem,
        UploaderFactory $uploaderFactory,
        StoreManagerInterface $storeManager
    ) {
        $this->_dateFilter   = $dateFilter;
        $this->dataPersistor = $dataPersistor;
        $this->uploaderFactory = $uploaderFactory;
        $this->mediaDirectory = $filesystem->getDirectoryWrite(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA);
        parent::__construct($context, $sliderFactory, $coreRegistry);
        $this->_storeManager = $storeManager;
    }

    /**
     * @return ResponseInterface|ResultInterface|void
     */
    public function execute()
    {
        if ($data = $this->getRequest()->getPost('slider')) {
            if (isset($this->getRequest()->getFiles('slider')['brand_vendor']) && !empty($files = $this->getRequest()->getFiles('slider')['brand_vendor'])) {
                foreach ($files as $key => $file) {
                    if (!empty($file['image']['name'])) {
                        $fileUploader = $this->uploaderFactory->create(['fileId' => $file['image']]);
                        $fileUploader->setAllowedExtensions(['jpg', 'jpeg', 'png']);
                        $fileUploader->setAllowRenameFiles(true);
                        $fileUploader->setAllowCreateFolders(true);
                        $fileUploader->setFilesDispersion(true);
                        $result = $fileUploader->save($this->mediaDirectory->getAbsolutePath('mobile_banner/image'));
                        if (!empty($result['file'])) {
                            $data['brand_vendor'][$key]['images'] = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA).'mobile_banner/image'.$result['file'];
                        }
                        unset($data['brand_vendor'][$key]['image']);
                    } else {
                        $data['brand_vendor'][$key]['images'] = $data['brand_vendor'][$key]['image'];
                        unset($data['brand_vendor'][$key]['image']);
                    }
                }
            }
            //custom product banner image upload
            $customImage = (isset($this->getRequest()->getFiles('slider')['new_items_banner']) && !empty($this->getRequest()->getFiles('slider')['new_items_banner'])) ? $this->getRequest()->getFiles('slider')['new_items_banner'] : '';

            if (isset($customImage['name']) && !empty($customImage['name'])) {
                $result = $this->uploadFile($data, $customImage);
                if ($result) {
                    $data['new_items_banner'] = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA).'mobile_banner/image'.$result;
                }
            }

            try {
                $data   = $this->_filterData($data);
                $slider = $this->_initSlider();

                $validateResult = $slider->validateData(new DataObject($data));
                if ($validateResult !== true) {
                    foreach ($validateResult as $errorMessage) {
                        $this->messageManager->addErrorMessage($errorMessage);
                    }
                    $this->_session->setPageData($data);
                    $this->dataPersistor->set('ktpl_productslider_slider', $data);
                    $this->_redirect('*/*/edit', ['id' => $slider->getId()]);

                    return;
                }

                $slider->addData($data)
                    ->save();
                $this->messageManager->addSuccessMessage(__('The Slider has been saved.'));
                $this->_session->setKtplProductsliderSliderData(false);
                $this->dataPersistor->clear('ktpl_productslider_slider');
                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', ['id' => $slider->getId()]);

                    return;
                }
                $this->_redirect('*/*/');

                return;
            } catch (Exception $e) {
                $this->messageManager->addExceptionMessage(
                    $e,
                    __('Something went wrong while saving the Slider. %1', $e->getMessage())
                );
                $this->_getSession()->setKtplProductsliderSliderData($data);
                $this->dataPersistor->set('ktpl_productslider_slider', $data);
                $sliderId = $this->getRequest()->getParam('id');
                if (empty($sliderId)) {
                    $this->_redirect('*/*/new');
                } else {
                    $this->_redirect('*/*/edit', ['id' => $sliderId->getId()]);
                }

                return;
            }
        }

        $this->_redirect('*/*/');
    }

    /**
     * filter values
     *
     * @param array $data
     *
     * @return array
     */
    protected function _filterData($data)
    {
        $inputFilter = new Zend_Filter_Input(['from_date' => $this->_dateFilter], [], $data);
        $data        = $inputFilter->getUnescaped();

        if (isset($data['responsive_items'])) {
            unset($data['responsive_items']['__empty']);
        }

        $data['product_ids'] = '';
        if ($products = $this->getRequest()->getParam('products')) {
            $data['product_ids'] = $products;
        }

        if (isset($data['brand_vendor'])) {
            unset($data['brand_vendor']['__empty']);
        }
        return $data;
    }

    public function uploadFile($data, $fileId) {
        try {
            $fileUploader = $this->uploaderFactory->create(['fileId' => $fileId]);
            $fileUploader->setAllowedExtensions(['jpg', 'jpeg', 'png']);
            $fileUploader->setAllowRenameFiles(true);
            $fileUploader->setAllowCreateFolders(true);
            $fileUploader->setFilesDispersion(true);
            $result = $fileUploader->save($this->mediaDirectory->getAbsolutePath('mobile_banner/image'));
            if ($result['error'] ==0) {
                return $result['file'];
            }
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage(
                $e,
                __('Something went wrong while saving the Slider. %1', $e->getMessage())
            );
            $this->_getSession()->setKtplProductsliderSliderData($data);
            $this->dataPersistor->set('ktpl_productslider_slider', $data);
            $sliderId = $this->getRequest()->getParam('id');
            if (empty($sliderId)) {
                $this->_redirect('*/*/new');
            } else {
                $this->_redirect('*/*/edit', ['id' => $sliderId]);
            }
            return;
        }
    }
}
