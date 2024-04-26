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
namespace Magedelight\Catalog\Api;

use Magento\Framework\Api\SearchCriteriaInterface;

interface CategoryProductRepositoryInterface
{
    /**
     * Collect and retrieve the list of product render info.
     *
     * This info contains raw prices and formatted prices, product name, stock status, store_id, etc.
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @param int|null $categoryId
     * @param string|array|null $productIds
     * @return \Magedelight\Catalog\Api\Data\ProductRenderSearchResultsInterface
     * @see \Magento\Catalog\Api\Data\ProductRenderInfoDtoInterface
     *
     * @since 102.0.0
     */
    public function getList(SearchCriteriaInterface $searchCriteria, $categoryId = null, $productIds = null);
}
