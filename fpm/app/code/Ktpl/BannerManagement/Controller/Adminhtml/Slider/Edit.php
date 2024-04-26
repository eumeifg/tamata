<?php
/*
 * Copyright © 2019 Krish Technolabs. All rights reserved.
 * See COPYING.txt for license details
 */

namespace Ktpl\BannerManagement\Controller\Adminhtml\Slider;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;
use Ktpl\BannerManagement\Controller\Adminhtml\Slider;
use Ktpl\BannerManagement\Model\SliderRepository;
use Ktpl\BannerManagement\Model\SliderFactory;

/**
 * Class Edit
 *
 * @package Ktpl\BannerManagement\Controller\Adminhtml\Slider
 */
class Edit extends Slider
{
    const ADMIN_RESOURCE = 'Ktpl_BannerSlider::slider';

    /**
     * Page factory
     *
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * Edit constructor.
     *
     * @param PageFactory   $resultPageFactory
     * @param SliderFactory $sliderFactory
     * @param Registry      $registry
     * @param Context       $context
     */
    public function __construct(
        PageFactory $resultPageFactory,
        SliderRepository $sliderRepository,
        SliderFactory $sliderFactory,
        Registry $registry,
        Context $context
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->sliderRepository = $sliderRepository;

        parent::__construct($sliderRepository, $sliderFactory, $registry, $context);
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Page|\Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Redirect|\Magento\Framework\Controller\ResultInterface|\Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('slider_id');
        /**
         * @var \Ktpl\BannerManagement\Model\Slider $slider
        */
         $slider = $this->initSlider();

       // if ($id) {

        if (!$slider) {
            $this->messageManager->addError(__('This Slider no longer exists.'));
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setPath(
                'bannermanagement/*/edit',
                [
                    'slider_id' => $slider->getId(),
                    '_current'  => true
                ]
            );

            return $resultRedirect;
        }
       // }

        $data = $this->_session->getData('bannerslider_slider_data', true);

        if (!empty($data)) {
            $slider->setData($data);
        }

        /**
 * @var \Magento\Backend\Model\View\Result\Page|\Magento\Framework\View\Result\Page $resultPage
*/
        $resultPage = $this->resultPageFactory->create();
       // $resultPage->setActiveMenu('Ktpl_BannerManagement::ktpl');
        $resultPage->getConfig()->getTitle()
            ->set(__('Sliders'))
            ->prepend($slider->getId() ? $slider->getName() : __('New Slider'));

        return $resultPage;
    }
}
