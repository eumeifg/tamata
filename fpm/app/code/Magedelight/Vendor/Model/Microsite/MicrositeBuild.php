<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Vendor
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Vendor\Model\Microsite;

use Magedelight\Vendor\Api\Data\MicrositeBuildDataInterface;

/**
 * Microsite Build getter-setter.
 */
class MicrositeBuild extends \Magento\Framework\DataObject implements MicrositeBuildDataInterface
{

    /**
     * {@inheritdoc}
     */
    public function getRatingData()
    {
        return $this->getData('rating_data');
    }

    /**
     * {@inheritdoc}
     */
    public function setRatingData($ratingData)
    {
        return $this->setData('rating_data', $ratingData);
    }

    /**
     * {@inheritdoc}
     */
    public function getVendorData()
    {
        return $this->getData('vendor_data');
    }

    /**
     * {@inheritdoc}
     */
    public function setVendorData($vendorData)
    {
        return $this->setData('vendor_data', $vendorData);
    }

    /**
     * {@inheritdoc}
     */
    public function getMicrositeData()
    {
        return $this->getData('microsite_data');
    }

    /**
     * {@inheritdoc}
     */
    public function setMicrositeData($micrositeData)
    {
        return $this->setData('microsite_data', $micrositeData);
    }

    /**
     * {@inheritdoc}
     */
    public function getProductsData()
    {
        return $this->getData('products_data');
    }

    /**
     * {@inheritdoc}
     */
    public function setProductsData($productsData)
    {
        return $this->setData('products_data', $productsData);
    }

    public function getFilterAndSortingData()
    {
        return $this->getData('filter_sorting_data');
    }

    public function setFilterAndSortingData($filterAndSortingData)
    {
        return $this->setData('filter_sorting_data', $filterAndSortingData);
    }
}
