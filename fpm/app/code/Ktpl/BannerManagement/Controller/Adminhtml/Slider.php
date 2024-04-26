<?php
/*
 * Copyright © 2019 Krish Technolabs. All rights reserved.
 * See COPYING.txt for license details
 */

namespace Ktpl\BannerManagement\Controller\Adminhtml;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;
use Ktpl\BannerManagement\Model\SliderRepository;
use Ktpl\BannerManagement\Model\SliderFactory;

/**
 * Class Slider
 *
 * @package Ktpl\BannerManagement\Controller\Adminhtml
 */
abstract class Slider extends Action
{
    /**
     * Slider Repository
     *
     * @var \Ktpl\BannerManagement\Model\SliderRepository
     */
    protected $sliderRepository;

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;

    /**
     * Slider constructor.
     *
     * @param SliderRepository $sliderRepository
     * @param SliderFactory $sliderFactory
     * @param Registry      $coreRegistry
     * @param Context       $context
     */
    public function __construct(
        SliderRepository $sliderRepository,
        SliderFactory $sliderFactory,
        Registry $coreRegistry,
        Context $context
    ) {
        $this->sliderRepository = $sliderRepository;
        $this->sliderFactory = $sliderFactory->create();
        $this->coreRegistry  = $coreRegistry;

        parent::__construct($context);
    }

    /**
     * Init Slider
     *
     * @return \Ktpl\BannerManagement\Model\SliderRepository
     */
    protected function initSlider()
    {
        $sliderId = (int)$this->getRequest()->getParam('id');
        /**
         * @var \Ktpl\BannerManagement\Model\Slider $slider
        */
        $slider = $this->sliderFactory;
        if ($sliderId) {
            $slider = $this->sliderRepository->getById($sliderId);
        }
        $this->coreRegistry->register('bannerslider_slider', $slider);

        return $slider;
    }
}
