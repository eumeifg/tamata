<?php
/*
 * Copyright © 2019 Krish Technolabs. All rights reserved.
 * See COPYING.txt for license details
 */

namespace Ktpl\BannerManagement\Controller\Adminhtml\Slider;

use Magento\Backend\App\Action\Context;
use Magento\Backend\Helper\Js;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\DateTime\Filter\Date;
use Ktpl\BannerManagement\Controller\Adminhtml\Slider;
use Ktpl\BannerManagement\Model\SliderRepository;
use Ktpl\BannerManagement\Model\SliderFactory;

/**
 * Class Save
 *
 * @package Ktpl\BannerManagement\Controller\Adminhtml\Slider
 */
class Save extends Slider
{
    /**
     * JS helper
     *
     * @var \Magento\Backend\Helper\Js
     */
    protected $jsHelper;

    /**
     * Date filter
     *
     * @var \Magento\Framework\Stdlib\DateTime\Filter\Date
     */
    protected $dateFilter;

    /**
     * Save constructor.
     *
     * @param Js            $jsHelper
     * @param SliderRepository $sliderRepository
     * @param Registry      $registry
     * @param Context       $context
     * @param Date          $dateFilter
     */
    public function __construct(
        Js $jsHelper,
        SliderRepository $sliderRepository,
        SliderFactory $sliderFactory,
        Registry $registry,
        Context $context,
        Date $dateFilter
    ) {
        $this->jsHelper    = $jsHelper;
        $this->dateFilter = $dateFilter;

        parent::__construct($sliderRepository, $sliderFactory, $registry, $context);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Redirect|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();

        if ($data = $this->getRequest()->getPostValue()) {
            $data   = $this->_filterData($data);

            $slider = $this->sliderFactory;
            $sliderId = (int)$this->getRequest()->getParam('slider_id');
            
            if ($sliderId) {
                $slider->load($sliderId);
            }
            $bannersData=isset($data['slider_banner_listing'])?$data['slider_banner_listing']:[];
            $resultData=[];

            if (!empty($bannersData)) {
                foreach ($bannersData as $key => $value) {

                    $resultData[]=$value['banner_id'];
                }
                $slider->setBannersData($resultData);
            }
                $slider->setName($data['name']);
                $slider->setPriority($data['priority']);
                $slider->setResponsiveItems($data['responsive_items']);
                $slider->setStatus($data['status']);
                $slider->setEffect($data['effect']);
                $slider->setIsResponsive($data['is_responsive']);
                $slider->setAutowidth($data['autowidth']);
                $slider->setAutoheight($data['autoheight']);
                $slider->setLoop($data['loop']);
                $slider->setNav($data['nav']);
                $slider->setDots($data['dots']);
                $slider->setLazyLoad($data['lazyload']);
                $slider->setAutoplay($data['autoplay']);
                $slider->setAutoplaytimeout($data['autoplaytimeout']);
                $slider->setAutoplayhoverpause($data['autoplayhoverpause']);
                $slider->setDesign($data['design']);
                $slider->setStoreids($data['store_ids']);
                $slider->setCustomerGroupIds($data['customer_group_ids']);
                $slider->setVisibleDevices($data['visible_devices']);
                $slider->setFromDate($data['from_date']);
                $slider->setToDate($data['to_date']);

            try {
                $slider->save();
                $this->messageManager->addSuccess(__('The Slider has been saved.'));
                $this->_session->setKtplBannerSliderSliderData(false);
                if ($this->getRequest()->getParam('back')) {
                    $resultRedirect->setPath(
                        'bannermanagement/*/edit',
                        [
                            'id' => $slider->getId(),
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

            $this->_getSession()->setKtplBannerSliderSliderData($data);
            $resultRedirect->setPath(
                'bannermanagement/*/edit',
                [
                    'id' => $slider->getId(),
                    '_current'  => true
                ]
            );

            return $resultRedirect;
        }

        $resultRedirect->setPath('bannermanagement/*/');

        return $resultRedirect;
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
        $inputFilter = new \Zend_Filter_Input(['from_date' => $this->dateFilter,], [], $data);
        $data        = $inputFilter->getUnescaped();
        return $data;
    }
}
