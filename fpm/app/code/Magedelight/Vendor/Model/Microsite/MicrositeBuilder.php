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

use Magedelight\Vendor\Model\Microsite\Build\Vendor as VendorDataBuilder;
use Magedelight\Vendor\Model\Microsite\Build\Microsite as MicrositeDataBuilder;
use Magedelight\Vendor\Model\Microsite\Build\Rating as RatingDataBuilder;
use Magedelight\Vendor\Model\Microsite\Build\Products as ProductsDataBuilder;
use Magento\Framework\Api\SearchCriteriaInterface;

/**
 * Build microsite data
 */
class MicrositeBuilder implements \Magedelight\Vendor\Api\MicrositeBuilderInterface
{
    /**
     * @var \Magedelight\Vendor\Api\Data\MicrositeBuildDataInterface
     */
    protected $micrositeBuildDataInterface;

    /**
     * @var VendorDataBuilder
     */
    protected $vendorDataBuilder;

    /**
     * @var MicrositeDataBuilder
     */
    protected $micrositeDataBuilder;

    /**
     * @var RatingDataBuilder
     */
    protected $ratingDataBuilder;

    /**
     * @var ProductsDataBuilder
     */
    protected $productsDataBuilder;

    /**
     * MicrositeBuilder constructor.
     * @param \Magedelight\Vendor\Api\Data\MicrositeBuildDataInterface $micrositeBuildDataInterface
     * @param VendorDataBuilder $vendorDataBuilder
     * @param MicrositeDataBuilder $micrositeDataBuilder
     * @param RatingDataBuilder $ratingDataBuilder
     * @param ProductsDataBuilder $productsDataBuilder
     */
    public function __construct(
        \Magedelight\Vendor\Api\Data\MicrositeBuildDataInterface $micrositeBuildDataInterface,
        VendorDataBuilder $vendorDataBuilder,
        MicrositeDataBuilder $micrositeDataBuilder,
        RatingDataBuilder $ratingDataBuilder,
        ProductsDataBuilder $productsDataBuilder
    ) {
        $this->micrositeBuildDataInterface = $micrositeBuildDataInterface;
        $this->vendorDataBuilder = $vendorDataBuilder;
        $this->micrositeDataBuilder = $micrositeDataBuilder;
        $this->ratingDataBuilder = $ratingDataBuilder;
        $this->productsDataBuilder = $productsDataBuilder;
    }

    /**
     * {@inheritDoc}
     *
     */
    public function build(
        $vendorId,
        $storeId,
        SearchCriteriaInterface $searchCriteria = null,
        $loadProductsOnly = false
    ) {
        if ($vendorId) {
            if(!$loadProductsOnly){
                $this->micrositeBuildDataInterface->setVendorData(
                    $this->vendorDataBuilder->build($vendorId, $storeId)
                );
                $this->micrositeBuildDataInterface->setRatingData(
                    $this->ratingDataBuilder->build($vendorId)
                );
                $this->micrositeBuildDataInterface->setMicrositeData(
                    $this->micrositeDataBuilder->build($vendorId, $storeId)
                );
            }
            $this->micrositeBuildDataInterface->setProductsData(
                $this->productsDataBuilder->build($vendorId, $searchCriteria)
            );
        }
        return $this->micrositeBuildDataInterface;
    }
}
