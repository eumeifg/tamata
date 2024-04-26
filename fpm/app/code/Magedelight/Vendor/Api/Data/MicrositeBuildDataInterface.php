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
namespace Magedelight\Vendor\Api\Data;

/**
 * Microsite build interface.
 */
interface MicrositeBuildDataInterface
{

    /**
     * Get rating data
     *
     * @api
     * @return \Magedelight\Vendor\Api\Data\MicrositeRatingDataInterface
     */
    public function getRatingData();

    /**
     * Set rating data
     *
     * @api
     * @param \Magedelight\Vendor\Api\Data\MicrositeRatingDataInterface $ratingData
     * @return $this
     */
    public function setRatingData($ratingData);

    /**
     * Get vendor data
     *
     * @api
     * @return \Magedelight\Vendor\Api\Data\VendorInterface
     */
    public function getVendorData();

    /**
     * Set vendor data
     *
     * @api
     * @param \Magedelight\Vendor\Api\Data\VendorInterface $vendorData
     * @return $this
     */
    public function setVendorData($vendorData);

    /**
     * Get microsite data
     *
     * @api
     * @return \Magedelight\Vendor\Api\Data\MicrositeInterface
     */
    public function getMicrositeData();

    /**
     * Set microsite data
     *
     * @api
     * @param \Magedelight\Vendor\Api\Data\MicrositeInterface $micrositeData
     * @return $this
     */
    public function setMicrositeData($micrositeData);

    /**
     * Get products data
     *
     * @api
     * @return \Magedelight\Vendor\Api\Data\Microsite\ProductSearchResultsInterface
     */
    public function getProductsData();

    /**
     * Set products data
     *
     * @api
     * @param \Magedelight\Vendor\Api\Data\Microsite\ProductSearchResultsInterface $productsData
     * @return $this
     */
    public function setProductsData($productsData);

    /**
     * Get products data
     *
     * @api
     * @return \Magedelight\Vendor\Api\Data\Microsite\FilterAndSortingDataInterface[]|null
     */
    public function getFilterAndSortingData();

    /**
     * Set products data
     *
     * @api
     * @param \Magedelight\Vendor\Api\Data\Microsite\FilterAndSortingDataInterface[] $filterAndSortingData
     * @return $this
     */
    public function setFilterAndSortingData($filterAndSortingData);
}
