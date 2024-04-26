<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Commissions
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Commissions\Api;

/**
 * @api
 */
interface TransactionRepositoryInterface
{
   
    /**
     * Get Transaction Summary
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface|null $searchCriteria
     * @param string|null $searchTerm
     * @return \Magedelight\Commissions\Api\Data\TransactionSummarySearchResultInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getList($searchCriteria = null, $searchTerm = null);
}
