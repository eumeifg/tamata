<?php
/*
 * Copyright © 2019 Krish Technolabs. All rights reserved.
 * See COPYING.txt for license details
 */

namespace Ktpl\BannerManagement\Model\Config\Source;

use Ktpl\BannerManagement\Model\ResourceModel\Slider\CollectionFactory;
use Magento\Framework\Option\ArrayInterface;

class SliderList implements ArrayInterface
{

    /**
     * @var SliderFactory
     */
    public $sliderFactory;
    /**
     * Data constructor.
     *
     * @param SliderFactory $sliderFactory
     */
    public function __construct(
        CollectionFactory $sliderFactory
    ) {
        $this->sliderFactory = $sliderFactory->create();
    }
    /**
     * to option array
     *
     * @return array
     */
    public function toOptionArray()
    {
        $collection = $this->getSliderData();
        $options = [];
        foreach ($collection as $value) {
            $options[] = [
                'value' => $value->getSliderId(),
                'label' => $value->getName()
            ];
        }
        return $options;
    }
    public function getSliderData()
    {
        /**
         * @var Collection $collection
        */
        $collection = $this->sliderFactory->addFieldToFilter('status', 1);
        return $collection;
    }
}
