<?php
/**
 * KrishTechnolabs
 *
 * PHP version 7
 *
 * @category  KrishTechnolabs
 * @package   Ktpl_Cms
 * @copyright 2019 (c) KrishTechnolabs (https://www.krishtechnolabs.com/)
 * @license   https://www.krishtechnolabs.com/LICENSE.txt Krish License
 * @link      https://www.krishtechnolabs.com/
 */
namespace Ktpl\Cms\Model\Pages;

use Ktpl\Cms\Api\Data\HomePageInterface as HomePageDataInterface;
use Ktpl\Cms\Api\HomePageBuilderInterface;
use Ktpl\BannerManagement\Api\SliderRepositoryInterface;
use Ktpl\BannerManagement\Model\Cms\HomePage\Banners;
use Ktpl\TopCategory\Model\Cms\HomePage\TopCategories;
use Ktpl\Productslider\Model\Cms\HomePage\ProductSliders;
use \Magento\Framework\Webapi\Rest\Response;
use Magento\Framework\Stdlib\DateTime;
use Ktpl\Productslider\Model\Cms\HomePage\AfterTopCategory\Banners as HomePageAfterTopCategoryBanner;

/**
 * Build Home Page Data
 */
class HomePage implements HomePageBuilderInterface
{
    /** Format for expiration timestamp headers */
    const EXPIRATION_TIMESTAMP_FORMAT = 'D, d M Y H:i:s T';

    /**
     * @var HomePageDataInterface
     */
    protected $homePageDataInterface;

    /**
     * @var Banners
     */
    protected $banners;

    /**
     * @var TopCategories
     */
    protected $topCategories;

    /**
     * @var TopCategories
     */
    protected $productSliders;

    /**
     * @var Response
     */
    protected $httpResponse;

    /**
     * @var DateTime
     */
    protected $dateTime;

    /**
     * @var HomePageAfterTopCategoryBanner
     */
    protected $homePageAfterTopCategoryBanner;

    /**
     * @param HomePageDataInterface $homePageDataInterface
     * @param Banners $banners
     * @param TopCategories $topCategories
     * @param ProductSliders $productSliders
     * @param Response $httpResponse
     * @param DateTime $dateTime
     */
    public function __construct(
        HomePageDataInterface $homePageDataInterface,
        Banners $banners,
        TopCategories $topCategories,
        ProductSliders $productSliders,
        Response $httpResponse,
        DateTime $dateTime,
        HomePageAfterTopCategoryBanner $homePageAfterTopCategoryBanner
    ) {
        $this->homePageDataInterface = $homePageDataInterface;
        $this->banners = $banners;
        $this->topCategories = $topCategories;
        $this->productSliders = $productSliders;
        $this->httpResponse = $httpResponse;
        $this->dateTime = $dateTime;
        $this->homePageAfterTopCategoryBanner = $homePageAfterTopCategoryBanner;
    }

    /**
     * {@inheritDoc}
     *
     */
    public function build()
    {
        $this->httpResponse->setHeader('pragma', 'cache', true);
        $this->httpResponse->setHeader('cache-control', 'public, max-age=3600, s-maxage=3600', true);
        $ttl = '3600';
        $this->httpResponse->setHeader('expires', $this->getExpirationHeader('+' . $ttl . ' seconds'), true);

        $this->homePageDataInterface->setBanners(
            $this->banners->build()
        );
        $this->homePageDataInterface->setTopCategories(
            $this->topCategories->build()
        );
        $this->homePageDataInterface->setBannerAfterTopCategories(
            $this->homePageAfterTopCategoryBanner->build()
        );
        $this->homePageDataInterface->setProductSliders(
            $this->productSliders->build()
        );
        return $this->homePageDataInterface;
    }

    public function getExpirationHeader($time) {
        return $this->dateTime->gmDate(self::EXPIRATION_TIMESTAMP_FORMAT, $this->dateTime->strToTime($time));
    }
}
