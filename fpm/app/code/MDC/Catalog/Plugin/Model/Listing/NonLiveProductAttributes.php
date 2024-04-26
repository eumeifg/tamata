<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Catalog
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace MDC\Catalog\Plugin\Model\Listing;

class NonLiveProductAttributes
{

    /**
     * @var \MDC\Catalog\Helper\Listing\Data
     */
    protected $listingHelper;

    /**
     * NonLiveProductAttributes constructor.
     * @param \MDC\Catalog\Helper\Listing\Data $listingHelper
     */
    public function __construct(
        \MDC\Catalog\Helper\Listing\Data $listingHelper
    ) {
        $this->listingHelper = $listingHelper;
    }

    /**
     * Add all attributes and apply pricing logic to products collection
     * to get correct values in different products lists.
     * E.g. crosssells, upsells, new products, recently viewed
     *
     * @param \Magento\Catalog\Model\ResourceModel\Product\Collection $collection
     * @param array $attributes
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    public function before_addProductAttributesAndPrices(
        \Magedelight\Catalog\Model\VendorProduct\Listing\ApprovedProducts $subject,
        \Magento\Catalog\Model\ResourceModel\Product\Collection $collection,
        $attributes = ['name','thumbnail']
    ) {
        $attributes = array_merge($attributes, $this->listingHelper->getAttributesList());
        return [$collection, $attributes];
    }
}
