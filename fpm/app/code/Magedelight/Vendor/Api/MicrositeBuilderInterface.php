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
namespace Magedelight\Vendor\Api;

use Magento\Framework\Api\SearchCriteriaInterface;

/**
 * Build microsite page data with products and reviews
 */
interface MicrositeBuilderInterface
{
    /**
     * @param integer $vendorId
     * @param integer $storeId
     * @param SearchCriteriaInterface|null $searchCriteria
     * @param bool $loadProductsOnly
     * @return \Magedelight\Vendor\Api\Data\MicrositeBuildDataInterface
     */
    public function build(
        $vendorId,
        $storeId,
        SearchCriteriaInterface $searchCriteria = null,
        $loadProductsOnly = false
    );
}
