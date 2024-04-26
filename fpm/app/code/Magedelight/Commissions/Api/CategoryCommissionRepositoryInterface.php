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
interface CategoryCommissionRepositoryInterface
{
    /**
     * Create category commission
     *
     * @param \Magedelight\Commissions\Api\Data\CommissionInterface $commission
     * @return \Magedelight\Commissions\Api\Data\CommissionInterface
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function save(\Magedelight\Commissions\Api\Data\CommissionInterface $commission);

    /**
     * Get info about category commission by commission id
     *
     * @param int $commissionId
     * @param int $storeId
     * @return \Magedelight\Commissions\Api\Data\CommissionInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function get($commissionId, $storeId = null);

    /**
     * Get category commission list
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Magedelight\Commissions\Api\Data\CommissionSearchResultsInterface
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);

    /**
     * Delete category commission by identifier
     *
     * @param \Magedelight\Commissions\Api\Data\CommissionInterface $commission category commission which will deleted
     * @return bool Will returned True if deleted
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\StateException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function delete(\Magedelight\Commissions\Api\Data\CommissionInterface $commission);

    /**
     * Delete category commission by identifier
     *
     * @param int $commissionId
     * @return bool Will returned True if deleted
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\StateException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function deleteByIdentifier($commissionId);
}
