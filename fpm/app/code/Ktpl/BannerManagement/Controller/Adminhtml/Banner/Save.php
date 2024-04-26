<?php
/*
 * Copyright © 2019 Krish Technolabs. All rights reserved.
 * See COPYING.txt for license details
 */

namespace Ktpl\BannerManagement\Controller\Adminhtml\Banner;

use Magento\Backend\App\Action\Context;
use Magento\Backend\Helper\Js;
use Magento\Framework\Registry;
use Ktpl\BannerManagement\Controller\Adminhtml\Banner;
use Ktpl\BannerManagement\Helper\Image;
use Ktpl\BannerManagement\Model\BannerRepository;
use Ktpl\BannerManagement\Model\BannerFactory;

/**
 * Class Save
 *
 * @package Ktpl\BannerManagement\Controller\Adminhtml\Banner
 */
class Save extends Banner
{
    /**
     * Image Helper
     *
     * @var \Ktpl\BannerManagement\Helper\Image
     */
    protected $imageHelper;

    /**
     * JS helper
     *
     * @var \Magento\Backend\Helper\Js
     */
    public $jsHelper;

    /**
     * Save constructor.
     *
     * @param Image         $imageHelper
     * @param BannerFactory $bannerFactory
     * @param Registry      $registry
     * @param Js            $jsHelper
     * @param Context       $context
     */
    public function __construct(
        Image $imageHelper,
        Registry $registry,
        Js $jsHelper,
        Context $context,
        BannerRepository $bannerRepository,
        BannerFactory $bannerFactory
    ) {
        $this->imageHelper = $imageHelper;
        $this->jsHelper    = $jsHelper;
        $this->bannerFactory  = $bannerFactory->create();

        parent::__construct($context, $registry, $bannerRepository, $bannerFactory);
    }


    protected function setimagePostData(array $rawData)
    {
        $data = $rawData;
        if (isset($data['image']) && is_array($data['image'])) {
            if (!empty($data['image']['delete'])) {
                $data['image'] = null;
            } else {
                if (isset($data['image'][0]['name']) && isset($data['image'][0]['tmp_name'])) {
                    $data['image'] = $data['image'][0]['name'];
                } elseif (isset($data['image'][0]['name'])) {
                    $data['image'] = $data['image'][0]['name'];
                }
                else {
                    unset($data['image']);
                }
            }
        }
        return $data;
    }
    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Redirect|\Magento\Framework\Controller\ResultInterface
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();

        if ($data = $this->getRequest()->getPost('general')) {

            $banner = $this->initBanner();

             $isUploaded = $this->imageHelper->uploadImage($data, 'image', Image::TEMPLATE_MEDIA_PATH, $banner->getImage());

            if (!empty($_FILES['image']['name']) && !$isUploaded) {
                $this->messageManager->addError(__('Something went wrong while saving the Banner.'));
                $resultRedirect->setPath('bannermanagement/*/');
                return $resultRedirect;
            }
            $data=$this->setimagePostData($this->getRequest()->getPost('general'));

             $banner->addData($data);
            try {
                $banner->save();
                $this->messageManager->addSuccess(__('The Banner has been saved.'));
                $this->_session->setKtplBannerSliderBannerData(false);
                if ($this->getRequest()->getParam('back')) {
                    $resultRedirect->setPath(
                        'bannermanagement/*/edit',
                        [
                            'id' => $banner->getId(),
                            '_current'  => true
                        ]
                    );

                    return $resultRedirect;
                }
                $resultRedirect->setPath('bannermanagement/*/');

                return $resultRedirect;
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\RuntimeException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addException($e, __($e->getMessage()));
            }

            $this->_getSession()->setData('ktpl_bannerSlider_banner_data', $data);
            $resultRedirect->setPath(
                'bannermanagement/*/edit',
                [
                    'id' => $banner->getId(),
                    '_current'  => true
                ]
            );

            return $resultRedirect;
        }

        $resultRedirect->setPath('bannermanagement/*/');

        return $resultRedirect;
    }
}
