<?php
/**
 * Copyright © 2019 Magedelight. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magedelight\Rma\Api;

interface MdRmaManagementInterface
{
    /**
     * Return list of RMA
     * @param int|null $limit
     * @param int|null $currPage
     * @return array
     */
    public function getRmaList($limit = null, $currPage = null);

    /**
     * Return data object for specified RMA id
     * @param int $id
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById($id);

    /**
     * Return data object for specified RMA id
     * @param int $orderId
     * @param int $vendorId
     * @return bool|\Magento\Rma\Api\Data\RmaInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getCreateReturnData($orderId, $vendorId);

    /**
     * Create a new RMA
     * @param mixed $rmaData
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function createReturn($rmaData);
}
