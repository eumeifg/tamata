<?php

namespace MDC\Vendor\Model\Microsite;

use Magedelight\Vendor\Model\Microsite\Build\Vendor as VendorDataBuilder;
use Magedelight\Vendor\Model\Microsite\Build\Microsite as MicrositeDataBuilder;
use Magedelight\Vendor\Model\Microsite\Build\Rating as RatingDataBuilder;
use Magedelight\Vendor\Model\Microsite\Build\Products as ProductsDataBuilder;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Stdlib\DateTime;
use Magento\Framework\Webapi\Rest\Response;

/**
 * Build microsite data
 */
class MicrositeBuilder extends \Magedelight\Vendor\Model\Microsite\MicrositeBuilder
{
    /** Format for expiration timestamp headers */
    const EXPIRATION_TIMESTAMP_FORMAT = 'D, d M Y H:i:s T';


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
     * @var Response
     */
    protected $httpResponse;

    /**
     * @var DateTime
     */
    protected $dateTime;

    /**
     * @param \Magedelight\Vendor\Api\Data\MicrositeBuildDataInterface $micrositeBuildDataInterface
     * @param VendorDataBuilder $vendorDataBuilder
     * @param MicrositeDataBuilder $micrositeDataBuilder
     * @param RatingDataBuilder $ratingDataBuilder
     * @param ProductsDataBuilder $productsDataBuilder
     * @param Response $httpResponse
     * @param DateTime $dateTime
     */
    public function __construct(
        \Magedelight\Vendor\Api\Data\MicrositeBuildDataInterface $micrositeBuildDataInterface,
        VendorDataBuilder $vendorDataBuilder,
        MicrositeDataBuilder $micrositeDataBuilder,
        RatingDataBuilder $ratingDataBuilder,
        ProductsDataBuilder $productsDataBuilder,
        Response $httpResponse,
        DateTime $dateTime
    ) {
         parent::__construct(
            $micrositeBuildDataInterface,
            $vendorDataBuilder,
            $micrositeDataBuilder,
            $ratingDataBuilder,
            $productsDataBuilder
        );
        $this->httpResponse = $httpResponse;
        $this->dateTime = $dateTime;
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
        $this->httpResponse->setHeader('pragma', 'cache', true);
        $this->httpResponse->setHeader('cache-control', 'public, max-age=3600, s-maxage=3600', true);
        $ttl = '3600';
        $this->httpResponse->setHeader('expires', $this->getExpirationHeader('+' . $ttl . ' seconds'), true);
        if ($vendorId) {
            if(!$loadProductsOnly){
                $this->micrositeBuildDataInterface->setRatingData(
                    $this->ratingDataBuilder->build($vendorId)
                );
                $this->micrositeBuildDataInterface->setMicrositeData(
                    $this->micrositeDataBuilder->build($vendorId, $storeId)
                );
            }
             $this->micrositeBuildDataInterface->setVendorData(
                    $this->vendorDataBuilder->build($vendorId, $storeId)
             );  // As per CR display vendor product data incase vendor dont have microsite and vendor "business_name" can available for both case to display
            $productDataResult = $this->productsDataBuilder->build($vendorId, $searchCriteria);
            $this->micrositeBuildDataInterface->setProductsData(
                $productDataResult['search_result']
            );
            $this->micrositeBuildDataInterface->setFilterAndSortingData(
                $this->productsDataBuilder->buildForFilterAndSorting($productDataResult['collection'], $vendorId, $storeId)
            );
        }
        return $this->micrositeBuildDataInterface;
    }

    public function getExpirationHeader($time) {
        return $this->dateTime->gmDate(self::EXPIRATION_TIMESTAMP_FORMAT, $this->dateTime->strToTime($time));
    }
}
