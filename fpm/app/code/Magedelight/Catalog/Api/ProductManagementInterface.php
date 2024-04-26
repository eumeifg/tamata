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

interface ProductManagementInterface
{
    /**#@+
      * Constant for product condition
      */
    const USED_PRODUCT = 0;
    const NEW_PRODUCT = 1;
    const RENTAL_PRODUCT = 2;

    /**#@+
      * Constant for product status
      */
    const STATUS_APPROVED_NOT_LIVE = 0;
    const STATUS_LIVE = 1;

    /**
     * Reject a product request. Perform necessary business operations like sending email.
     *
     * @api
     * @param int $requestId
     * @return \Magedelight\Catalog\Api\ProductManagementInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\MailException
     * @throws \Exception
     */
    public function rejectProductRequest($requestId);

    /**
     * List/Unlist product.
     *
     * @api
     * @param mixed $productId
     * @param string $type
     * @return \Magedelight\Sales\Api\Data\CustomMessageInterface
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Exception
     */
    public function listUnlistProduct($productId, $type);

    /**
     * @api
     * @param int $vendorProductId
     * @return \Magedelight\Sales\Api\Data\CustomMessageInterface
     */
    public function quickEdit($vendorProductId);
}
