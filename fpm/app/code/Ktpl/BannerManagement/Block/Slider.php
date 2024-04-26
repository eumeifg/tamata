<?php
/*
 * Copyright © 2019 Krish Technolabs. All rights reserved.
 * See COPYING.txt for license details
 */

namespace Ktpl\BannerManagement\Block;

use Magento\Cms\Model\Template\FilterProvider;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\View\Element\Template;
use Ktpl\BannerManagement\Helper\Data as bannerHelper;

/**
 * Class Slider
 * @package Ktpl\BannerManagement\Block
 */
class Slider extends Template
{
    /**
     * @type \Ktpl\BannerManagement\Helper\Data
     */
    public $helperData;

    public $sliderID;


    /**
     * @type \Magento\Store\Model\StoreManagerInterface
     */
    protected $store;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $_date;

    /**
     * @var FilterProvider
     */
    public $filterProvider;

    /**
     * Slider constructor.
     *
     * @param Template\Context $context
     * @param bannerHelper $helperData
     * @param CustomerRepositoryInterface $customerRepository
     * @param DateTime $dateTime
     * @param FilterProvider $filterProvider
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        bannerHelper $helperData,
        CustomerRepositoryInterface $customerRepository,
        DateTime $dateTime,
        FilterProvider $filterProvider,
        array $data = []
    ) {
        $this->helperData         = $helperData;
        $this->customerRepository = $customerRepository;
        $this->store              = $context->getStoreManager();
        $this->_date              = $dateTime;
        $this->filterProvider     = $filterProvider;
        parent::__construct($context, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function _construct()
    {
        parent::_construct();
    }

    /**
     * Set Slider Id
     *
     * @return string
     */
    public function setSliderId($id)
    {
        $this->sliderID = $id;
    }

    /**
     * Get Slider Id
     * @return string
     */
    public function getSliderId()
    {
        return $this->sliderID;
    }

    /**
     * @param $content
     *
     * @return string
     * @throws \Exception
     */
    public function getPageFilter($content)
    {
        return $this->filterProvider->getPageFilter()->filter($content);
    }

    /**
     * @return array|\Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
     */
    public function getSliderCollection()
    {
        if (!empty($this->getSliderName())) {
            $collection = $this->helperData->getSliderByName($this->getSliderName());
        } else {
            $collection = $this->helperData->getSliderById($this->getSliderId());
        }

        $slider = [];
        foreach ($collection as $slider) {
            $this->setSliderId($slider->getSliderId());
        }
        return $slider;
    }

    /**
     * @return false|string
     */
    public function getBannerOptions()
    {
        $slider= $this->getSliderCollection();

        return $this->helperData->getBannerOptions($slider);
    }

    /**
     * @return array|\Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
     */
    public function getBannerCollection()
    {
        $banner_id = $this->getSliderId();

        if (!empty($banner_id)) {
            $collection = $this->helperData->getBannerCollection($banner_id)->addFieldToFilter('status', 1);
        } else {
            $collection = [];
        }
        return $collection;
    }

    /**
     * @return array
     */
    public function getDeviceClass()
    {
        $slider= $this->getSliderCollection();
        return $this->helperData->getDeviceClass($slider);
    }
}
