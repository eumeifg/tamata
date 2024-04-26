<?php
/*
 * Copyright © 2019 Krish Technolabs. All rights reserved.
 * See COPYING.txt for license details
 */

namespace Ktpl\BannerManagement\Controller\Adminhtml\Slider;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Ktpl\BannerManagement\Model\SliderRepository;

/**
 * Class InlineEdit
 *
 * @package Ktpl\BannerManagement\Controller\Adminhtml\Slider
 */
class InlineEdit extends \Magento\Backend\App\Action
{
    /**
     * JSON Factory
     *
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $jsonFactory;

    /**
     * Banner Factory
     *
     * @var \Ktpl\BannerManagement\Model\SliderRepository
     */
    protected $sliderRepository;

    /**
     * InlineEdit constructor.
     *
     * @param JsonFactory   $jsonFactory
     * @param SliderFactory $sliderFactory
     * @param Context       $context
     */
    public function __construct(
        JsonFactory $jsonFactory,
        SliderRepository $sliderRepository,
        Context $context
    ) {
        $this->jsonFactory   = $jsonFactory;
        $this->sliderRepository = $sliderRepository;

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
        foreach (array_keys($postItems) as $sliderId) {
            /**
             * @var \Ktpl\BannerManagement\Model\Slider $slider
            */
            $slider = $this->sliderRepository->getById($sliderId);
            try {
                $sliderData = $postItems[$sliderId];
                $slider->addData($sliderData);
                $slider->save();
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $messages[] = $this->getErrorWithSliderId($slider, $e->getMessage());
                $error      = true;
            } catch (\RuntimeException $e) {
                $messages[] = $this->getErrorWithSliderId($slider, $e->getMessage());
                $error      = true;
            } catch (\Exception $e) {
                $messages[] = $this->getErrorWithSliderId(
                    $slider,
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
     * Add slider id to error message
     *
     * @param \Ktpl\BannerManagement\Model\Slider $slider
     * @param $errorText
     *
     * @return string
     */
    protected function getErrorWithSliderId(\Ktpl\BannerManagement\Model\SliderRepository $slider, $errorText)
    {
        return '[Slider ID: ' . $slider->getId() . '] ' . $errorText;
    }
}
