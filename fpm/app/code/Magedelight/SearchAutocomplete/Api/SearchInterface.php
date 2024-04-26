<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_SearchAutocomplete
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */

namespace Magedelight\SearchAutocomplete\Api;

/**
 * @api
 */
interface SearchInterface
{
    /**
     * @param string $query
     * @param \Magento\Framework\Api\Search\SearchCriteriaInterface $searchCriteria
     * @return \Magedelight\Catalog\Api\Data\ProductRenderSearchResultsInterface
     */
    public function search($query, \Magento\Framework\Api\Search\SearchCriteriaInterface $searchCriteria = null);
}
