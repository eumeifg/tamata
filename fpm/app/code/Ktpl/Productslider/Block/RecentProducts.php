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

namespace Ktpl\Productslider\Block;

use Magento\Catalog\Block\Product\Context;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\App\Http\Context as HttpContext;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Reports\Block\Product\Viewed as ReportProductViewed;
use Ktpl\Productslider\Helper\Data;

/**
 * Class RecentProducts
 * @package Ktpl\Productslider\Block
 */
class RecentProducts extends AbstractSlider
{
    /**
     * @var ReportProductViewed
     */
    protected $reportProductViewed;

    /**
     * RecentProducts constructor.
     * @param Context $context
     * @param CollectionFactory $productCollectionFactory
     * @param Visibility $catalogProductVisibility
     * @param DateTime $dateTime
     * @param Data $helperData
     * @param HttpContext $httpContext
     * @param ReportProductViewed $reportProductViewed
     * @param array $data
     */
    public function __construct(
        Context $context,
        CollectionFactory $productCollectionFactory,
        Visibility $catalogProductVisibility,
        DateTime $dateTime,
        Data $helperData,
        HttpContext $httpContext,
        ReportProductViewed $reportProductViewed,
        array $data = []
    ) {
        $this->reportProductViewed = $reportProductViewed;

        parent::__construct(
            $context,
            $productCollectionFactory,
            $catalogProductVisibility,
            $dateTime,
            $helperData,
            $httpContext,
            $data
        );
    }

    /**
     * Get Collection Recently Viewed product
     * @return mixed
     */
    public function getProductCollection()
    {
        return $this->reportProductViewed->getItemsCollection()->setPageSize($this->getProductsCount());
    }
}
