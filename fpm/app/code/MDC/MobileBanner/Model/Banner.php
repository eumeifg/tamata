<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace MDC\MobileBanner\Model;

use MDC\MobileBanner\Api\Data\BannerInterface;
use MDC\MobileBanner\Api\Data\BannerInterfaceFactory;
use Magento\Framework\Api\DataObjectHelper;

class Banner extends \Magento\Framework\Model\AbstractModel
{

    protected $_eventPrefix = 'mdc_mobilebanner_banner';
    protected $dataObjectHelper;

    protected $bannerDataFactory;


    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param BannerInterfaceFactory $bannerDataFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param \MDC\MobileBanner\Model\ResourceModel\Banner $resource
     * @param \MDC\MobileBanner\Model\ResourceModel\Banner\Collection $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        BannerInterfaceFactory $bannerDataFactory,
        DataObjectHelper $dataObjectHelper,
        \MDC\MobileBanner\Model\ResourceModel\Banner $resource,
        \MDC\MobileBanner\Model\ResourceModel\Banner\Collection $resourceCollection,
        array $data = []
    ) {
        $this->bannerDataFactory = $bannerDataFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Retrieve banner model with banner data
     * @return BannerInterface
     */
    public function getDataModel()
    {
        $bannerData = $this->getData();
        
        $bannerDataObject = $this->bannerDataFactory->create();
        $this->dataObjectHelper->populateWithArray(
            $bannerDataObject,
            $bannerData,
            BannerInterface::class
        );
        
        return $bannerDataObject;
    }
}

