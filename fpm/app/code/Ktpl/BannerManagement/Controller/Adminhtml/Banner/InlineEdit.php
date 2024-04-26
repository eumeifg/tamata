<?php
/*
 * Copyright © 2019 Krish Technolabs. All rights reserved.
 * See COPYING.txt for license details
 */

namespace Ktpl\BannerManagement\Controller\Adminhtml\Banner;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Ktpl\BannerManagement\Model\BannerRepository;

/**
 * Class InlineEdit
 *
 * @package Ktpl\BannerManagement\Controller\Adminhtml\Banner
 */
class InlineEdit extends Action
{
    /**
     * JSON Factory
     *
     * @var JsonFactory
     */
    protected $jsonFactory;

    /**
     * Banner Factory
     *
     * @var BannerRepository
     */
    protected $bannerRepository;

    /**
     * constructor
     *
     * @param JsonFactory   $jsonFactory
     * @param BannerRepository $bannerRepository
     * @param Context       $context
     */
    public function __construct(
        JsonFactory $jsonFactory,
        BannerRepository $bannerRepository,
        Context $context
    ) {
        $this->jsonFactory   = $jsonFactory;
        $this->bannerRepository = $bannerRepository;

        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /**
 * @var \Magento\Framework\Controller\Result\Json $resultJson
*/
        $resultJson = $this->jsonFactory->create();
        $error      = false;
        $messages   = [];
        $postItems  = $this->getRequest()->getParam('items', []);
        if (!($this->getRequest()->getParam('isAjax') && count($postItems))) {
            return $resultJson->setData(
                [
                'messages' => [__('Please correct the data sent.')],
                'error'    => true,
                ]
            );
        }
        foreach (array_keys($postItems) as $bannerId) {
            /**
 * @var \Ktpl\BannerManagement\Model\Banner $banner
*/
            $banner = $this->bannerRepository->getById($bannerId);
            try {
                $bannerData = $postItems[$bannerId];//todo: handle dates
                $banner->addData($bannerData);
                $banner->save();
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $messages[] = $this->getErrorWithBannerId($banner, $e->getMessage());
                $error      = true;
            } catch (\RuntimeException $e) {
                $messages[] = $this->getErrorWithBannerId($banner, $e->getMessage());
                $error      = true;
            } catch (\Exception $e) {
                $messages[] = $this->getErrorWithBannerId(
                    $banner,
                    __('Something went wrong while saving the Banner.')
                );
                $error      = true;
            }
        }

        return $resultJson->setData(
            [
            'messages' => $messages,
            'error'    => $error
            ]
        );
    }

    /**
     * Add Banner id to error message
     *
     * @param \Ktpl\BannerManagement\Model\Banner $banner
     * @param string                          $errorText
     *
     * @return string
     */
    protected function getErrorWithBannerId(\Ktpl\BannerManagement\Model\BannerRepository $bannerRepository, $errorText)
    {
        return '[Banner ID: ' . $banner->getId() . '] ' . $errorText;
    }
}
