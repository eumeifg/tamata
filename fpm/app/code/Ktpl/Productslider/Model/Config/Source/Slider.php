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

namespace Ktpl\Productslider\Model\Config\Source;

use Ktpl\Productslider\Model\SliderFactory;

/**
 * Class Slider
 * @package Ktpl\Productslider\Model\Config\Source
 */
class Slider implements \Magento\Framework\Option\ArrayInterface
{
   /**
    * @var SliderFactory
    */
    protected $productsliderFactory;

    /**
     * @param SliderFactory $productsliderFactory
     */
    public function __construct(
        SliderFactory $productsliderFactory
    ) {
        $this->productsliderFactory = $productsliderFactory;
    }

    /**
     * Get sliders
     * @return void
     */
    public function getSliders()
    {
        $sliderModel = $this->productsliderFactory->create();
        return $sliderModel->getCollection()->getData();
    }

    /**
     * To option array
     * @return array
     */
    public function toOptionArray()
    {
        $sliders = [];
        foreach ($this->getSliders() as $slider) {
            array_push($sliders, [
                'value' => $slider['slider_id'],
                'label' => $slider['name']
            ]);
        }
        return $sliders;
    }
}
