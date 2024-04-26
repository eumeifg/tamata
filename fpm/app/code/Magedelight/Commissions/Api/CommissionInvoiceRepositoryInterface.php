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
interface CommissionInvoiceRepositoryInterface
{
   
    /**
     * Get Commission Invoice List for Vendor
     *
     * @param int $paymentId
     * @return \Magedelight\Commissions\Api\Data\InvoiceDownloadInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function downloadCommissionInvoice(int $paymentId);

    /**
     * Get Commission Invoice List for Vendor
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Magedelight\Commissions\Api\Data\CommissionInvoiceItemsInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);
}
