<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Sales
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Sales\Api;

/**
 * @api
 */
interface InvoiceBuilderInterface
{

    /**
     * Prepare invoice form data for invoice generation in vendor app.
     * @param int $orderId
     * @param int $vendorOrderId
     * @return \Magedelight\Sales\Api\Data\InvoiceDataInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function createInvoiceFormData($orderId, $vendorOrderId);

    /**
     * @param int|mixed[] $orderId
     * @return \Magedelight\Sales\Api\Data\FileDownloadInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function printInvoice($vendorOrderId);
}
