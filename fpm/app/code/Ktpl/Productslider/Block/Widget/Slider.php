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

namespace Ktpl\Productslider\Block\Widget;

use Magento\Framework\View\Element\Template;
use Magento\Widget\Block\BlockInterface;
use Magento\Catalog\Block\Product\Context;
use Ktpl\Productslider\Helper\Data;
use Ktpl\Productslider\Model\Config\Source\ProductType;

/**
 * Class Slider
 * @package Ulmod\Productslider\Block\Widget
 */
class Slider extends Template implements BlockInterface
{
    /**
     * Default template to use for slider widget
     */
    protected $_template = 'Ktpl_Productslider::widget/slider.phtml';

    /**
     * @var Data
     */
    protected $helperData;

    /**
     * @var ProductType
     */
    protected $productType;

    /**
     * Data constructor.
     * @param Data $helperData
     */
    public function __construct(
        Context $context,
        Data $helperData,
        ProductType $productType,
        array $data = []
    ) {
        $this->helperData = $helperData;
        $this->productType = $productType;
        parent::__construct($context, $data);
    }

    public function getSliderCollection()
    {

        /** @var Collection $collection */
        $collection = $this->helperData->getActiveSliders();
        $collection->addFieldToFilter('slider_id', $this->getData('slider'));
        if ($collection->getFirstItem()) {
            return $collection->getFirstItem();
        }

        return false;
    }

    public function getProductType($slider)
    {
        return $this->productType->getBlockMap($slider->getProductType());
    }
}
